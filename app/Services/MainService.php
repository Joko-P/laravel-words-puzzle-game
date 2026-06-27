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
        $availableGameSessions = collect($gameSessions)->where('endTime', null)->keys()->toArray();

        return $availableGameSessions;
    }
}