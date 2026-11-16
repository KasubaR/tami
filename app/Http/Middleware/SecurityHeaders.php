<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        // Keeps the secret link out of any Referer header.
        $response->headers->set('Referrer-Policy', 'no-referrer');
        // Pages hold personal content: never cache them in shared caches.
        $response->headers->set('Cache-Control', 'no-store, private');

        return $response;
    }
}
