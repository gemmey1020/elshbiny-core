<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HoneypotMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (filled($request->input('omega_trap'))) {
            abort(403);
        }

        return $next($request);
    }
}
