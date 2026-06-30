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
}