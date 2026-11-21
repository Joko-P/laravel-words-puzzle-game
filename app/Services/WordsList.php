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
 *         "<id unik, misal UUID>": { "word": "KALIMAT", "hints": ["hint 1", "hint 2"] }
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
        if ($word === '') {
            return null;
        }

        $hints = array_values(array_filter(
            array_map(fn ($hint) => is_string($hint) ? trim($hint) : '', (array) ($entry['hints'] ?? [])),
            fn ($hint) => $hint !== ''
        ));

        return [
            'id' => (string) ($id ?? $entry['id'] ?? 'legacy-' . md5($word)),
            'word' => $word,
            'hints' => $hints,
        ];
    }

    private static function normalizeWords(array $words, string $filename): array
    {
        $isLegacyList = array_is_list($words);
        $normalized = [];

        foreach ($words as $key => $entry) {
            $normalizedEntry = self::normalizeEntry($entry, $isLegacyList ? null : (string) $key);

            if ($normalizedEntry === null) {
                Log::warning("Words list '{$filename}': entry '{$key}' dilewati karena \"word\" tidak ada / kosong.");
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
