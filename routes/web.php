<?php

use App\Http\Controllers\GameController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::get('/play', [GameController::class, 'dashboard']);
Route::get('/play/{number}', [GameController::class, 'show'])->whereNumber('number');
Route::post('/play/{number}/check', [GameController::class, 'check'])
    ->whereNumber('number')->middleware('throttle:60,1');
Route::get('/play/{number}/gift', [GameController::class, 'gift'])->whereNumber('number');
Route::get('/finale', [GameController::class, 'finale']);
