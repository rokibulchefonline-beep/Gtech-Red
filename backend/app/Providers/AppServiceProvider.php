<?php

namespace App\Providers;

use App\Auth\LegacyAwareUserProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Auth::provider('legacy-eloquent', fn ($app, array $config) => new LegacyAwareUserProvider($app['hash'], $config['model']));
    }
}
