<?php

use App\Http\Controllers\Api\FormController;
use Illuminate\Support\Facades\Route;

// Website forms (contact, proposal, inquiry, free audit) and the blog newsletter.
Route::middleware('throttle:forms')->group(function () {
    Route::post('contact', [FormController::class, 'contact']);
    Route::post('subscribe', [FormController::class, 'subscribe']);
});

// Website analytics (page views and time on page from public/js/site.js).
Route::post('t', \App\Http\Controllers\Api\TrackController::class)->middleware('throttle:track');
