<?php

use App\Http\Controllers\GameController;
use App\Http\Controllers\MainController;
use Illuminate\Support\Facades\Route;

Route::get('/', [MainController::class, 'main_menu_index'])->name('main-menu');
Route::post('/start-new-game', [MainController::class, 'start_new_game'])->name('start-new-game');
Route::post('/continue-game', [MainController::class, 'continue_game'])->name('continue-game');
Route::get('/current-session-details', [MainController::class, 'current_session_details'])->name('current-session-details');
Route::post('/delete-game-session', [MainController::class, 'delete_game_session'])->name('delete-game-session');
Route::get('/get-random-picture', [MainController::class, 'get_random_picture'])->name('get-random-picture');

Route::name('game.')->prefix('game/{urlEncodedNamaSesi}')->middleware(['activeGameSession'])->group(function () {
    Route::get('/lobby', [GameController::class, 'lobby_index'])->name('lobby');
    Route::get('/get-words-list', [GameController::class,'get_words_list'])->name('get-words-list');
    Route::post('/add-new-player', [GameController::class,'add_new_player'])->name('add-new-player');
    Route::get('/fetch-players-list', [GameController::class,'fetch_players_list'])->name('fetch-players-list');
    Route::post('/delete-player', [GameController::class,'delete_player'])->name('delete-player');

    Route::post('/start-game', [GameController::class,'start_game'])->name('start-game');
    Route::get('/game-screen', [GameController::class,'game_screen'])->name('game-screen');
    Route::get('/game-screen-data', [GameController::class,'game_screen_data'])->name('game-screen-data');
    Route::post('/guessing-the-sentence', [GameController::class,'guess_the_sentence'])->name('guess-the-sentence');
    Route::post('/guessing-the-letter', [GameController::class,'guess_the_leter'])->name('guess-the-letter');
    Route::get('/game-over-screen', [GameController::class,'game_over_screen'])->name('game-over-screen');
});