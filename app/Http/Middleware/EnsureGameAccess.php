<?php

namespace App\Http\Middleware;

use App\Models\BirthdayGame;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The game is private: only a browser that opened the secret link (/g/{token})
 * may see any page. Everyone else gets a plain 404, so the site does not even
 * reveal that a game exists. Rotating the token logs every old session out.
 */
class EnsureGameAccess
{
    public static function fingerprint(string $token): string
    {
        return hash('sha256', $token);
    }

    public function handle(Request $request, Closure $next): Response
    {
        // Local development shortcut (see config/game.php); off unless explicitly enabled.
        if (config('game.open_access') && app()->isLocal()) {
            return $next($request);
        }

        $game = BirthdayGame::first();
        $session = (string) $request->session()->get('game_access', '');

        abort_unless(
            $game && $session !== '' && hash_equals(self::fingerprint($game->access_token), $session),
            404
        );

        return $next($request);
    }
}
