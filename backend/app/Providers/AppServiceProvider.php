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
        // Admin uploads: Livewire's temporary uploads stop at 12 MB by default; the hero video allows up to 50 MB.
        // (Each field still sets its own smaller limit, e.g. images.)
        config(['livewire.temporary_file_upload.rules' => ['required', 'file', 'max:51200']]);

        // Breezy registers its My account components only while a panel boots, which Livewire's update route
        // skips, so their actions failed with 419. Register them for every request.
        foreach (['personal_info' => \Jeffgreco13\FilamentBreezy\Livewire\PersonalInfo::class, 'update_password' => \Jeffgreco13\FilamentBreezy\Livewire\UpdatePassword::class,
            'two_factor_authentication' => \Jeffgreco13\FilamentBreezy\Livewire\TwoFactorAuthentication::class, 'browser_sessions' => \Jeffgreco13\FilamentBreezy\Livewire\BrowserSessions::class] as $name => $class) {
            \Livewire\Livewire::component($name, $class);
        }

        \App\Models\Lead::observe(\App\Observers\LeadObserver::class);

        Auth::provider('legacy-eloquent', fn ($app, array $config) => new LegacyAwareUserProvider($app['hash'], $config['model']));

        // Public website (Blade): shared data and the same helpers the React site used.
        View::composer('site.*', SiteComposer::class);
        Blade::directive('icon', fn ($e) => "<?php echo \\App\\Support\\Site\\Icons::svg($e); ?>");
        Blade::directive('hl', fn ($e) => "<?php echo \\App\\Support\\Site\\Hl::html($e); ?>");
        // One password rule everywhere (users screen, invitations, password reset, My account).
        \Illuminate\Validation\Rules\Password::defaults(fn () => \Illuminate\Validation\Rules\Password::min(10)->letters()->numbers());

        // Separate rate limits, so page-view reporting can never use up a visitor's allowance for the forms.
        \Illuminate\Support\Facades\RateLimiter::for('forms', fn ($r) => \Illuminate\Cache\RateLimiting\Limit::perMinute(20)->by('forms|'.$r->ip()));
        \Illuminate\Support\Facades\RateLimiter::for('track', fn ($r) => \Illuminate\Cache\RateLimiting\Limit::perMinute(300)->by('track|'.$r->ip()));

        // Password reset and other notifications are sent with the SMTP account from Site settings > Email.
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Notifications\Events\NotificationSending::class, fn () => \App\Support\SiteMailer::useAsDefault());
        // Email dashboard: every email the site sends is recorded under Sent.
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Mail\Events\MessageSent::class, [\App\Support\Mail\MailLog::class, 'sent']);

        // Any content change refreshes the cached public pages.
        foreach (\App\Support\Site\PageCache::MODELS as $model) {
            $model::saved(fn () => \App\Support\Site\PageCache::flush());
            $model::deleted(fn () => \App\Support\Site\PageCache::flush());
        }
        Blade::directive('rt', fn ($e) => "<?php echo \\App\\Support\\Site\\Sanitizer::clean($e); ?>");
    }
}
