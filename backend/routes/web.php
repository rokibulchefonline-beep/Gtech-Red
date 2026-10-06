<?php

use App\Http\Controllers\MediaController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/admin');
Route::get('/api/media/{id}', [MediaController::class, 'show'])->where('id', '[A-Za-z0-9_-]+');
