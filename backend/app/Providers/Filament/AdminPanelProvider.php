<?php

namespace App\Providers\Filament;

use App\Filament\Admin\Pages\Dashboard;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(\App\Filament\Auth\Login::class)
            ->passwordReset()
            ->brandName('GTech Digital Admin')
            ->favicon(asset('favicon.ico'))
            ->colors(['primary' => Color::hex('#e8202f'), 'gray' => Color::Zinc])
            ->font('Inter')
            // My account (name, email, password), two-factor sign-in with an authenticator app, and signed-in devices.
            ->plugin(\Jeffgreco13\FilamentBreezy\BreezyCore::make()
                ->myProfile(shouldRegisterUserMenu: true, userMenuLabel: 'My account', slug: 'my-account')
                ->passwordUpdateRules([\Illuminate\Validation\Rules\Password::default()])
                ->enableTwoFactorAuthentication(force: fn () => \App\Support\Security::requiresTwoFactor(auth()->user()))
                ->enableBrowserSessions())
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->defaultAvatarProvider(\App\Filament\Support\InitialsAvatar::class)
            ->sidebarCollapsibleOnDesktop()
            ->navigationGroups([
                NavigationGroup::make('Website content'),
                NavigationGroup::make('Site structure')->collapsed(),
                NavigationGroup::make('Blog'),
                NavigationGroup::make('Leads'),
                NavigationGroup::make('Settings'),
            ])
            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\\Filament\\Admin\\Resources')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\\Filament\\Admin\\Pages')
            ->pages([Dashboard::class])
            ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\\Filament\\Admin\\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([Authenticate::class])
            // On phones and small tablets the menu starts closed, so the page is visible straight away. The gt_staff flag stops
            // this browser's own visits to the website being counted in Analytics.
            ->renderHook(\Filament\View\PanelsRenderHook::BODY_END, fn () => new \Illuminate\Support\HtmlString(
                "<script>try{localStorage.setItem('gt_staff','1')}catch(e){}document.addEventListener('alpine:initialized',function(){if(window.innerWidth<1024&&window.Alpine&&Alpine.store('sidebar'))Alpine.store('sidebar').close()})</script>"
            ));
    }
}
