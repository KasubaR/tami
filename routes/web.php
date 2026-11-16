<?php

use App\Http\Controllers\AccessController;
use App\Http\Controllers\GameController;
use Illuminate\Support\Facades\Route;

/*
 * The only public route is the secret entry link. There is no registration,
 * no login form and no admin interface. Everything else needs game access,
 * and the puzzles also stay locked until the opening time (config/game.php).
 */
Route::get('/g/{token}', [AccessController::class, 'enter'])
    ->where('token', '[A-Za-z0-9]{20,128}')
    ->middleware('throttle:10,1');

Route::middleware('game.access')->group(function () {
    Route::get('/', [GameController::class, 'welcome']);

    Route::middleware('game.open')->group(function () {
        Route::get('/play', [GameController::class, 'dashboard']);
        Route::get('/play/{number}', [GameController::class, 'show'])->whereNumber('number');
        Route::post('/play/{number}/check', [GameController::class, 'check'])
            ->whereNumber('number')->middleware('throttle:30,1');
        Route::get('/play/{number}/gift', [GameController::class, 'gift'])->whereNumber('number');
        Route::get('/finale', [GameController::class, 'finale']);
    });
});
