<?php

namespace App\Services;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class MainService
{
    public function availableGameSessions()
    {
        $gameSessions = session('gameSessions', []);
        $availableGameSessions = collect($gameSessions)->keys()->toArray();

        return $availableGameSessions;
    }

    public function returnAllLettersAsArray()
    {
        return [
            'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'
        ];
    }

    // Default dipasang di sini juga, supaya tetap jalan kalau config di-cache sebelum config/game.php ada
    public function hintPenalty(): int
    {
        return (int) config('game.hint_penalty', 7);
    }

    public function hintUnlocksAfterTurn(): int
    {
        return (int) config('game.hint_unlocks_after_turn', 5);
    }
}