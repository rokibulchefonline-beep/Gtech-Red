<?php

namespace Tests\Feature;

use App\Models\CaseStudy;
use App\Models\Lead;
use App\Models\Post;
use App\Models\Redirect;
use App\Models\Setting;
use App\Models\User;
use App\Support\Site\Blog;
use App\Support\SiteMailer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AdminFixesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Livewire remembers rendering an admin page in a static flag; a real request starts fresh.
        \Livewire\Features\SupportAutoInjectedAssets\SupportAutoInjectedAssets::$hasRenderedAComponentThisRequest = false;
        Artisan::call('gtech:seed-content');
        \App\Support\Site\Repo::flush();
        \App\View\SiteComposer::flush();
    }

    public function test_changing_a_slug_keeps_the_old_address_working(): void
    {
        $p = Post::query()->firstOrFail(); $old = $p->slug;
        $p->update(['slug' => 'renamed-once']);
        $p->update(['slug' => 'renamed-twice']);
        \App\Support\Site\Repo::flush();
        $this->get("/blogs/$old")->assertStatus(301)->assertRedirect('/blogs/renamed-twice');
        $this->get('/blogs/renamed-once?utm_source=x')->assertRedirect('/blogs/renamed-twice?utm_source=x');
        $this->get('/blogs/renamed-twice')->assertOk();

        // Back to the first address: no loop, the live page wins.
        $p->update(['slug' => $old]);
        \App\Support\Site\Repo::flush(); // a new request would start with fresh data
        $this->get("/blogs/$old")->assertOk();
        $this->assertSame(0, Redirect::query()->where('from_path', "/blogs/$old")->count());

        $c = CaseStudy::query()->where('status', 'published')->firstOrFail(); $oldC = $c->slug;
        $c->update(['slug' => 'new-case-address']);
        $this->get("/case-studies/$oldC")->assertRedirect('/case-studies/new-case-address');
        $this->get('/never-was-a-page')->assertNotFound();
    }

    public function test_empty_optional_fields_save_as_blank(): void
    {
        $p = Post::query()->firstOrFail();
        $p->update(['image_alt' => null, 'meta_title' => null, 'excerpt' => null]);
        $this->assertSame('', $p->fresh()->image_alt);
        $this->assertSame('', $p->fresh()->meta_title);
    }

    public function test_markdown_posts_open_as_html(): void
    {
        $this->assertSame("<h2>Intro</h2>\n<p>Some <strong>bold</strong> and <a href=\"/x\">a link</a>.</p>\n<ul><li>one</li><li>two</li></ul>",
            Blog::markdownToHtml("## Intro\nSome **bold** and [a link](/x).\n\n- one\n- two"));
    }

    public function test_notifications_use_the_smtp_account_from_settings(): void
    {
        Setting::put('smtp', ['host' => 'smtp.example.com', 'port' => 587, 'user' => 'u', 'fromEmail' => 'noreply@example.com', 'fromName' => 'GTech'] + Setting::group('smtp'));
        SiteMailer::useAsDefault();
        $this->assertSame('site', config('mail.default'));
        $this->assertSame('smtp.example.com', config('mail.mailers.site.host'));
        $this->assertSame('noreply@example.com', config('mail.from.address'));
    }

    public function test_lead_phone_numbers_become_international(): void
    {
        $this->assertSame('447700900123', (new Lead(['phone' => '07700 900123']))->phoneDigits());
        $this->assertSame('447700900123', (new Lead(['phone' => '+44 7700 900123']))->phoneDigits());
    }

    public function test_publish_site_button_only_before_go_live(): void
    {
        $u = User::query()->create(['name' => 'Admin', 'email' => 'a@example.com', 'password' => bcrypt('x-'.uniqid()), 'role' => 'super_admin', 'active' => true]);
        $this->actingAs($u)->get('/admin')->assertOk()->assertSee('Publish site')->assertDontSee('Refresh website');
        config(['gtech.blade_live' => true]);
        $this->actingAs($u)->get('/admin')->assertOk()->assertDontSee('Publish site')->assertSee('Refresh website');
    }
}
