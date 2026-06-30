<?php

namespace App\Services;

class Emoji
{
    protected static $emojis = [
        [
            'id' => 'android',
            'path' => 'profile-pics-openmoji/android.svg',
        ],
        [
            'id' => 'bear',
            'path' => 'profile-pics-openmoji/bear.svg',
        ],
        [
            'id' => 'butterfly',
            'path' => 'profile-pics-openmoji/butterfly.svg',
        ],
        [
            'id' => 'cat',
            'path' => 'profile-pics-openmoji/cat.svg',
        ],
        [
            'id' => 'chicken',
            'path' => 'profile-pics-openmoji/chicken.svg',
        ],
        [
            'id' => 'dog',
            'path' => 'profile-pics-openmoji/dog.svg',
        ],
        [
            'id' => 'frog',
            'path' => 'profile-pics-openmoji/frog.svg',
        ],
        [
            'id' => 'hero-female',
            'path' => 'profile-pics-openmoji/hero-female.svg',
        ],
        [
            'id' => 'hero-male',
            'path' => 'profile-pics-openmoji/hero-male.svg',
        ],
        [
            'id' => 'horse-black',
            'path' => 'profile-pics-openmoji/horse-black.svg',
        ],
        [
            'id' => 'horse-white',
            'path' => 'profile-pics-openmoji/horse-white.svg',
        ],
        [
            'id' => 'ladybug',
            'path' => 'profile-pics-openmoji/ladybug.svg',
        ],
        [
            'id' => 'mage-female',
            'path' => 'profile-pics-openmoji/mage-female.svg',
        ],
        [
            'id' => 'mage-male',
            'path' => 'profile-pics-openmoji/mage-male.svg',
        ],
        [
            'id' => 'moai',
            'path' => 'profile-pics-openmoji/moai.svg',
        ],
        [
            'id' => 'mouse',
            'path' => 'profile-pics-openmoji/mouse.svg',
        ],
        [
            'id' => 'ninja',
            'path' => 'profile-pics-openmoji/ninja.svg',
        ],
        [
            'id' => 'panda',
            'path' => 'profile-pics-openmoji/panda.svg',
        ],
        [
            'id' => 'pitik',
            'path' => 'profile-pics-openmoji/pitik.svg',
        ],
        [
            'id' => 'polar-explorer',
            'path' => 'profile-pics-openmoji/polar-explorer.svg',
        ],
        [
            'id' => 'rabbit',
            'path' => 'profile-pics-openmoji/rabbit.svg',
        ],
        [
            'id' => 'tuxedo-female',
            'path' => 'profile-pics-openmoji/tuxedo-female.svg',
        ],
        [
            'id' => 'tuxedo-male',
            'path' => 'profile-pics-openmoji/tuxedo-male.svg',
        ],
        [
            'id' => 'vampire-female',
            'path' => 'profile-pics-openmoji/vampire-female.svg',
        ],
        [
            'id' => 'vampire-male',
            'path' => 'profile-pics-openmoji/vampire-male.svg',
        ],
        [
            'id' => 'villain-female',
            'path' => 'profile-pics-openmoji/villain-female.svg',
        ],
        [
            'id' => 'villain-male',
            'path' => 'profile-pics-openmoji/villain-male.svg',
        ],
    ];

    public static function all(): array
    {
        return self::$emojis;
    }

    public static function find(string $id): ?array
    {
        foreach (self::$emojis as $emoji) {
            if ($emoji['id'] === $id) {
                return $emoji;
            }
        }

        return null;
    }

    public static function path(string $id): ?string
    {
        $emoji = self::find($id);

        return $emoji['path'] ?? null;
    }

    public static function get_random_profile_pic()
    {
        $deck = session('emojiDeck');

        if (empty($deck)) {
            $deck = collect(self::all())
                ->shuffle()
                ->pluck('id')
                ->toArray();
        }

        $emoji = array_shift($deck);
        session(['emojiDeck' => $deck]);

        $item = self::find($emoji);
        return $item;
    }
}