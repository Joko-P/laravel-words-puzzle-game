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
        [
            'id' => 'bratty-cat',
            'path' => 'profile-pics-openmoji/bratty-cat.svg',
        ],
        [
            'id' => 'cactus',
            'path' => 'profile-pics-openmoji/cactus.svg',
        ],
        [
            'id' => 'cow',
            'path' => 'profile-pics-openmoji/cow.svg',
        ],
        [
            'id' => 'crab',
            'path' => 'profile-pics-openmoji/crab.svg',
        ],
        [
            'id' => 'dark-moon',
            'path' => 'profile-pics-openmoji/dark-moon.svg',
        ],
        [
            'id' => 'disco-ball',
            'path' => 'profile-pics-openmoji/disco-ball.svg',
        ],
        [
            'id' => 'discord',
            'path' => 'profile-pics-openmoji/discord.svg',
        ],
        [
            'id' => 'fairy-male',
            'path' => 'profile-pics-openmoji/fairy-male.svg',
        ],
        [
            'id' => 'fairy-female',
            'path' => 'profile-pics-openmoji/fairy-female.svg',
        ],
        [
            'id' => 'fox',
            'path' => 'profile-pics-openmoji/fox.svg',
        ],
        [
            'id' => 'full-moon',
            'path' => 'profile-pics-openmoji/full-moon.svg',
        ],
        [
            'id' => 'gihtub',
            'path' => 'profile-pics-openmoji/gihtub.svg',
        ],
        [
            'id' => 'hacker-cat',
            'path' => 'profile-pics-openmoji/hacker-cat.svg',
        ],
        [
            'id' => 'jack-o-lantern',
            'path' => 'profile-pics-openmoji/jack-o-lantern.svg',
        ],
        [
            'id' => 'hamster',
            'path' => 'profile-pics-openmoji/hamster.svg',
        ],
        [
            'id' => 'joker',
            'path' => 'profile-pics-openmoji/joker.svg',
        ],
        [
            'id' => 'lotus',
            'path' => 'profile-pics-openmoji/lotus.svg',
        ],
        [
            'id' => 'monkey',
            'path' => 'profile-pics-openmoji/monkey.svg',
        ],
        [
            'id' => 'mushroom',
            'path' => 'profile-pics-openmoji/mushroom.svg',
        ],
        [
            'id' => 'pig',
            'path' => 'profile-pics-openmoji/pig.svg',
        ],
        [
            'id' => 'pinata',
            'path' => 'profile-pics-openmoji/pinata.svg',
        ],
        [
            'id' => 'snowman',
            'path' => 'profile-pics-openmoji/snowman.svg',
        ],
        [
            'id' => 'suit-club',
            'path' => 'profile-pics-openmoji/suit-club.svg',
        ],
        [
            'id' => 'suit-heart',
            'path' => 'profile-pics-openmoji/suit-heart.svg',
        ],
        [
            'id' => 'suit-diamond',
            'path' => 'profile-pics-openmoji/suit-diamond.svg',
        ],
        [
            'id' => 'suit-spade',
            'path' => 'profile-pics-openmoji/suit-spade.svg',
        ],
        [
            'id' => 'teddy-bear',
            'path' => 'profile-pics-openmoji/teddy-bear.svg',
        ],
        [
            'id' => 'tiger',
            'path' => 'profile-pics-openmoji/tiger.svg',
        ],
        [
            'id' => 'vendetta',
            'path' => 'profile-pics-openmoji/vendetta.svg',
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