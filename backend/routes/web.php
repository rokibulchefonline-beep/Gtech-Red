<?php

use App\Http\Controllers\MediaController;
use App\Http\Controllers\Site\PageController;
use App\Http\Controllers\Site\SeoController;
use App\Http\Middleware\CacheSitePage;
use App\Http\Middleware\MinifySiteHtml;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\Support\Facades\Route;

// Blade move, phase 2: shared layout preview (header, footer, popup, cookie banner). Pages arrive in phase 3.
Route::view('/blade-preview', 'site.preview');
// Blade move, phase 3: public page templates.
// Public pages need no session or cookies (forms post to the API), so those middleware are left out: no
// Set-Cookie headers, no session rows, and the pages can be cached by browsers and CDNs.
$noSession = [StartSession::class, ShareErrorsFromSession::class, ValidateCsrfToken::class, AddQueuedCookiesToResponse::class, EncryptCookies::class];
Route::middleware([\App\Http\Middleware\RecordBotHits::class, CacheSitePage::class, MinifySiteHtml::class])->withoutMiddleware($noSession)->group(function () {
    Route::get('/', [PageController::class, 'main'])->defaults('slug', 'home');
    Route::get('/services/{slug}', [PageController::class, 'service'])->where('slug', '[a-z0-9-]+');
    Route::get('/industries/{slug}', [PageController::class, 'industry'])->where('slug', '[a-z0-9-]+');
    Route::get('/services', [PageController::class, 'main'])->defaults('slug', 'services-hub');
    Route::get('/industries', [PageController::class, 'main'])->defaults('slug', 'industries-hub');
    Route::get('/about', [PageController::class, 'main'])->defaults('slug', 'about');
    Route::get('/contact', [PageController::class, 'main'])->defaults('slug', 'contact');
    Route::view('/free-audit', 'site.pages.free-audit');
    Route::get('/case-studies', [PageController::class, 'caseStudies']);
    Route::get('/case-studies/{slug}', [PageController::class, 'caseStudy'])->where('slug', '[a-z0-9-]+');
    Route::get('/blogs', [PageController::class, 'blogs']);
    Route::get('/blogs/{slug}', [PageController::class, 'post'])->where('slug', '[a-z0-9-]+');
    Route::get('/blogs/author/{slug}', [PageController::class, 'author'])->where('slug', '[a-z0-9-]+');
    foreach (['terms', 'privacy-policy', 'cookie-policy'] as $legal) {
        Route::get("/$legal", [PageController::class, 'legal'])->defaults('slug', $legal);
    }
    // Landing pages made in the panel (/<slug>). A fallback route, so every other address in the app wins.
    Route::fallback([PageController::class, 'landing']);
});

// The panel's preview of unsaved page changes (signed-in staff only).
Route::get('/preview/page/{token}', [\App\Http\Controllers\Site\PreviewController::class, 'show'])->where('token', '[A-Za-z0-9]{40}');
Route::get('/preview/page/{token}/frame', [\App\Http\Controllers\Site\PreviewController::class, 'frame'])->where('token', '[A-Za-z0-9]{40}');
Route::get('/preview/widget/{type}', [\App\Http\Controllers\Site\PreviewController::class, 'widget'])->where('type', '[a-z]+');

Route::withoutMiddleware($noSession)->group(function () {
    Route::get('/sitemap.xml', [SeoController::class, 'sitemap']);
    Route::get('/robots.txt', [SeoController::class, 'robots']);
    Route::get('/llms.txt', [SeoController::class, 'llms']);
});

// Old addresses (next.config.mjs on the website).
Route::redirect('/quote', '/contact', 308);
Route::redirect('/blog', '/blogs', 308);
Route::get('/blog/{slug}', fn (string $slug) => redirect('/blogs/'.$slug, 308))->where('slug', '[A-Za-z0-9-]+');
Route::get('/services/{group}/{item}', fn (string $group, string $item) => redirect('/services/'.$item, 308))->where(['group' => '[a-z0-9-]+', 'item' => '[a-z0-9-]+']);

Route::get('/api/media/{id}', [MediaController::class, 'show'])->where('id', '[A-Za-z0-9_-]+');
