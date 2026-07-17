<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();

        $middleware->validateCsrfTokens(except: [
            'api/v1/auth/register',
            'api/v1/auth/login',
            'api/v1/auth/logout',
            'api/v1/quote/generate',
            'api/v1/quote/*/cancel',
            'api/v1/contact-requests',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
