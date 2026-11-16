<?php

namespace App\Http\Controllers;

use App\Http\Middleware\EnsureGameAccess;
use App\Models\BirthdayGame;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AccessController extends Controller
{
    /** Opening the private link grants this browser access, then moves on without the token in the URL. */
    public function enter(Request $request, string $token): RedirectResponse
    {
        $game = BirthdayGame::first();

        abort_unless($game && hash_equals($game->access_token, $token), 404);

        $request->session()->regenerate();
        $request->session()->put('game_access', EnsureGameAccess::fingerprint($game->access_token));

        return redirect('/');
    }
}
