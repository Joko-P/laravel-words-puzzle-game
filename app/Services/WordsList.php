<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

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

            $lists[$id] = $words_set;
        }
        return collect($lists);
    }

    public static function all_list(): array
    {
        return self::all()
            ->map(fn ($json, $id) => [
                'id' => $id,
                'name' => ($json['name'] ?? $id) . " (" . count($json['words']) . " Words)",
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
}
