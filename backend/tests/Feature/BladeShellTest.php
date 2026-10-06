<?php

namespace Tests\Feature;

use App\Support\Site\Hl;
use App\Support\Site\Icons;
use App\Support\Site\Sanitizer;
use App\View\SiteComposer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class BladeShellTest extends TestCase
{
    use RefreshDatabase;

    public function test_heading_highlights_match_the_website(): void
    {
        $this->assertSame('Local SEO <span class="hl">Agency UK</span>', Hl::html('Local SEO [[Agency UK]]'));
        $this->assertSame('Plain heading ', Hl::html('Plain heading [[]]'));
        $this->assertSame('<span class="hl">On-Page SEO</span>: Content and Links', Hl::html('On-Page SEO: Content and Links'));
        $this->assertSame('How Much Does a <span class="hl">Mobile App</span> Cost?', Hl::html('How Much Does a Mobile App Cost?'));
        $this->assertSame('Shopify vs <span class="hl">WooCommerce</span>', Hl::html('Shopify vs WooCommerce'));
        $this->assertSame('Tom &amp; Co ', Hl::html('Tom & Co [[]]'));
    }

    public function test_sanitizer_drops_unsafe_markup(): void
    {
        $out = Sanitizer::clean('<p onclick="x()">Hi <a href="javascript:alert(1)">bad</a> <a href="/ok" target="_blank">ok</a></p><script>x</script><h2>Dup</h2><h2>Dup</h2>');
        $this->assertSame('<p>Hi <a>bad</a> <a href="/ok" target="_blank" rel="noopener noreferrer">ok</a></p><h2 id="dup">Dup</h2><h2 id="dup-2">Dup</h2>', $out);
    }

    public function test_icons_render_like_the_react_component(): void
    {
        $this->assertStringStartsWith('<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">', Icons::svg('lucide:check', 16));
        $this->assertSame('', Icons::svg('lucide:not-a-real-icon'));
    }

    public function test_layout_renders_menus_from_the_database(): void
    {
        Artisan::call('gtech:seed-content');
        SiteComposer::flush();
        $html = $this->get('/blade-preview')->assertOk()->getContent();
        foreach (['<header class="hdr">', 'Search Engine Optimization', 'Technology &amp; SaaS', '<footer class="ft">', 'id="cm-tpl"', 'class="ck"', 'class="to-top"', 'class="float-talk"', 'Blade Layout Preview of <span class="hl">GTech Digital</span>'] as $s) {
            $this->assertStringContainsString($s, $html);
        }
        $this->assertSame(5, substr_count($html, 'class="mega-pane'));
    }

    public function test_forms_post_to_the_same_address_as_before(): void
    {
        $this->postJson('/api/contact', ['name' => 'Jo', 'company' => 'Co', 'email' => 'jo@example.com', 'phone' => '0700', 'service' => 'SEO', 'source' => 'inquiry'])->assertOk()->assertJson(['ok' => true]);
        $this->postJson('/api/subscribe', ['email' => 'a@example.com'])->assertOk();
    }
}
