<?php

namespace App\Http\Controllers;

use App\Services\Emoji;
use App\Services\MainService;
use App\Services\WordsList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class GameController extends Controller
{
    protected $mainService;

    public function __construct(MainService $mainService)
    {
        $this->mainService = $mainService;
    }

    public function lobby_index(Request $request)
    {
        $currentGameSessionData = session('gameSessions.' . session('current_game.name'));

        if ($currentGameSessionData['endTime']) {
            // Kalau game sudah finish / selesai
            return redirect()->route('game.game-over-screen', ['urlEncodedNamaSesi' => session('current_game.name')]);
        } else if ($currentGameSessionData['startTime']) {
            // Game yang lagi jalan
            return redirect()->route('game.game-screen', ['urlEncodedNamaSesi' => session('current_game.name')]);
        }

        $default_profile_pic = Emoji::get_random_profile_pic();
        $words_list = WordsList::all_list();
        
        return view('game-views.lobby', [
            'default_pic' => $default_profile_pic,
            'words_list' => $words_list,
        ]);
    }

    public function get_words_list()
    {
        $lists = WordsList::all();
        return $lists;
    }

    public function add_new_player(Request $request, $urlEncodedNamaSesi)
    {
        $validator = Validator::make($request->all(), [
            'profilePicID' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($request) {
                    $emoji = Emoji::find($value);
                    if (!$emoji) {
                        $fail("Profile Pic dengan ID '$value' tidak ditemukan!");
                    }
                },
            ],
            'name' => [
                'required',
                'string',
                'regex:/^[\w\- ]*$/',
            ]
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $current_session_name = 'gameSessions.' . session('current_game')['name'];
        
        $uniqueTrue = false;
        while (!$uniqueTrue) {
            $unique_id = (string) Str::uuid();
            $playersList = session($current_session_name)['playersList'];
            if (!empty($playersList)) {
                $existing = $playersList[$unique_id] ?? [];
                if (empty($existing)) {
                    $uniqueTrue = true;
                }
            } else {
                $uniqueTrue = true;
            }
        }

        $user_data = [
            'id' => $unique_id,
            'name' => $request->name,
            'profilePicID' => $request->profilePicID,
            'profilePicPath' => asset(Emoji::path($request->profilePicID)),
            'score' => 0,
            'order' => null,
        ];

        $playersList = session($current_session_name . '.playersList', []);
        $playersList[$unique_id] = $user_data;
        session([
            $current_session_name . '.playersList' => $playersList
        ]);

        return response()->json(['success' => true], 200);
    }

    public function fetch_players_list()
    {
        $current_session_name = 'gameSessions.' . session('current_game')['name'];
        $players = session($current_session_name . '.playersList', []);

        return response()->json($players, 200);
    }

    public function delete_player(Request $request)
    {
        $player_id = $request->player_id;
        $current_session_name = 'gameSessions.' . session('current_game')['name'];
        $playersList = session($current_session_name)['playersList'];
        if (!empty($playersList) && $playersList[$player_id]) {
            session()->forget($current_session_name . '.playersList.' . $player_id);
        }
        return response()->json(['success' => true], 200);
    }

    public function start_game(Request $request)
    {
        $validated = $request->validate([
            'wordsList' => [
                'required',
                'array',
                'min:1',
            ],
            'wordsList.*' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($request) {
                    $word = WordsList::find($value);
                    if (empty($word)) {
                        $fail("Words List tidak ditemukan!");
                    }
                },
            ],
            'jumlahRonde' => [
                'required',
                'integer',
                'min:1',
                'max:16',
            ],
        ]);

        $currentGameSession = session('gameSessions.' . session('current_game.name'));
        $players_count = count($currentGameSession['playersList']);
        if ($players_count < 2) { return redirect()->back()->withErrors('Minimal jumlah pemain adalah 2!'); }

        $shuffled_words = $this->words_shuffler($validated['wordsList']); // Shuffle words list
        if (count($shuffled_words) < $validated['jumlahRonde']) { $validated['jumlahRonde'] = count($shuffled_words); } // Prevent more rounds than words list
        $shuffled_players_list = $this->shuffle_player_order(); // Shuffle players order
         
        session([
            'gameSessions.' . session('current_game.name') . '.roundsAmount' => (int)$validated['jumlahRonde'],
            'gameSessions.' . session('current_game.name') . '.loadedWordsList' => $shuffled_words,
            'gameSessions.' . session('current_game.name') . '.playersList' => $shuffled_players_list,
            'gameSessions.' . session('current_game.name') . '.startTime' => now('Asia/Jakarta')->toDateTimeString(),
        ]);

        return redirect()->route('game.game-screen', ['urlEncodedNamaSesi' => session('current_game.name')]);
    }

    private function words_shuffler(array $filenames): array
    {
        $shuffled_words = [];

        foreach ($filenames as $id) {
            $words = WordsList::find($id)['words'] ?? [];
            foreach ($words as $word) {
                if (!in_array($word, $shuffled_words)) {
                    array_push($shuffled_words, $word);
                }
            }
        }

        shuffle($shuffled_words);
        return $shuffled_words;
    }

    private function shuffle_player_order(): array
    {
        $playersList = session('gameSessions.' . session('current_game.name') . '.playersList');
        
        $playersIds = array_keys($playersList);
        shuffle($playersIds);

        foreach ($playersIds as $index => $id) {
            $playersList[$id]['order'] = $index;
        }

        return $playersList;
    }

    public function game_screen(Request $request)
    {
        return view('game-views.game-screen');
    }

    public function game_screen_data(Request $request)
    {
        $this->make_new_rounds();
        $current_session = session('gameSessions.' . session('current_game.name'));

        $gameOver = $current_session['endTime'];
        $currentTurn = $this->getCurrentTurnNumber($current_session);
        $scoreBoard = $this->getScoreLeaderboard($current_session);
        $playersTurn = $this->getPlayerTurnsOrder($current_session);

        return response()->json([
            'game_over' => $gameOver ? true : false,
            'game_session_data' => $current_session,
            'current_turn' => $currentTurn,
            'leaderboard' => $scoreBoard,
            'players_turn' => $playersTurn,
        ]);
    }

    private function getCurrentTurnNumber($current_session)
    {
        $currentTurn = '1';
        $roundsData = $current_session['roundsData'] ?? [];

        if (!empty($roundsData)) {
            $latestRound = end($roundsData);
            if (!empty($latestRound)) {
                $latestTurn = (int)($latestRound['turnCount']);
                $currentTurn = $latestTurn;
            }
        }

        return $currentTurn;
    }

    private function getScoreLeaderboard($current_session)
    {
        $playersData = array_values(collect(collect($current_session['playersList'])->values())->sortBy('order')->sortByDesc('score')->toArray());
        
        return $playersData;
    }

    private function getPlayerTurnsOrder($current_session)
    {
        $latestRound = end($current_session['roundsData']);
        $currentPlayerTurn = $latestRound['currentPlayerTurn'];
        
        $playersData = array_values(collect(collect($current_session['playersList'])->values())->sortBy('order')->toArray());
        foreach ($playersData as $index => $player) {
            $playersData[$index]['currentlyPlay'] = false;
            if ($player['order'] == $currentPlayerTurn) {
                $playersData[$index]['currentlyPlay'] = true;
            }
        }
        
        return $playersData;
    }

    private function make_new_rounds()
    {
        $gameSessionName = session('current_game.name');
        $sessionData = session('gameSessions.' . $gameSessionName);
        $roundsAmount = $sessionData['roundsAmount'];

        if (empty($sessionData['roundsData'])) {
            // No rounds started, initialize first round and first turn
            session([
                'gameSessions.' . $gameSessionName . '.roundsData.1' => [
                    'roundNumber' => 1,
                    'currentWord' => $sessionData['loadedWordsList'][0],
                    'availableLetters' => $this->mainService->returnAllLettersAsArray(),
                    'guessedLetters' => [],
                    'currentPlayerTurn' => 0,
                    'turnCount' => 1,
                    'isAnswered' => false,
                    'roundWinner' => null,
                ]
            ]);
        } else {
            // Round has started
            $currentRound = end($sessionData['roundsData']);
            $currentRoundNumber = (int)($currentRound['roundNumber']);
            if ($currentRoundNumber == $roundsAmount && $currentRound['isAnswered']) {
                // Final round is answered, game ended
                session([
                    'gameSessions.' . $gameSessionName . '.endTime' => now('Asia/Jakarta')->toDateTimeString(),
                ]);
            } else if ($currentRound['isAnswered']) {
                // Current round already answered, make new one next.
                $currentPlayerTurn = (int)($currentRound['currentPlayerTurn']);

                $nextPlayerTurn = (((count($sessionData['playersList']) - 1) == $currentPlayerTurn) ? 0 : ($currentPlayerTurn + 1));
                
                session([
                    'gameSessions.' . $gameSessionName . '.roundsData.' . ($currentRoundNumber + 1) => [
                        'roundNumber' => ($currentRoundNumber + 1),
                        'currentWord' => $sessionData['loadedWordsList'][$currentRoundNumber],
                        'availableLetters' => $this->mainService->returnAllLettersAsArray(),
                        'guessedLetters' => [],
                        'currentPlayerTurn' => $nextPlayerTurn,
                        'turnCount' => 1,
                        'isAnswered' => false,
                        'roundWinner' => null,
                    ],
                    'gameSessions.' . $gameSessionName . '.currentRound' => ($currentRoundNumber + 1)
                ]);
            }
        }
    }

    public function guess_the_sentence(Request $request)
    {
        $sentence = $request->input('sentence', null);
        $sentence = trim(strtoupper(str_replace(' ','',$sentence)));

        if ($sentence) {
            $currentSessionRounds = session('gameSessions.' . session('current_game.name') . '.roundsData');
            $currentRound = end($currentSessionRounds);
            $currentRoundNum = $currentRound['roundNumber'];
            
            $guessedLetters = $currentRound['guessedLetters'];
            $currentPlayerTurn = $currentRound['currentPlayerTurn'];
            $isAnswered = $currentRound['isAnswered'];
            $playersList = collect(array_values(session('gameSessions.' . session('current_game.name') . '.playersList')));
            $playersCount = count($playersList);
            $nextPlayerTurn = ((($playersCount - 1) == $currentPlayerTurn) ? 0 : ($currentPlayerTurn + 1));
            $currentPlayer = $playersList->firstWhere('order', $currentPlayerTurn);
            $currentPlayerScore = $currentPlayer['score'];
            $turnCount = $currentRound['turnCount'];
            $currentWord = trim(strtoupper(str_replace(' ','',$currentRound['currentWord'])));
            
            $remainingLetters = $currentWord;
            foreach ($guessedLetters as $letter) {
                $remainingLetters = str_replace($letter, '', $remainingLetters);
            }
            $remainingLettersCount = strlen($remainingLetters);

            if (!$isAnswered) {
                if ($sentence === $currentWord) {
                    $score = 100 + ($remainingLettersCount * 20);
                } else {
                    $score = $remainingLettersCount * -20;
                }

                session([
                    'gameSessions.' . session('current_game.name') . '.playersList.' . $currentPlayer['id'] . '.score' => $currentPlayerScore + $score,
                ]);

                if ($sentence === $currentWord) {
                    session([
                        'gameSessions.' . session('current_game.name') . '.roundsData.' . $currentRoundNum . '.isAnswered' => true,
                        'gameSessions.' . session('current_game.name') . '.roundsData.' . $currentRoundNum . '.roundWinner' => $currentPlayer['id'],
                    ]);

                    $this->make_new_rounds();

                    return response()->json([
                        'state' => 'next_round',
                        'sentence' => '"' . trim(strtoupper($currentRound['currentWord'])) . '"',
                    ], 200);
                } else {
                    session([
                        'gameSessions.' . session('current_game.name') . '.roundsData.' . $currentRoundNum . '.currentPlayerTurn' => $nextPlayerTurn,
                        'gameSessions.' . session('current_game.name') . '.roundsData.' . $currentRoundNum . '.turnCount' => $turnCount + 1,
                    ]);
                }
            }
        }

        return response()->json([
            'state' => 'next_turn',
            'sentence' => $sentence
        ], 200);
    }

    public function guess_the_leter(Request $request)
    {
        $letter = $request->input('letter', null);
        if ($letter) {
            $letter = trim(strtoupper($letter));
            $currentSessionRounds = session('gameSessions.' . session('current_game.name') . '.roundsData');
            $currentRound = end($currentSessionRounds);
            $currentRoundNum = $currentRound['roundNumber'];
            
            $availableLetters = $currentRound['availableLetters'];
            $guessedLetters = $currentRound['guessedLetters'];
            $currentPlayerTurn = $currentRound['currentPlayerTurn'];
            $isAnswered = $currentRound['isAnswered'];
            $playersList = collect(array_values(session('gameSessions.' . session('current_game.name') . '.playersList')));
            $playersCount = count($playersList);
            $nextPlayerTurn = ((($playersCount - 1) == $currentPlayerTurn) ? 0 : ($currentPlayerTurn + 1));
            $currentPlayer = $playersList->firstWhere('order', $currentPlayerTurn);
            $currentPlayerScore = $currentPlayer['score'];
            $turnCount = $currentRound['turnCount'];
            
            if (in_array($letter, $availableLetters) && !in_array($letter, $guessedLetters) && !$isAnswered) {
                // Kalau huruf belum ketebak, cek
                $currentWord = str_replace(' ','',trim(strtoupper($currentRound['currentWord'])));
                $uniqueLetters = [];
                $occurrences = 0;
                foreach (str_split($currentWord) as $char) {
                    if (!in_array($char, $uniqueLetters)) { $uniqueLetters[] = $char; }
                    if ($char == $letter) { $occurrences++;}
                }
                
                if ($occurrences > 0) {
                    $score = 25 + (round(100/$occurrences));
                } else {
                    $score = -25;
                }
                
                // Scoring and letter control
                $key = array_search($letter, $availableLetters);
                if ($key !== false) {
                    $guessedLetters[] = $letter;
                    unset($availableLetters[$key]);

                    $allRevealed = empty(array_diff($uniqueLetters, $guessedLetters));

                    session([
                        'gameSessions.' . session('current_game.name') . '.roundsData.' . $currentRoundNum . '.availableLetters' => $availableLetters,
                        'gameSessions.' . session('current_game.name') . '.roundsData.' . $currentRoundNum . '.guessedLetters' => $guessedLetters,
                        'gameSessions.' . session('current_game.name') . '.playersList.' . $currentPlayer['id'] . '.score' => $currentPlayerScore + $score,
                    ]);

                    if ($allRevealed) {
                        session([
                            'gameSessions.' . session('current_game.name') . '.roundsData.' . $currentRoundNum . '.isAnswered' => true,
                            'gameSessions.' . session('current_game.name') . '.roundsData.' . $currentRoundNum . '.roundWinner' => $currentPlayer['id'],
                        ]);

                        $this->make_new_rounds();

                        return response()->json([
                            'state' => 'next_round',
                            'sentence' => '"' . trim(strtoupper($currentRound['currentWord'])) . '"',
                        ], 200);
                    } else {
                        session([
                            'gameSessions.' . session('current_game.name') . '.roundsData.' . $currentRoundNum . '.currentPlayerTurn' => $nextPlayerTurn,
                            'gameSessions.' . session('current_game.name') . '.roundsData.' . $currentRoundNum . '.turnCount' => $turnCount + 1,
                        ]);
                    }
                }
            }
        }

        return response()->json([
            'state' => 'next_turn',
            'sentence' => null,
        ], 200);
    }

    public function game_over_screen(Request $request)
    {
        $current_session = session('gameSessions.' . session('current_game.name'));
        $scoreBoard = $this->getScoreLeaderboard($current_session);

        return view('game-views.game-over-screen', [
            'leaderBoard' => $scoreBoard,
        ]);
    }
}
