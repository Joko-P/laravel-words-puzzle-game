<?php

namespace App\Http\Controllers;

use App\Services\MainService;
use Illuminate\Http\Request;

class MainController extends Controller
{
    protected $mainService;

    public function __construct(MainService $mainService)
    {
        $this->mainService = $mainService;
    }

    public function main_menu_index()
    {
        $availableGameSessions = $this->mainService->availableGameSessions();

        return view('game-views.main-menu', [
            'availableGameSessions' => $availableGameSessions,
        ]);
    }

    public function start_new_game(Request $request)
    {
        $request = $request->merge([
            'namaSesi' => str_replace(' ', '_', trim($request->input('namaSesi'))),
        ]);

        $validated = $request->validate([
            'namaSesi' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($request) {
                    $gameSessions = session('gameSessions', []);
                    $sessionAlreadyExists = isset($gameSessions[$value]);

                    if ($sessionAlreadyExists) {
                        $fail("Sesi game dengan nama tersebut sudah ada!");
                    }
                },
            ]
        ]);

        session([
            'gameSessions.' . $validated['namaSesi'] => [
                'namaSesi' => $validated['namaSesi'],
                'urlEncodedNamaSesi' => urlencode($validated['namaSesi']),
                'sessionCreatedAt' => now(),
                'startTime' => null,
                'endTime' => null,
                'playersList' => [],
                'playerTurnIndex' => 0,
                'loadedWordsList' => [],
                'roundsData' => [],
                'currentRound' => 0,
                'roundWinndersList' => [],
                'shopItemsList' => [],
            ]
        ]);        

        session()->put('current_game', $validated['namaSesi']);

        return redirect()->route('game.lobby', ['urlEncodedNamaSesi' => session('gameSessions')[$validated['namaSesi']]['urlEncodedNamaSesi']]);
    }

    public function continue_game(Request $request)
    {
        $request = $request->merge([
            'namaSesi' => str_replace(' ', '_', trim($request->input('namaSesi'))),
        ]);

        $validated = $request->validate([
            'namaSesi' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($request) {
                    $gameSessions = session('gameSessions', []);
                    $sessionExists = isset($gameSessions[$value]);

                    if (!$sessionExists) {
                        $fail("Sesi game dengan nama tersebut tidak ditemukan!");
                    }
                },
            ]
        ]);

        session()->put('current_game', $validated['namaSesi']);

        return redirect()->route('game.lobby', ['urlEncodedNamaSesi' => session('gameSessions')[$validated['namaSesi']]['urlEncodedNamaSesi']]);
    }
}
