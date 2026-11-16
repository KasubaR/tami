<?php

namespace App\Http\Middleware;

use App\Support\GameSchedule;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The puzzles stay locked until the opening time, on the server, so typing a
 * level address early does not skip the wait.
 */
class EnsureGameIsOpen
{
    public function handle(Request $request, Closure $next): Response
    {
        if (GameSchedule::isOpen()) {
            return $next($request);
        }

        abort_unless($request->isMethod('GET'), 403);

        return redirect('/');
    }
}
