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
            'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
            '1','2','3','4','5','6','7','8','9','0',
        ];
    }

    // Default dipasang di sini juga, supaya tetap jalan kalau config di-cache sebelum config/game.php ada
    public function hintPenalty(): int
    {
        return (int) config('game.hint_penalty');
    }

    public function hintUnlocksAfterTurn(): int
    {
        return (int) config('game.hint_unlocks_after_turn');
    }

    public function turnTimeoutPenalty(): int
    {
        return (int) config('game.turn_timeout_penalty');
    }
}