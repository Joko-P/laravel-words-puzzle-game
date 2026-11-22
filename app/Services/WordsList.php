<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

/**
 * Format words list (storage/app/words-list/*.json):
 *
 * {
 *     "name": "Nama Pack",
 *     "words": {
 *         "<id unik, misal UUID>": {
 *             "word": "KALIMAT",
 *             "hints": ["hint 1", "hint 2"],
 *             "desc": "Opsional, boleh pakai <b>tebal</b>, <i>miring</i>, <u>garis bawah</u>, <s>coret</s>, <a href='https://...'>link</a>",
 *             "image": "Opsional, misal images/words-images/<pack id>/<word id>.jpg (di folder public/)"
 *         }
 *     }
 * }
 *
 * Format lama ("words": ["KALIMAT", ...]) masih bisa dibaca; id-nya dibuat otomatis dan hints-nya kosong.
 * Semua pack dinormalisasi ke bentuk: [id => ['id' => ..., 'word' => ..., 'hints' => [...]]].
 */
class WordsList
{
    protected static string $folder = 'app/words-list';

    public static function all(): object
    {
        $path = storage_path(self::$folder);
        $lists = [];
        foreach (File::files($path) as $file) {
            if (strtolower($file->getExtension()) !== 'json') {
                continue;
            }

            $id = pathinfo($file, PATHINFO_FILENAME);
            $words_set = json_decode(File::get($file), true);

            // Pack yang rusak (JSON tidak valid, tidak ada "words", atau "words" kosong) dilewati
            // supaya lobby tidak error 500. Nama file-nya dicatat di log (storage/logs).
            if (!is_array($words_set) || empty($words_set['words']) || !is_array($words_set['words'])) {
                Log::warning("Words list '{$file->getFilename()}' dilewati: JSON tidak valid, atau \"words\" tidak ada / kosong.", [
                    'json_error' => json_last_error_msg(),
                ]);
                continue;
            }

            $words = self::normalizeWords($words_set['words'], $file->getFilename());
            if (empty($words)) {
                Log::warning("Words list '{$file->getFilename()}' dilewati: tidak ada kalimat yang valid.");
                continue;
            }

            $lists[$id] = [
                'name' => $words_set['name'] ?? $id,
                'words' => $words,
            ];
        }
        return collect($lists);
    }

    public static function all_list(): array
    {
        return self::all()
            ->map(fn ($pack, $id) => [
                'id' => $id,
                'name' => $pack['name'] . " (" . count($pack['words']) . " Words)",
            ])
            ->values()
            ->toArray();
    }

    public static function find(string $id): array
    {
        $lists = self::all();
        $word = $lists[$id] ?? [];

        return $word;
    }

    /**
     * Ubah satu entry kalimat (format baru maupun string format lama) ke bentuk standar.
     * Juga dipakai untuk data game yang sudah tersimpan di session sebelum format ini ada.
     */
    public static function normalizeEntry($entry, ?string $id = null): ?array
    {
        if (is_string($entry)) {
            $entry = ['word' => $entry];
        }

        if (!is_array($entry) || !isset($entry['word']) || !is_string($entry['word'])) {
            return null;
        }

        $word = strtoupper(preg_replace('/\s+/', ' ', trim($entry['word'])));

        // Dicek setelah dinormalisasi (huruf besar, spasi dirapikan), supaya berlaku untuk format lama
        // maupun baru. Karakter lain tidak bisa ditebak lewat tombol, jadi ronde-nya tidak bisa selesai.
        if ($word === '' || !preg_match('/^[A-Z0-9 ]+$/', $word)) {
            return null;
        }

        $hints = array_values(array_filter(
            array_map(fn ($hint) => is_string($hint) ? trim($hint) : '', (array) ($entry['hints'] ?? [])),
            fn ($hint) => $hint !== ''
        ));

        // "desc" dan "image" opsional, hanya dipakai untuk ringkasan di game over screen
        $optionalText = fn ($value) => (is_string($value) && trim($value) !== '') ? trim($value) : null;

        return [
            'id' => (string) ($id ?? $entry['id'] ?? 'legacy-' . md5($word)),
            'word' => $word,
            'hints' => $hints,
            'desc' => $optionalText($entry['desc'] ?? null),
            'image' => $optionalText($entry['image'] ?? null),
        ];
    }

    /**
     * Ubah "desc" dari pack menjadi HTML yang aman untuk ditampilkan.
     * Semua teks di-escape dulu, lalu hanya tag ini yang dikembalikan:
     *   <b> <strong> <i> <em> <u> <s> <del> <br>, dan
     *   <a href='https://...'>teks</a> (petik satu atau dua, hanya http/https, dibuka di tab baru).
     * Tag lain (misal <script>, onclick, javascript:) tetap tampil sebagai teks biasa. Enter jadi baris baru.
     */
    public static function formatDescription(?string $desc): ?string
    {
        if (!is_string($desc) || trim($desc) === '') {
            return null;
        }

        $html = e(trim($desc));
        $html = preg_replace('#&lt;(/?)(b|strong|i|em|u|s|del)&gt;#i', '<$1$2>', $html);
        $html = preg_replace('#&lt;br\s*/?&gt;#i', '<br>', $html);
        $html = preg_replace_callback(
            '#&lt;a\s+href=(?:&quot;|&\#039;)(https?://[^\s<>]*?)(?:&quot;|&\#039;)\s*&gt;(.*?)&lt;/a&gt;#is',
            fn ($m) => '<a href="' . $m[1] . '" target="_blank" rel="noopener noreferrer">' . $m[2] . '</a>',
            $html
        );

        return nl2br($html, false);
    }

    /**
     * URL gambar untuk "image" di pack, misal "images/words-images/kartun-pack/ben_ten.jpg".
     * Hanya file gambar yang benar-benar ada di public/images/words-images/ yang diterima,
     * selain itu (path aneh, "../", file tidak ada) diabaikan saja.
     */
    public static function imageUrl(?string $image): ?string
    {
        if (!is_string($image)) {
            return null;
        }

        $image = ltrim(str_replace('\\', '/', trim($image)), '/');
        if (!preg_match('#^images/words-images/[A-Za-z0-9_\-/]+\.(jpe?g|png|webp|gif|svg)$#i', $image)) {
            return null;
        }
        if (!is_file(public_path($image))) {
            return null;
        }

        return asset($image);
    }

    /**
     * Cari desc & image sebuah kalimat untuk ringkasan game over.
     * $packs adalah hasil all() (dimuat sekali saja). Kalau packId tidak ada (game lama), dicari dari teks kalimatnya.
     */
    public static function wordInfo(object $packs, ?string $packId, ?string $wordId, string $word): array
    {
        $entry = null;
        if ($packId !== null && $wordId !== null) {
            $entry = $packs[$packId]['words'][$wordId] ?? null;
        }
        if ($entry === null) {
            foreach ($packs as $pack) {
                $entry = collect($pack['words'])->firstWhere('word', $word);
                if ($entry !== null) {
                    break;
                }
            }
        }

        return [
            'descHtml' => self::formatDescription($entry['desc'] ?? null),
            'imageUrl' => self::imageUrl($entry['image'] ?? null),
        ];
    }

    private static function normalizeWords(array $words, string $filename): array
    {
        $isLegacyList = array_is_list($words);
        $normalized = [];

        foreach ($words as $key => $entry) {
            $normalizedEntry = self::normalizeEntry($entry, $isLegacyList ? null : (string) $key);

            if ($normalizedEntry === null) {
                Log::warning("Words list '{$filename}': entry '{$key}' dilewati karena \"word\" tidak ada / kosong, atau berisi karakter selain A-Z, 0-9 dan spasi.");
                continue;
            }

            if (isset($normalized[$normalizedEntry['id']])) {
                Log::warning("Words list '{$filename}': id '{$normalizedEntry['id']}' dipakai lebih dari sekali, yang berikutnya dilewati.");
                continue;
            }

            $normalized[$normalizedEntry['id']] = $normalizedEntry;
        }

        return $normalized;
    }
}
