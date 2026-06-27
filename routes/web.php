<?php

use App\Http\Controllers\GameController;
use App\Http\Controllers\MainController;
use Illuminate\Support\Facades\Route;

Route::get('/', [MainController::class, 'main_menu_index'])->name('main-menu');
Route::post('/start-new-game', [MainController::class, 'start_new_game'])->name('start-new-game');
Route::post('/continue-game', [MainController::class, 'continue_game'])->name('continue-game');

Route::name('game.')->prefix('game/{urlEncodedNamaSesi}')->middleware(['activeGameSession'])->group(function () {
    Route::get('/lobby', [GameController::class, 'lobby_index'])->name('lobby');
});