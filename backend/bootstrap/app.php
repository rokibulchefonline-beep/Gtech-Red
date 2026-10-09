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
        // Plesk (and most hosts) run nginx in front of Apache/PHP on the same server: trust it for HTTPS and the visitor's IP.
        $middleware->trustProxies(at: ['127.0.0.1', '::1']);
        // Global, so it also sees "not found" for addresses that match no route.
        $middleware->append(\App\Http\Middleware\FollowRedirects::class);
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        $middleware->prepend(\App\Http\Middleware\CanonicalUrl::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
