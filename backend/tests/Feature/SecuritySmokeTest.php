<?php

namespace Tests\Feature;

use App\Filament\Auth\Login;
use App\Models\Setting;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SecuritySmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Livewire\Features\SupportAutoInjectedAssets\SupportAutoInjectedAssets::$hasRenderedAComponentThisRequest = false;
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_public_settings_never_contain_secrets(): void
    {
        Setting::query()->updateOrCreate(['key' => 'forms'], ['value' => ['turnstileSite' => 'site-key', 'turnstileSecret' => 'TOP-SECRET', 'privacyNotice' => 'x']]);
        Setting::query()->updateOrCreate(['key' => 'leads'], ['value' => ['webhook' => 'https://hooks.example/SECRET']]);
        Setting::query()->updateOrCreate(['key' => 'smtp'], ['value' => ['pass' => 'enc-SECRET']]);
        \Illuminate\Support\Facades\Cache::flush();
        $this->getJson('/api/v1/settings')->assertOk()->assertJsonPath('settings.forms.turnstileSite', 'site-key')
            ->assertDontSee('SECRET')->assertJsonMissingPath('settings.leads')->assertJsonMissingPath('settings.smtp');
    }

    public function test_wrong_passwords_are_throttled_then_lock_the_account(): void
    {
        User::factory()->create(['email' => 'boss@example.com', 'password' => bcrypt('Right-pass-12345'), 'role' => 'admin', 'active' => true]);
        foreach (range(1, Login::MAX_FAILURES) as $i) {
            Livewire::test(Login::class)->set('data.email', 'boss@example.com')->set('data.password', "wrong-$i")->call('authenticate');
            \Illuminate\Support\Facades\RateLimiter::clear('livewire-rate-limiter:'.sha1(Login::class.'|authenticate|127.0.0.1'));
        }
        // Even the right password is refused while the account is locked.
        Livewire::test(Login::class)->set('data.email', 'boss@example.com')->set('data.password', 'Right-pass-12345')->call('authenticate')
            ->assertHasErrors(['data.email']);
        $this->assertGuest();
    }

    public function test_admin_screens_need_a_login_and_the_right_role(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/users')->assertRedirect('/admin/login');
        $this->actingAs(User::factory()->create(['role' => 'sales', 'active' => true]));
        $this->get('/admin/users')->assertForbidden();
        $this->get('/admin/settings')->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => 'admin', 'active' => false]));
        $this->get('/admin')->assertForbidden();
    }

    public function test_content_api_needs_the_token(): void
    {
        config(['gtech.api_token' => 'tok-123']);
        $this->postJson('/api/v1/query')->assertUnauthorized();
        $this->postJson('/api/v1/query', [], ['X-Api-Key' => 'nope'])->assertUnauthorized();
        config(['gtech.api_token' => '']);
        $this->postJson('/api/v1/query', [], ['X-Api-Key' => ''])->assertUnauthorized();
    }

    public function test_uploaded_svgs_lose_their_scripts(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        \Illuminate\Support\Facades\Storage::disk('public')->put('media/x.svg', '<svg xmlns="http://www.w3.org/2000/svg" onload="alert(1)"><script>alert(2)</script><a href="javascript:alert(3)"><rect width="5" height="5"/></a><image href="https://evil.example/x.png"/></svg>');
        \App\Models\Media::create(['name' => 'x.svg', 'type' => 'image/svg+xml', 'size' => 1, 'path' => 'media/x.svg']);
        $svg = \Illuminate\Support\Facades\Storage::disk('public')->get('media/x.svg');
        $this->assertStringContainsString('<rect', $svg);
        foreach (['<script', 'onload', 'javascript:', 'evil.example'] as $bad) $this->assertStringNotContainsString($bad, $svg);
    }

    public function test_security_headers_and_tag_manager_are_on_every_page(): void
    {
        \Illuminate\Support\Facades\Artisan::call('gtech:seed-content');
        $r = $this->get('/')->assertOk()->assertHeader('X-Frame-Options', 'SAMEORIGIN')->assertHeader('X-Content-Type-Options', 'nosniff');
        $html = $r->getContent();
        $this->assertStringContainsString("'dataLayer','GTM-NRPJVVSH'", $html);
        $this->assertMatchesRegularExpression('#<body>\s*<!-- Google Tag Manager \(noscript\) --><noscript><iframe src="https://www.googletagmanager.com/ns.html\?id=GTM-NRPJVVSH"#', $html);
        $this->get('/admin/login')->assertHeader('X-Frame-Options', 'SAMEORIGIN');

        // Editable in Site settings; an invalid value is never printed into the page.
        \App\Models\Setting::put('tracking', ['gtmId' => "GTM-X');alert(1);//"]);
        \App\Support\Site\PageCache::flush();
        $this->assertStringNotContainsString('alert(1)', $this->get('/about')->getContent());
    }
}
