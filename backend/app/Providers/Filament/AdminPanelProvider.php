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
            ->passwordReset(resetAction: \App\Filament\Auth\ResetPassword::class)
            ->brandName('GTech Digital Admin')
            ->favicon(asset('favicon.ico'))
            ->colors(['primary' => Color::hex('#e8202f'), 'gray' => Color::Zinc])
            ->font('Inter')
            // Dark by default; anyone can switch to light (or follow their device) from the avatar menu.
            ->defaultThemeMode(\Filament\Enums\ThemeMode::Dark)
            ->renderHook(\Filament\View\PanelsRenderHook::USER_MENU_BEFORE, fn () => view('filament.admin.partials.theme-toggle'))
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
                NavigationGroup::make('SEO'),
                NavigationGroup::make('Site structure')->collapsed(),
                NavigationGroup::make('Blog'),
                NavigationGroup::make('Leads'),
                NavigationGroup::make('Email'),
                NavigationGroup::make('Image tools')->collapsed(),
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
                // Article editor (ArticleEditor) copies the text into the form after a short pause; copy it straight away when
                // the editor loses focus or a form is sent, so Save never misses the last words typed.
                ."<script>(function(){function flush(el){try{var d=window.Alpine&&Alpine.\$data(el);if(!d||!el.__typedAt||Date.now()-el.__typedAt>1000||typeof d.editor!=='function')return;el.__typedAt=0;clearTimeout(d.timeOut);d.timeOut=null;var ed=d.editor();if(ed)d.state=ed.isEmpty?null:ed.getJSON()}catch(e){}}"
                ."document.addEventListener('input',function(e){var w=e.target.closest&&e.target.closest('.tiptap-wrapper');if(w)w.__typedAt=Date.now()},true);function all(){document.querySelectorAll('.tiptap-wrapper').forEach(flush)}"
                ."document.addEventListener('focusout',function(e){var w=e.target.closest&&e.target.closest('.tiptap-wrapper');if(w)flush(w)},true);"
                ."document.addEventListener('submit',all,true);"
                // The editor plugin reloads its content whenever the form state comes back from the server and then focuses itself,
                // which pulled people back into the article (e.g. when picking categories) and made the link and table menus blink.
                // Reload only when the content really changed, and never take the focus.
                ."function patch(){if(!window.Alpine)return;document.querySelectorAll('.tiptap-wrapper').forEach(function(el){var d;try{d=Alpine.\$data(el)}catch(e){return}if(!d||d.__gtPatched||typeof d.editor!=='function'||!d.editor())return;d.__gtPatched=true;d.updateEditorContent=function(content){var ed=this.editor();if(!ed||!ed.isEditable)return;try{if(content&&typeof content==='object'&&JSON.stringify(content)===JSON.stringify(ed.getJSON()))return;if(typeof content==='string'&&content===ed.getHTML())return}catch(e){}var had=ed.isFocused,sel=ed.state.selection;ed.commands.setContent(content,false);if(had){try{ed.commands.setTextSelection({from:sel.from,to:sel.to})}catch(e){}}}})}setInterval(patch,500);document.addEventListener('focusin',patch,true);document.addEventListener('pointerdown',function(e){var t=e.target;if(t.closest&&t.closest('button,a')&&!t.closest('.tiptap-wrapper,.fi-modal'))all()},true)})()</script>"
            ));
    }
}
