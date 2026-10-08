<?php

namespace Tests\Feature;

use App\Filament\Admin\Pages\PageWidgets;
use App\Filament\Support\PageBlocks;
use App\Filament\Support\WidgetCatalog;
use App\Models\Page;
use App\Models\User;
use App\Support\Site\PageCache;
use App\Support\Site\Repo;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Livewire\Livewire;
use Tests\TestCase;

class PageWidgetsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Livewire\Features\SupportAutoInjectedAssets\SupportAutoInjectedAssets::$hasRenderedAComponentThisRequest = false;
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Artisan::call('gtech:seed-content');
        Repo::flush();
    }

    private function landing(array $sections, array $data = []): Page
    {
        $p = Page::query()->create(['key' => 'landing~ads-test', 'kind' => 'landing', 'slug' => 'ads-test', 'name' => 'Ads test', 'path' => '/ads-test',
            'published' => true, 'hero' => ['h1' => 'Ads test', 'lead' => 'Lead'], 'sections' => $sections, 'faqs' => [], 'related' => [], 'data' => $data]);
        PageCache::flush();
        Repo::flush();
        return $p;
    }

    public function test_every_widget_has_information_in_the_admin(): void
    {
        $this->assertEqualsCanonicalizing(array_keys(PageBlocks::LABELS), array_keys(WidgetCatalog::all()));
        foreach (WidgetCatalog::all() as $key => $w) {
            $this->assertContains($w['group'], WidgetCatalog::GROUPS, $key);
            foreach (['use', 'best', 'example'] as $f) $this->assertNotSame('', $w[$f], "$key $f");
        }
    }

    public function test_the_widgets_page_is_for_page_editors_only(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'sales', 'active' => true]));
        $this->get('/admin/page-widgets')->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => 'editor', 'active' => true]));
        $this->get('/admin/page-widgets')->assertOk()->assertSee('Call to action banner')->assertSee('Pricing plans');
    }

    public function test_video_links_are_turned_into_embeds_and_anything_else_is_dropped(): void
    {
        $this->assertSame('youtube:dQw4w9WgXcQ', PageBlocks::videoId('https://www.youtube.com/watch?v=dQw4w9WgXcQ&t=10'));
        $this->assertSame('youtube:dQw4w9WgXcQ', PageBlocks::videoId('https://youtu.be/dQw4w9WgXcQ'));
        $this->assertSame('vimeo:123456789', PageBlocks::videoId('https://vimeo.com/123456789'));
        $this->assertSame('', PageBlocks::videoId('https://evil.example/watch?v=dQw4w9WgXcQ'));
        $this->assertSame('/contact', PageBlocks::link('javascript:alert(1)'));
        $this->assertSame('#inquiry', PageBlocks::link('#inquiry'));
    }

    public function test_the_new_widgets_are_saved_and_shown_on_the_site(): void
    {
        $sections = PageBlocks::fromBuilder([
            ['type' => 'cta', 'data' => ['id' => 'cta-one', 'heading' => 'Ready for [[more enquiries]]?', 'text' => 'Talk to us today.', 'button' => 'Get a Free Audit', 'link' => '#inquiry', 'tone' => 'dark']],
            ['type' => 'faq', 'data' => ['id' => 'faq-block', 'heading' => 'Questions', 'items' => [['title' => 'How long does it take?', 'text' => 'About six weeks.']]]],
            ['type' => 'video', 'data' => ['id' => 'film', 'heading' => 'Watch', 'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'caption' => 'Two minutes']],
            ['type' => 'pricing', 'data' => ['id' => 'prices', 'heading' => 'Plans', 'plans' => [[
                'name' => 'Growth', 'price' => '£599', 'period' => 'per month', 'text' => 'For growing firms', 'features' => [['html' => 'Monthly report'], ['html' => 'Two calls a month']],
                'highlight' => true, 'button' => 'Start', 'link' => '#inquiry']]]],
            ['type' => 'form', 'data' => ['id' => 'enquire', 'heading' => 'Get a free audit']],
        ]);
        $this->assertSame('dQw4w9WgXcQ', explode(':', $sections[2]['video'])[1]);
        $this->landing($sections);

        $html = $this->get('/ads-test')->assertOk()->getContent();
        foreach (['Ready for <span class="hl">more enquiries</span>?', 'sp-cta dark', 'Get a Free Audit', 'class="sp-acc-item"', 'youtube-nocookie.com/embed/dQw4w9WgXcQ',
            '£599', 'sp-plan hot', 'Monthly report', 'free audit</span>', 'data-form="inquiry"'] as $needle) {
            $this->assertStringContainsString($needle, $html, $needle);
        }
    }

    public function test_a_focus_landing_page_hides_the_menu_and_footer_and_the_phone_bar_only_when_switched_on(): void
    {
        $sections = [['type' => 'text', 'id' => 'a', 'heading' => 'Hello', 'paras' => ['<p>Hi</p>']]];
        $this->landing($sections, ['focus' => true, 'stickyCta' => true, 'cta' => 'Book now']);
        $html = $this->get('/ads-test')->assertOk()->getContent();
        $this->assertStringNotContainsString('<header class="hdr">', $html);
        $this->assertStringNotContainsString('<footer class="ft">', $html);
        $this->assertStringContainsString('class="lp-sticky"', $html);
        $this->assertStringContainsString('Book now', $html);

        Page::query()->where('key', 'landing~ads-test')->update(['data' => ['cta' => 'Book now']]);
        PageCache::flush();
        Repo::flush();
        $normal = $this->get('/ads-test')->assertOk()->getContent();
        $this->assertStringContainsString('<header class="hdr">', $normal);
        $this->assertStringNotContainsString('class="lp-sticky"', $normal);
    }
}
