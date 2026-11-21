<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Hint Penalty
    |--------------------------------------------------------------------------
    |
    | Skor yang dikurangi dari pemain yang sedang mendapat giliran setiap kali
    | 1 hint dibuka. Bisa diubah lewat GAME_HINT_PENALTY di .env.
    |
    */

    'hint_penalty' => (int) env('GAME_HINT_PENALTY', 7),

    /*
    |--------------------------------------------------------------------------
    | Hint Unlock
    |--------------------------------------------------------------------------
    |
    | Hint baru bisa dibuka setelah giliran ke-N dalam satu ronde selesai,
    | supaya pemain pertama tidak langsung membuka semua hint lalu menebak.
    | Bisa diubah lewat GAME_HINT_UNLOCKS_AFTER_TURN di .env.
    |
    */

    'hint_unlocks_after_turn' => (int) env('GAME_HINT_UNLOCKS_AFTER_TURN', 5),

];
