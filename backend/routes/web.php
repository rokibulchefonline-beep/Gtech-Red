<?php

use App\Http\Controllers\MediaController;
use App\Http\Controllers\Site\PageController;
use App\Http\Middleware\MinifySiteHtml;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

// Blade move, phase 2: shared layout preview (header, footer, popup, cookie banner). Pages arrive in phase 3.
Route::view('/blade-preview', 'site.preview');
// Blade move, phase 3: public page templates.
Route::middleware(MinifySiteHtml::class)->group(function () {
    Route::get('/services/{slug}', [PageController::class, 'service'])->where('slug', '[a-z0-9-]+');
    Route::get('/industries/{slug}', [PageController::class, 'industry'])->where('slug', '[a-z0-9-]+');
});

Route::get('/api/media/{id}', [MediaController::class, 'show'])->where('id', '[A-Za-z0-9_-]+');
