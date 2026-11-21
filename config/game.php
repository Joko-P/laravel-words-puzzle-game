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

];
