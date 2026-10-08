<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\ServiceItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ServiceChangesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('gtech:seed-content');
        \App\Support\Site\Repo::flush();
    }

    public function test_marketing_advisory_is_removed_and_its_address_redirects_to_services(): void
    {
        $this->assertFalse(ServiceItem::query()->where('slug', 'marketing-advisory')->exists());
        $this->assertNull(Page::find('service~marketing-advisory'));
        $this->get('/services/marketing-advisory')->assertRedirect('/services');
        $this->assertStringNotContainsString('marketing-advisory', $this->get('/services/branding-strategy')->getContent());
        $this->assertStringNotContainsString('/services/marketing-advisory', $this->get('/')->getContent());
    }

    public function test_ui_ux_design_and_print_media_are_under_branding_and_strategy_with_their_own_pages(): void
    {
        foreach (['ui-ux-design' => 'UI/UX Design', 'print-media' => 'Print Media'] as $slug => $name) {
            $item = ServiceItem::query()->where('slug', $slug)->firstOrFail();
            $this->assertSame('branding-strategy', $item->group_slug);
            $this->assertSame($name, $item->name);
            $html = $this->get("/services/$slug")->assertOk()->getContent();
            $this->assertStringContainsString("<h1>", $html);
            $this->assertStringContainsString($name, $html);
            $this->assertStringContainsString('"@type":"FAQPage"', $html);
            $this->assertStringContainsString('/services/'.$slug, $this->get('/services/branding-strategy')->getContent());
        }
    }

    public function test_the_change_is_the_same_on_a_fresh_install_and_when_run_again(): void
    {
        \App\Support\Site\ServiceUpdates::apply();
        \App\Support\Site\ServiceUpdates::apply();
        $this->assertSame(1, Page::query()->where('key', 'service~ui-ux-design')->count());
        $this->assertFalse(ServiceItem::query()->where('slug', 'marketing-advisory')->exists());
    }
}
