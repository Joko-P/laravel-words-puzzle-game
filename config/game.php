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

    'hint_penalty' => (int) env('GAME_HINT_PENALTY', 5),

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

    /*
    |--------------------------------------------------------------------------
    | Turn Timeout Penalty
    |--------------------------------------------------------------------------
    |
    | Kalau batas waktu per giliran diatur di lobby (lebih dari 0 detik) dan
    | waktunya habis, pemain yang sedang giliran dikurangi skor sebanyak ini
    | lalu giliran pindah ke pemain berikutnya. Sengaja lebih besar dari salah
    | tebak huruf (-25), supaya diam saja tidak lebih untung daripada menebak.
    | Bisa diubah lewat GAME_TURN_TIMEOUT_PENALTY di .env.
    |
    */

    'turn_timeout_penalty' => (int) env('GAME_TURN_TIMEOUT_PENALTY', 35),

];
