<?php

use App\Http\Controllers\MediaController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');

// Blade move, phase 2: shared layout preview (header, footer, popup, cookie banner). Pages arrive in phase 3.
Route::view('/blade-preview', 'site.preview');
Route::get('/api/media/{id}', [MediaController::class, 'show'])->where('id', '[A-Za-z0-9_-]+');
