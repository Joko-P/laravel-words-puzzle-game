<?php

namespace App\Http\Controllers;

use App\Services\Emoji;
use App\Services\MainService;
use Carbon\Carbon;
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
                'sessionCreatedAt' => now('Asia/Jakarta')->toDateTimeString(),
                'startTime' => null,
                'endTime' => null,
                'playersList' => [],
                'loadedWordsList' => [],
                'roundsData' => [],
                'roundsAmount' => 1,
                'currentRound' => 1,
            ]
        ]);        

        session()->put('current_game', [
            'name' => $validated['namaSesi'],
            'url' => urlencode($validated['namaSesi']),
        ]);

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

        session()->put('current_game', [
            'name' => $validated['namaSesi'],
            'url' => urlencode($validated['namaSesi']),
        ]);

        return redirect()->route('game.lobby', ['urlEncodedNamaSesi' => session('gameSessions')[$validated['namaSesi']]['urlEncodedNamaSesi']]);
    }

    public function current_session_details(Request $request)
    {
        $currentGameSession = session('gameSessions.' . session('current_game')['name']);

        if (!$currentGameSession) {
            return response()->json(['error' => 'No active game session found.'], 404);
        }

        $currentGameSession['sessionCreatedAt'] = $currentGameSession['sessionCreatedAt'] ? Carbon::parse($currentGameSession['sessionCreatedAt'], 'Asia/Jakarta')->format('Y-m-d H:i:s') : null;
        

        return response()->json($currentGameSession);
    }

    public function delete_game_session(Request $request)
    {
        $currentGameSessionName = session('current_game');

        if (!$currentGameSessionName) {
            return response()->json(['error' => 'No active game session found.'], 404);
        }

        // Remove the current game session from the session data
        $gameSessions = session('gameSessions', []);
        unset($gameSessions[$currentGameSessionName['name']]);
        session()->put('gameSessions', $gameSessions);

        // Clear the current game session
        session()->forget('current_game');

        return response()->json(['success' => 'Game session deleted successfully.']);
    }

    public function get_random_picture(Request $request)
    {
        $new_picture = Emoji::get_random_profile_pic();
        $new_picture['path'] = asset($new_picture['path']);

        return $new_picture;
    }
}
