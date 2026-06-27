<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GameController extends Controller
{
    public function lobby_index(Request $request)
    {
        return view('game-views.lobby');
    }
}
