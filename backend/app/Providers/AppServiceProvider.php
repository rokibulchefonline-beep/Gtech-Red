<?php

namespace App\Providers;

use App\Auth\LegacyAwareUserProvider;
use App\View\SiteComposer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
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

        // Public website (Blade): shared data and the same helpers the React site used.
        View::composer('site.*', SiteComposer::class);
        Blade::directive('icon', fn ($e) => "<?php echo \\App\\Support\\Site\\Icons::svg($e); ?>");
        Blade::directive('hl', fn ($e) => "<?php echo \\App\\Support\\Site\\Hl::html($e); ?>");
        // Separate rate limits, so page-view reporting can never use up a visitor's allowance for the forms.
        \Illuminate\Support\Facades\RateLimiter::for('forms', fn ($r) => \Illuminate\Cache\RateLimiting\Limit::perMinute(20)->by('forms|'.$r->ip()));
        \Illuminate\Support\Facades\RateLimiter::for('track', fn ($r) => \Illuminate\Cache\RateLimiting\Limit::perMinute(300)->by('track|'.$r->ip()));

        // Password reset and other notifications are sent with the SMTP account from Site settings > Email.
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Notifications\Events\NotificationSending::class, fn () => \App\Support\SiteMailer::useAsDefault());

        // Any content change refreshes the cached public pages.
        foreach (\App\Support\Site\PageCache::MODELS as $model) {
            $model::saved(fn () => \App\Support\Site\PageCache::flush());
            $model::deleted(fn () => \App\Support\Site\PageCache::flush());
        }
        Blade::directive('rt', fn ($e) => "<?php echo \\App\\Support\\Site\\Sanitizer::clean($e); ?>");
    }
}
