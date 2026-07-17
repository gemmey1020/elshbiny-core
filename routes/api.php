<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\ContactController;
use App\Http\Controllers\Api\V1\FitOutEngineController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\QuoteController;
use App\Http\Middleware\HoneypotMiddleware;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/fit-out-engine', [FitOutEngineController::class, 'index']);
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/sync', [ProductController::class, 'sync']);
    Route::get('/products/{slug}', [ProductController::class, 'show']);
    Route::get('/quote/{hash}', [QuoteController::class, 'show']);

    Route::post('/contact-requests', [ContactController::class, 'store'])
        ->middleware([HoneypotMiddleware::class, 'throttle:8,1']);

    Route::post('/quote/generate', [QuoteController::class, 'store'])
        ->middleware([HoneypotMiddleware::class, 'throttle:5,1']);

    Route::post('/quote/{hash}/cancel', [QuoteController::class, 'cancel'])
        ->middleware('auth:sanctum');

    Route::prefix('auth')->group(function (): void {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function (): void {
            Route::get('/me', [AuthController::class, 'me']);
            Route::patch('/profile', [AuthController::class, 'updateProfile']);
            Route::get('/my-quotes', [AuthController::class, 'myQuotes']);
            Route::post('/logout', [AuthController::class, 'logout']);
        });
    });
});
