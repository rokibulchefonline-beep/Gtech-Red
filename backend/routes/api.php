<?php

use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\FormController;
use App\Http\Middleware\RequireApiToken;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Read access for the Next.js website build (server to server, token protected).
    Route::middleware(RequireApiToken::class)->group(function () {
        Route::post('query', [ContentController::class, 'query']);
    });

    // Public forms (called by the website's own API routes or directly by the browser).
    Route::middleware('throttle:60,1')->group(function () {
        Route::post('contact', [FormController::class, 'contact']);
        Route::post('subscribe', [FormController::class, 'subscribe']);
    });
    Route::get('settings', [FormController::class, 'settings']);
});

// Same addresses the website forms already use, so the Blade pages post exactly like the Next.js ones.
Route::middleware('throttle:60,1')->group(function () {
    Route::post('contact', [FormController::class, 'contact']);
    Route::post('subscribe', [FormController::class, 'subscribe']);
});
