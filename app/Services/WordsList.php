<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class WordsList
{
    protected static string $folder = 'app/words-list';

    public static function all(): object
    {
        $path = storage_path(self::$folder);
        $lists = [];
        foreach (File::files($path) as $file) {
            $id = pathinfo($file, PATHINFO_FILENAME);
            $words_set = json_decode(File::get($file), true);
            $lists[$id] = $words_set;
        }
        return collect($lists);
    }

    public static function all_list(): array
    {
        $path = storage_path(self::$folder);

        $lists = [];

        foreach (File::files($path) as $file) {

            $id = pathinfo($file, PATHINFO_FILENAME);

            $json = json_decode(File::get($file), true);

            $lists[] = [
                'id' => $id,
                'name' => $json['name'] ?? $id,
            ];
        }

        return $lists;
    }

    public static function find(string $id): array
    {
        $lists = self::all();
        $word = $lists[$id] ?? [];

        return $word;
    }
}