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

    private function currentSessionKey(): string
    {
        return 'gameSessions.' . (string) session('current_game.name');
    }

    private function currentSession(): array
    {
        return session($this->currentSessionKey(), []);
    }

    private function gameAlreadyStarted(): bool
    {
        return !empty($this->currentSession()['startTime']);
    }

    private function playersLockedResponse()
    {
        // Format sama dengan error validasi supaya bisa langsung ditampilkan oleh JS lobby
        return response()->json(['error' => ['Game sudah dimulai, daftar pemain tidak bisa diubah.']], 422);
    }

    private function gameNotReadyResponse()
    {
        return response()->json(['error' => 'Game belum dimulai atau data ronde tidak ditemukan.'], 409);
    }

    public function lobby_index(Request $request)
    {
        $currentGameSessionData = $this->currentSession();

        if ($currentGameSessionData['endTime']) {
            // Kalau game sudah finish / selesai
            return redirect()->route('game.game-over-screen', ['urlEncodedNamaSesi' => session('current_game.url')]);
        } else if ($currentGameSessionData['startTime']) {
            // Game yang lagi jalan
            return redirect()->route('game.game-screen', ['urlEncodedNamaSesi' => session('current_game.url')]);
        }

        $default_profile_pic = Emoji::get_random_profile_pic();
        $words_list = WordsList::all_list();
        
        return view('game-views.lobby', [
            'default_pic' => $default_profile_pic,
            // Semua pilihan profile pic untuk modal "Change" di lobby
            'emojis' => Emoji::all(),
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
        if ($this->gameAlreadyStarted()) {
            return $this->playersLockedResponse();
        }

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

        $current_session_name = $this->currentSessionKey();
        
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
        Emoji::remove_from_deck($request->profilePicID);

        return response()->json(['success' => true], 200);
    }

    public function fetch_players_list()
    {
        $current_session_name = $this->currentSessionKey();
        $players = session($current_session_name . '.playersList', []);

        return response()->json($players, 200);
    }

    public function delete_player(Request $request)
    {
        if ($this->gameAlreadyStarted()) {
            return $this->playersLockedResponse();
        }

        $player_id = (string) $request->input('player_id');
        $current_session_name = $this->currentSessionKey();
        $playersList = session($current_session_name . '.playersList', []);
        // Player yang sudah terhapus (misal tombol × diklik dua kali) cukup diabaikan
        if ($player_id !== '' && isset($playersList[$player_id])) {
            session()->forget($current_session_name . '.playersList.' . $player_id);
        }
        return response()->json(['success' => true], 200);
    }

    public function start_game(Request $request)
    {
        if ($this->gameAlreadyStarted()) {
            // Misal form lobby di-submit ulang dari halaman cache (tombol Back browser)
            return redirect()->route('game.game-screen', ['urlEncodedNamaSesi' => session('current_game.url')])
                ->withErrors('Game sudah dimulai!');
        }

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
            // Batas waktu per giliran dalam detik, 0 = tanpa batas waktu
            'batasWaktu' => [
                'nullable',
                'integer',
                'min:0',
                'max:600',
            ],
        ], [
            'batasWaktu.integer' => 'Batas waktu harus berupa angka bulat (detik).',
            'batasWaktu.min' => 'Batas waktu minimal 0 detik (0 = tanpa batas waktu).',
            'batasWaktu.max' => 'Batas waktu maksimal 600 detik.',
        ]);

        $currentGameSession = session($this->currentSessionKey());
        $players_count = count($currentGameSession['playersList']);
        if ($players_count < 2) { return redirect()->back()->withErrors('Minimal jumlah pemain adalah 2!'); }

        $shuffled_words = $this->words_shuffler($validated['wordsList']); // Shuffle words list
        if (empty($shuffled_words)) { return redirect()->back()->withErrors('Words List yang dipilih tidak berisi kalimat apapun!'); }
        if (count($shuffled_words) < $validated['jumlahRonde']) { $validated['jumlahRonde'] = count($shuffled_words); } // Prevent more rounds than words list
        $shuffled_players_list = $this->shuffle_player_order(); // Shuffle players order
         
        session([
            $this->currentSessionKey() . '.roundsAmount' => (int)$validated['jumlahRonde'],
            $this->currentSessionKey() . '.loadedWordsList' => $shuffled_words,
            $this->currentSessionKey() . '.playersList' => $shuffled_players_list,
            $this->currentSessionKey() . '.turnTimeLimit' => (int) ($validated['batasWaktu'] ?? 0),
            $this->currentSessionKey() . '.startTime' => now('Asia/Jakarta')->toDateTimeString(),
        ]);

        return redirect()->route('game.game-screen', ['urlEncodedNamaSesi' => session('current_game.url')]);
    }

    private function words_shuffler(array $filenames): array
    {
        // Key pakai teks kalimat, supaya kalimat yang sama dari beberapa pack hanya muncul sekali
        $merged_words = [];

        foreach ($filenames as $id) {
            $words = WordsList::find($id)['words'] ?? [];
            foreach ($words as $entry) {
                if (!isset($merged_words[$entry['word']])) {
                    // packId disimpan supaya desc / image bisa dicari lagi di game over screen.
                    // desc dan image sendiri tidak disimpan di session, supaya session tidak membengkak.
                    $merged_words[$entry['word']] = [
                        'id' => $entry['id'],
                        'packId' => $id,
                        'word' => $entry['word'],
                        'hints' => $entry['hints'],
                    ];
                }
            }
        }

        $shuffled_words = array_values($merged_words);
        shuffle($shuffled_words);
        return $shuffled_words;
    }

    /**
     * Data ronde baru dari satu entry di loadedWordsList.
     * Entry bisa berupa string (session game lama) atau ['id', 'word', 'hints'].
     */
    private function new_round_data(int $roundNumber, $wordEntry, int $playerTurn): array
    {
        $entry = WordsList::normalizeEntry($wordEntry) ?? ['id' => null, 'word' => '', 'hints' => []];

        return [
            'roundNumber' => $roundNumber,
            'packId' => is_array($wordEntry) ? ($wordEntry['packId'] ?? null) : null,
            'wordId' => $entry['id'],
            'currentWord' => $entry['word'],
            'hints' => $entry['hints'],
            'hintsRevealed' => 0,
            'hintLog' => [], // [['turn' => N, 'playerId' => ..., 'penalty' => ...], ...]
            'availableLetters' => $this->mainService->returnAllLettersAsArray(),
            'guessedLetters' => [],
            'currentPlayerTurn' => $playerTurn,
            'turnCount' => 1,
            'turnStartedAt' => now()->getTimestampMs(), // diisi ulang setiap giliran pindah, dipakai untuk batas waktu
            'timeoutLog' => [], // [['turn' => N, 'playerId' => ..., 'penalty' => ...], ...]
            'turnLog' => [], // semua aksi di ronde ini, untuk ringkasan di game over screen (lihat appendTurnLog)
            'isAnswered' => false,
            'roundWinner' => null,
        ];
    }

    /**
     * Catat 1 aksi di turnLog ronde, untuk ringkasan di game over screen.
     * $type: 'letter' | 'sentence' | 'hint' | 'timeout'. $correct null kalau tidak relevan (hint / timeout).
     */
    private function appendTurnLog(int $roundNumber, string $type, string $playerId, int $turn, ?string $value, ?bool $correct, int $points): void
    {
        $key = $this->currentSessionKey() . '.roundsData.' . $roundNumber . '.turnLog';
        $turnLog = session($key, []);
        $turnLog[] = [
            'turn' => $turn,
            'playerId' => $playerId,
            'type' => $type,
            'value' => $value,
            'correct' => $correct,
            'points' => $points,
        ];
        session([$key => $turnLog]);
    }

    private function shuffle_player_order(): array
    {
        $playersList = session($this->currentSessionKey() . '.playersList');
        
        $playersIds = array_keys($playersList);
        shuffle($playersIds);

        foreach ($playersIds as $index => $id) {
            $playersList[$id]['order'] = $index;
        }

        return $playersList;
    }

    public function game_screen(Request $request)
    {
        if (!$this->gameAlreadyStarted()) {
            return redirect()->route('game.lobby', ['urlEncodedNamaSesi' => session('current_game.url')]);
        }

        return view('game-views.game-screen');
    }

    public function game_screen_data(Request $request)
    {
        if (!$this->gameAlreadyStarted()) {
            return $this->gameNotReadyResponse();
        }

        // Waktu giliran yang sudah habis juga diproses di sini, misal setelah halaman ditutup lalu dibuka lagi
        $timeout = $this->applyTurnTimeoutIfExpired();
        $this->make_new_rounds();
        $current_session = session($this->currentSessionKey());

        $gameOver = $current_session['endTime'];
        $currentTurn = $this->getCurrentTurnNumber($current_session);
        $scoreBoard = $this->getScoreLeaderboard($current_session);
        $playersTurn = $this->getPlayerTurnsOrder($current_session);
        $hintsData = $this->getHintsData($current_session);
        $timerData = $this->getTimerData($current_session);

        // Hint yang belum dibuka (dan daftar kalimat berikutnya) tidak perlu dikirim ke browser
        unset($current_session['loadedWordsList']);
        foreach ($current_session['roundsData'] as $roundNumber => $round) {
            unset($current_session['roundsData'][$roundNumber]['hints']);
        }

        return response()->json([
            'game_over' => $gameOver ? true : false,
            'game_session_data' => $current_session,
            'current_turn' => $currentTurn,
            'leaderboard' => $scoreBoard,
            'players_turn' => $playersTurn,
            'hints' => $hintsData,
            'timer' => $timerData,
            'timeout' => $timeout, // diisi kalau barusan ada giliran yang waktunya habis
        ]);
    }

    private function getHintsData($current_session): array
    {
        $latestRound = end($current_session['roundsData']);
        $hints = $latestRound['hints'] ?? [];
        $revealed = min((int) ($latestRound['hintsRevealed'] ?? 0), count($hints));
        $unlocksAfterTurn = $this->mainService->hintUnlocksAfterTurn();
        $locked = $this->hintsLocked($latestRound);
        $usedThisTurn = $this->hintUsedThisTurn($latestRound);

        return [
            'revealed' => array_slice($hints, 0, $revealed),
            'total' => count($hints),
            'penalty' => $this->mainService->hintPenalty(),
            'unlocks_after_turn' => $unlocksAfterTurn,
            'locked' => $locked,
            'used_this_turn' => $usedThisTurn,
            'can_reveal' => !$latestRound['isAnswered'] && !$locked && !$usedThisTurn && $revealed < count($hints),
        ];
    }

    // Hint terkunci selama giliran 1 sampai N di ronde ini (turnCount = giliran yang sedang berjalan)
    private function hintsLocked(array $round): bool
    {
        return (int) ($round['turnCount'] ?? 1) <= $this->mainService->hintUnlocksAfterTurn();
    }

    // Maksimal 1 hint per giliran: cek apakah hint terakhir dibuka di giliran yang sedang berjalan
    private function hintUsedThisTurn(array $round): bool
    {
        $hintLog = $round['hintLog'] ?? []; // ronde lama (sebelum ada hintLog) dianggap belum pernah buka hint
        $lastHint = end($hintLog);

        return $lastHint !== false && (int) $lastHint['turn'] === (int) ($round['turnCount'] ?? 1);
    }

    // Batas waktu per giliran (detik) yang diatur di lobby, 0 = tanpa batas waktu
    private function turnTimeLimit(array $session): int
    {
        return max(0, (int) ($session['turnTimeLimit'] ?? 0));
    }

    private function getTimerData($current_session): array
    {
        $limit = $this->turnTimeLimit($current_session);
        $latestRound = end($current_session['roundsData']);
        $running = $limit > 0
            && empty($current_session['endTime'])
            && !$latestRound['isAnswered']
            && isset($latestRound['turnStartedAt']);

        $elapsedMs = $running ? now()->getTimestampMs() - (int) $latestRound['turnStartedAt'] : 0;

        return [
            'limit_seconds' => $limit,
            'running' => $running,
            // Sisa waktu dihitung server, supaya jam komputer yang beda tidak berpengaruh
            'remaining_ms' => $running ? max(0, $limit * 1000 - $elapsedMs) : 0,
            'penalty' => $this->mainService->turnTimeoutPenalty(),
        ];
    }

    /**
     * Kalau waktu giliran sudah habis: kurangi skor pemain yang sedang giliran, catat di timeoutLog,
     * lalu pindahkan giliran (giliran baru mulai dihitung dari sekarang). Paling banyak 1 giliran
     * per panggilan, dan tidak melakukan apa-apa kalau waktunya belum habis, jadi aman dipanggil berulang.
     * Mengembalikan info timeout, atau null kalau tidak ada yang berubah.
     */
    private function applyTurnTimeoutIfExpired(): ?array
    {
        $session = $this->currentSession();
        $limit = $this->turnTimeLimit($session);
        if ($limit <= 0 || empty($session['startTime']) || !empty($session['endTime']) || empty($session['roundsData'])) {
            return null;
        }

        $round = end($session['roundsData']);
        if ($round['isAnswered'] || !isset($round['turnStartedAt'])) {
            return null;
        }
        if (now()->getTimestampMs() - (int) $round['turnStartedAt'] < $limit * 1000) {
            return null;
        }

        $playersList = collect(array_values($session['playersList']));
        $currentPlayerTurn = (int) $round['currentPlayerTurn'];
        $currentPlayer = $playersList->first(fn ($player) => $player['order'] !== null && (int) $player['order'] === $currentPlayerTurn);
        if (empty($currentPlayer)) {
            return null;
        }

        $nextPlayerTurn = (($playersList->count() - 1) <= $currentPlayerTurn) ? 0 : ($currentPlayerTurn + 1);
        $penalty = $this->mainService->turnTimeoutPenalty();
        $roundKey = $this->currentSessionKey() . '.roundsData.' . $round['roundNumber'];

        $timeoutLog = $round['timeoutLog'] ?? [];
        $timeoutLog[] = [
            'turn' => (int) $round['turnCount'],
            'playerId' => $currentPlayer['id'],
            'penalty' => $penalty,
        ];

        session([
            $this->currentSessionKey() . '.playersList.' . $currentPlayer['id'] . '.score' => $currentPlayer['score'] - $penalty,
            $roundKey . '.currentPlayerTurn' => $nextPlayerTurn,
            $roundKey . '.turnCount' => (int) $round['turnCount'] + 1,
            $roundKey . '.turnStartedAt' => now()->getTimestampMs(),
            $roundKey . '.timeoutLog' => $timeoutLog,
        ]);
        $this->appendTurnLog((int) $round['roundNumber'], 'timeout', $currentPlayer['id'], (int) $round['turnCount'], null, null, -$penalty);

        return [
            'player_name' => $currentPlayer['name'],
            'penalty' => $penalty,
        ];
    }

    // Dipanggil oleh timer di game screen saat waktunya habis. Server tetap mengecek sendiri apakah benar sudah habis.
    public function turn_timeout(Request $request)
    {
        if (!$this->gameAlreadyStarted()) {
            return $this->gameNotReadyResponse();
        }

        $timeout = $this->applyTurnTimeoutIfExpired();
        if ($timeout) {
            return response()->json(['state' => 'timeout'] + $timeout, 200);
        }

        return response()->json(['state' => 'running'], 200);
    }

    public function show_hint(Request $request)
    {
        if ($timeout = $this->applyTurnTimeoutIfExpired()) {
            return response()->json(['state' => 'timeout'] + $timeout, 200);
        }

        $roundsKey = $this->currentSessionKey() . '.roundsData';
        $currentSessionRounds = session($roundsKey, []);
        if (!$this->gameAlreadyStarted() || empty($currentSessionRounds)) {
            return $this->gameNotReadyResponse();
        }

        $currentRound = end($currentSessionRounds);
        $currentRoundNum = $currentRound['roundNumber'];
        if ($currentRound['isAnswered'] || !empty($this->currentSession()['endTime'])) {
            return response()->json(['error' => 'Ronde ini sudah selesai.'], 409);
        }

        if ($this->hintsLocked($currentRound)) {
            return response()->json([
                'error' => 'Hint baru bisa dibuka setelah giliran ke-' . $this->mainService->hintUnlocksAfterTurn() . '.',
            ], 422);
        }

        if ($this->hintUsedThisTurn($currentRound)) {
            return response()->json(['error' => 'Hint sudah dibuka di giliran ini. Maksimal 1 hint per giliran.'], 422);
        }

        $hints = $currentRound['hints'] ?? [];
        $revealed = (int) ($currentRound['hintsRevealed'] ?? 0);
        if ($revealed >= count($hints)) {
            return response()->json(['error' => 'Tidak ada hint lagi untuk kalimat ini.'], 422);
        }

        $playersList = collect(array_values(session($this->currentSessionKey() . '.playersList')));
        $currentPlayer = $playersList->firstWhere('order', $currentRound['currentPlayerTurn']);
        if (empty($currentPlayer)) {
            return $this->gameNotReadyResponse();
        }

        // Hint tidak menghabiskan giliran, hanya mengurangi skor pemain yang sedang giliran
        $penalty = $this->mainService->hintPenalty();
        $hintLog = $currentRound['hintLog'] ?? [];
        $hintLog[] = [
            'turn' => (int) ($currentRound['turnCount'] ?? 1),
            'playerId' => $currentPlayer['id'],
            'penalty' => $penalty,
        ];
        session([
            $roundsKey . '.' . $currentRoundNum . '.hintsRevealed' => $revealed + 1,
            $roundsKey . '.' . $currentRoundNum . '.hintLog' => $hintLog,
            $this->currentSessionKey() . '.playersList.' . $currentPlayer['id'] . '.score' => $currentPlayer['score'] - $penalty,
        ]);
        $this->appendTurnLog((int) $currentRoundNum, 'hint', $currentPlayer['id'], (int) ($currentRound['turnCount'] ?? 1), $hints[$revealed], null, -$penalty);

        return response()->json([
            'state' => 'hint_revealed',
            'hint' => $hints[$revealed],
            'hint_number' => $revealed + 1,
            'hints_total' => count($hints),
            'penalty' => $penalty,
            'player_name' => $currentPlayer['name'],
        ], 200);
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
            // Strict, karena null == 0 bernilai true di PHP
            if ($player['order'] !== null && (int) $player['order'] === (int) $currentPlayerTurn) {
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
                'gameSessions.' . $gameSessionName . '.roundsData.1' => $this->new_round_data(1, $sessionData['loadedWordsList'][0], 0),
            ]);
        } else {
            // Round has started
            $currentRound = end($sessionData['roundsData']);
            $currentRoundNumber = (int)($currentRound['roundNumber']);
            $noWordsLeft = !isset($sessionData['loadedWordsList'][$currentRoundNumber]);
            if (($currentRoundNumber >= $roundsAmount || $noWordsLeft) && $currentRound['isAnswered']) {
                // Final round is answered, game ended (endTime cukup diisi sekali)
                if (empty($sessionData['endTime'])) {
                    session([
                        'gameSessions.' . $gameSessionName . '.endTime' => now('Asia/Jakarta')->toDateTimeString(),
                    ]);
                }
            } else if ($currentRound['isAnswered']) {
                // Current round already answered, make new one next.
                $currentPlayerTurn = (int)($currentRound['currentPlayerTurn']);

                $nextPlayerTurn = (((count($sessionData['playersList']) - 1) == $currentPlayerTurn) ? 0 : ($currentPlayerTurn + 1));
                
                session([
                    'gameSessions.' . $gameSessionName . '.roundsData.' . ($currentRoundNumber + 1) => $this->new_round_data(
                        $currentRoundNumber + 1,
                        $sessionData['loadedWordsList'][$currentRoundNumber],
                        $nextPlayerTurn
                    ),
                    'gameSessions.' . $gameSessionName . '.currentRound' => ($currentRoundNumber + 1)
                ]);
            }
        }
    }

    public function guess_the_sentence(Request $request)
    {
        // Tebakan yang masuk setelah waktu giliran habis ditolak, giliran langsung dipindah
        if ($timeout = $this->applyTurnTimeoutIfExpired()) {
            return response()->json(['state' => 'timeout'] + $timeout, 200);
        }

        $sentence = $request->input('sentence', null);
        $sentence = is_string($sentence) ? $sentence : '';
        // Versi yang rapi (spasi tetap ada) untuk ditampilkan di ringkasan game over
        $sentenceForLog = strtoupper(preg_replace('/\s+/', ' ', trim($sentence)));
        $sentence = trim(strtoupper(str_replace(' ','',$sentence)));

        // Cek string kosong secara eksplisit: "0" bernilai false di PHP, jadi `if ($sentence)` akan melewatinya
        if ($sentence !== '') {
            $currentSessionRounds = session($this->currentSessionKey() . '.roundsData', []);
            if (!$this->gameAlreadyStarted() || empty($currentSessionRounds)) {
                return $this->gameNotReadyResponse();
            }
            $currentRound = end($currentSessionRounds);
            $currentRoundNum = $currentRound['roundNumber'];
            
            $guessedLetters = $currentRound['guessedLetters'];
            $currentPlayerTurn = $currentRound['currentPlayerTurn'];
            $isAnswered = $currentRound['isAnswered'];
            $playersList = collect(array_values(session($this->currentSessionKey() . '.playersList')));
            $playersCount = count($playersList);
            $nextPlayerTurn = ((($playersCount - 1) == $currentPlayerTurn) ? 0 : ($currentPlayerTurn + 1));
            $currentPlayer = $playersList->firstWhere('order', $currentPlayerTurn);
            if (empty($currentPlayer)) {
                return $this->gameNotReadyResponse();
            }
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
                    $this->currentSessionKey() . '.playersList.' . $currentPlayer['id'] . '.score' => $currentPlayerScore + $score,
                ]);
                $this->appendTurnLog((int) $currentRoundNum, 'sentence', $currentPlayer['id'], (int) $turnCount, $sentenceForLog, $sentence === $currentWord, (int) $score);

                if ($sentence === $currentWord) {
                    session([
                        $this->currentSessionKey() . '.roundsData.' . $currentRoundNum . '.isAnswered' => true,
                        $this->currentSessionKey() . '.roundsData.' . $currentRoundNum . '.roundWinner' => $currentPlayer['id'],
                    ]);

                    $this->make_new_rounds();

                    return response()->json([
                        'state' => 'next_round',
                        'sentence' => '"' . trim(strtoupper($currentRound['currentWord'])) . '"',
                    ], 200);
                } else {
                    session([
                        $this->currentSessionKey() . '.roundsData.' . $currentRoundNum . '.currentPlayerTurn' => $nextPlayerTurn,
                        $this->currentSessionKey() . '.roundsData.' . $currentRoundNum . '.turnCount' => $turnCount + 1,
                        $this->currentSessionKey() . '.roundsData.' . $currentRoundNum . '.turnStartedAt' => now()->getTimestampMs(),
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
        // Tebakan yang masuk setelah waktu giliran habis ditolak, giliran langsung dipindah
        if ($timeout = $this->applyTurnTimeoutIfExpired()) {
            return response()->json(['state' => 'timeout'] + $timeout, 200);
        }

        $letter = $request->input('letter', null);
        $letter = is_string($letter) ? trim(strtoupper($letter)) : '';

        // Cek string kosong secara eksplisit: tombol "0" mengirim "0", yang bernilai false di PHP
        if ($letter !== '') {
            $currentSessionRounds = session($this->currentSessionKey() . '.roundsData', []);
            if (!$this->gameAlreadyStarted() || empty($currentSessionRounds)) {
                return $this->gameNotReadyResponse();
            }
            $currentRound = end($currentSessionRounds);
            $currentRoundNum = $currentRound['roundNumber'];
            
            $availableLetters = $currentRound['availableLetters'];
            $guessedLetters = $currentRound['guessedLetters'];
            $currentPlayerTurn = $currentRound['currentPlayerTurn'];
            $isAnswered = $currentRound['isAnswered'];
            $playersList = collect(array_values(session($this->currentSessionKey() . '.playersList')));
            $playersCount = count($playersList);
            $nextPlayerTurn = ((($playersCount - 1) == $currentPlayerTurn) ? 0 : ($currentPlayerTurn + 1));
            $currentPlayer = $playersList->firstWhere('order', $currentPlayerTurn);
            if (empty($currentPlayer)) {
                return $this->gameNotReadyResponse();
            }
            $currentPlayerScore = $currentPlayer['score'];
            $turnCount = $currentRound['turnCount'];
            
            // Perbandingan strict (true / ===), karena sekarang ada angka: jangan sampai "0" dibandingkan secara longgar
            if (in_array($letter, $availableLetters, true) && !in_array($letter, $guessedLetters, true) && !$isAnswered) {
                // Kalau huruf belum ketebak, cek
                $currentWord = str_replace(' ','',trim(strtoupper($currentRound['currentWord'])));
                $uniqueLetters = [];
                $occurrences = 0;
                foreach (str_split($currentWord) as $char) {
                    if (!in_array($char, $uniqueLetters, true)) { $uniqueLetters[] = $char; }
                    if ($char === $letter) { $occurrences++;}
                }
                
                if ($occurrences > 0) {
                    $score = 25 + (round(100/$occurrences));
                } else {
                    $score = -25;
                }
                
                // Scoring and letter control
                $key = array_search($letter, $availableLetters, true);
                if ($key !== false) {
                    $guessedLetters[] = $letter;
                    unset($availableLetters[$key]);

                    $allRevealed = empty(array_diff($uniqueLetters, $guessedLetters));

                    session([
                        $this->currentSessionKey() . '.roundsData.' . $currentRoundNum . '.availableLetters' => $availableLetters,
                        $this->currentSessionKey() . '.roundsData.' . $currentRoundNum . '.guessedLetters' => $guessedLetters,
                        $this->currentSessionKey() . '.playersList.' . $currentPlayer['id'] . '.score' => $currentPlayerScore + $score,
                    ]);
                    $this->appendTurnLog((int) $currentRoundNum, 'letter', $currentPlayer['id'], (int) $turnCount, $letter, $occurrences > 0, (int) $score);

                    if ($allRevealed) {
                        session([
                            $this->currentSessionKey() . '.roundsData.' . $currentRoundNum . '.isAnswered' => true,
                            $this->currentSessionKey() . '.roundsData.' . $currentRoundNum . '.roundWinner' => $currentPlayer['id'],
                        ]);

                        $this->make_new_rounds();

                        return response()->json([
                            'state' => 'next_round',
                            'sentence' => '"' . trim(strtoupper($currentRound['currentWord'])) . '"',
                        ], 200);
                    } else {
                        session([
                            $this->currentSessionKey() . '.roundsData.' . $currentRoundNum . '.currentPlayerTurn' => $nextPlayerTurn,
                            $this->currentSessionKey() . '.roundsData.' . $currentRoundNum . '.turnCount' => $turnCount + 1,
                            $this->currentSessionKey() . '.roundsData.' . $currentRoundNum . '.turnStartedAt' => now()->getTimestampMs(),
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
        $current_session = session($this->currentSessionKey());
        $scoreBoard = $this->getScoreLeaderboard($current_session);

        return view('game-views.game-over-screen', [
            'leaderBoard' => $scoreBoard,
            'summary' => $this->buildGameSummary($current_session),
        ]);
    }

    /**
     * Ringkasan per ronde untuk game over screen: kalimat, pemenang, desc/image dari pack (kalau ada),
     * dan semua aksi dari turnLog. Game lama (sebelum ada turnLog) hanya punya daftar huruf yang ditebak.
     */
    private function buildGameSummary(array $session): array
    {
        $players = $session['playersList'] ?? [];
        $playerName = fn ($id) => $players[$id]['name'] ?? '(pemain tidak ditemukan)';
        $packs = WordsList::all(); // dimuat sekali untuk semua ronde

        $summary = [];
        foreach ($session['roundsData'] ?? [] as $round) {
            $word = (string) ($round['currentWord'] ?? '');
            $info = WordsList::wordInfo($packs, $round['packId'] ?? null, $round['wordId'] ?? null, $word);

            $events = array_map(fn ($event) => [
                'turn' => (int) ($event['turn'] ?? 0),
                'player' => $playerName($event['playerId'] ?? null),
                'type' => $event['type'] ?? '',
                'value' => $event['value'] ?? null,
                'correct' => $event['correct'] ?? null,
                'points' => (int) ($event['points'] ?? 0),
            ], $round['turnLog'] ?? []);

            $summary[] = [
                'number' => (int) ($round['roundNumber'] ?? 0),
                'word' => $word,
                'answered' => (bool) ($round['isAnswered'] ?? false),
                'winner' => !empty($round['roundWinner']) ? $playerName($round['roundWinner']) : null,
                'hasTurnLog' => array_key_exists('turnLog', $round),
                'events' => $events,
                'guessedLetters' => $round['guessedLetters'] ?? [],
                'descHtml' => $info['descHtml'],
                'imageUrl' => $info['imageUrl'],
            ];
        }

        return $summary;
    }
}
