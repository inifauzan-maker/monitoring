<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PastikanLevelAkses
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $levelAkses): Response
    {
        abort_unless(
            $request->user()?->punyaAksesMinimal($levelAkses),
            Response::HTTP_FORBIDDEN,
        );

        return $next($request);
    }
}
