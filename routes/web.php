<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

// Phase 1 placeholders; replaced by controllers in later phases.
Route::view('/play', 'game.dashboard');
Route::view('/play/preview', 'game.crossword');
Route::view('/play/preview/gift', 'game.gift-reveal');
Route::view('/finale', 'game.finale');
