<?php

namespace Tests\Feature;

use App\Filament\Admin\Pages\SearchEngines;
use App\Models\Setting;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Livewire\Livewire;
use Tests\TestCase;

class SearchEnginesTest extends TestCase
{
    use RefreshDatabase;

    private const LIVE = 'http://www.gtechdigital.co.uk';

    protected function setUp(): void
    {
        parent::setUp();
        config(['gtech.blade_live' => true]);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Artisan::call('gtech:seed-content');
        \App\Support\Site\Repo::flush();
    }

    private function crawl(array $v): void
    {
        Setting::put('crawl', array_merge(Setting::DEFAULTS['crawl'], $v));
        \App\Support\Site\PageCache::flush();
    }

    public function test_admin_saves_the_settings(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'super_admin', 'active' => true]));
        $this->get(SearchEngines::getUrl())->assertOk()->assertSee('robots.txt');

        Livewire::test(SearchEngines::class)
            ->set('data.indexing', 'off')->set('data.robotsTxt', "User-agent: *\nDisallow: /private/\n\nSitemap: {sitemap}")
            ->set('data.sitemapSections', ['main', 'posts'])->call('save')->assertHasNoErrors();

        $s = Setting::group('crawl');
        $this->assertSame('off', $s['indexing']);
        $this->assertSame(['main', 'posts'], $s['sitemapSections']);
    }

    public function test_custom_robots_txt_and_indexing_switch(): void
    {
        $this->crawl(['robotsTxt' => "User-agent: *\nDisallow: /private/\nSitemap: {sitemap}"]);
        $this->get(self::LIVE.'/robots.txt')->assertSee('Disallow: /private/')->assertSee('/sitemap.xml')->assertDontSee('{sitemap}');

        $this->crawl(['indexing' => 'off']);
        $this->get(self::LIVE.'/case-studies')->assertHeader('X-Robots-Tag', 'noindex, nofollow')->assertSee('content="noindex, follow"', false);
    }

    public function test_robots_meta_defaults_and_page_types(): void
    {
        $this->crawl(['maxImagePreview' => 'large', 'noarchive' => true, 'noindexCaseStudies' => true]);
        $this->get(self::LIVE.'/about')->assertSee('content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1, noarchive"', false);
        $this->get(self::LIVE.'/case-studies/chefonline-seo')->assertSee('content="noindex, follow"', false);
    }

    public function test_sitemap_sections_exclusions_and_extras(): void
    {
        $this->get(self::LIVE.'/sitemap.xml')->assertSee('/case-studies/chefonline-seo')->assertSee('<priority>', false);

        $this->crawl(['sitemapSections' => ['main', 'services'], 'sitemapExclude' => "/terms\n/services/*", 'sitemapExtra' => '/extra-page', 'sitemapPriority' => false]);
        $this->get(self::LIVE.'/sitemap.xml')->assertDontSee('/case-studies/chefonline-seo')->assertDontSee('/terms<', false)
            ->assertDontSee('/services/search-engine-optimization')->assertSee('/extra-page')->assertSee('/about<', false)->assertDontSee('<priority>', false);
    }
}
