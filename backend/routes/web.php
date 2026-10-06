<?php

use App\Http\Controllers\MediaController;
use App\Http\Controllers\Site\PageController;
use App\Http\Middleware\MinifySiteHtml;
use Illuminate\Support\Facades\Route;

// Blade move, phase 2: shared layout preview (header, footer, popup, cookie banner). Pages arrive in phase 3.
Route::view('/blade-preview', 'site.preview');
// Blade move, phase 3: public page templates.
Route::middleware(MinifySiteHtml::class)->group(function () {
    Route::get('/', [PageController::class, 'main'])->defaults('slug', 'home');
    Route::get('/services/{slug}', [PageController::class, 'service'])->where('slug', '[a-z0-9-]+');
    Route::get('/industries/{slug}', [PageController::class, 'industry'])->where('slug', '[a-z0-9-]+');
    Route::get('/services', [PageController::class, 'main'])->defaults('slug', 'services-hub');
    Route::get('/industries', [PageController::class, 'main'])->defaults('slug', 'industries-hub');
    Route::get('/about', [PageController::class, 'main'])->defaults('slug', 'about');
    Route::get('/contact', [PageController::class, 'main'])->defaults('slug', 'contact');
    Route::get('/case-studies', [PageController::class, 'caseStudies']);
    Route::get('/case-studies/{slug}', [PageController::class, 'caseStudy'])->where('slug', '[a-z0-9-]+');
    Route::get('/blogs', [PageController::class, 'blogs']);
    Route::get('/blogs/{slug}', [PageController::class, 'post'])->where('slug', '[a-z0-9-]+');
    foreach (['terms', 'privacy-policy', 'cookie-policy'] as $legal) {
        Route::get("/$legal", [PageController::class, 'legal'])->defaults('slug', $legal);
    }
});

Route::get('/api/media/{id}', [MediaController::class, 'show'])->where('id', '[A-Za-z0-9_-]+');
