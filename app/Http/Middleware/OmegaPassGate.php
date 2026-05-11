<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OmegaPassGate
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('omega.pass_gate.enabled')) {
            return $next($request);
        }

        $secret = (string) config('omega.pass_gate.secret', '');

        if ($secret === '') {
            if (app()->environment(['local', 'testing'])) {
                return $next($request);
            }

            abort(404);
        }

        $provided = (string) ($request->header('X-Omega-Pass') ?? $request->query('omega_pass', ''));

        if (! hash_equals($secret, $provided)) {
            abort(404);
        }

        return $next($request);
    }
}
