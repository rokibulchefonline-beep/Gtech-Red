<?php

namespace Tests\Feature;

use App\Models\SeoEntry;
use App\Support\Site\Repo;
use App\Support\Site\Schema;
use App\View\SiteComposer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class BladeSeoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Livewire remembers rendering an admin page in a static flag; a real request starts fresh.
        \Livewire\Features\SupportAutoInjectedAssets\SupportAutoInjectedAssets::$hasRenderedAComponentThisRequest = false;
        Artisan::call('gtech:seed-content');
        Repo::flush();
        SiteComposer::flush();
    }

    private function graph(string $html): array
    {
        preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $m);
        return json_decode($m[1], true)['@graph'];
    }

    public function test_service_page_head_and_structured_data(): void
    {
        $html = $this->get('/services/local-seo')->getContent();
        $this->assertStringContainsString('<link rel="canonical" href="https://www.gtechdigital.co.uk/services/local-seo">', $html);
        $this->assertStringContainsString('<meta property="og:image" content="https://www.gtechdigital.co.uk/services/localseo.webp">', $html);
        $types = array_map(fn ($n) => is_array($n['@type']) ? implode('+', $n['@type']) : $n['@type'], $this->graph($html));
        $this->assertSame(['Organization+ProfessionalService', 'WebSite', 'WebPage', 'BreadcrumbList', 'Service', 'FAQPage'], $types);
    }

    public function test_panel_overrides_win(): void
    {
        SeoEntry::create(['key' => 'services~local-seo', 'path' => '/services/local-seo', 'title' => 'Custom title', 'description' => 'Custom description', 'noindex' => true, 'schema_custom' => '{"@type":"Thing","name":"a</script>"}']);
        $html = $this->get('/services/local-seo')->getContent();
        $this->assertStringContainsString('<title>Custom title</title>', $html);
        $this->assertStringContainsString('<meta name="robots" content="noindex, follow">', $html);
        $this->assertStringContainsString('"a'.chr(92).'u003c/script>"', $html);
        $this->assertStringNotContainsString('<loc>https://www.gtechdigital.co.uk/services/local-seo</loc>', $this->get('/sitemap.xml')->getContent());

        SeoEntry::query()->find('services~local-seo')->update(['schema_off' => true, 'schema_custom' => '']);
        $this->assertStringNotContainsString('application/ld+json', $this->get('/services/local-seo')->getContent());
    }

    public function test_sitemap_robots_and_redirects(): void
    {
        $xml = $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8')->getContent();
        $this->assertSame(1, substr_count($xml, '<loc>https://www.gtechdigital.co.uk</loc>'));
        $this->assertStringContainsString('<loc>https://www.gtechdigital.co.uk/services/local-seo</loc>', $xml);
        $this->assertStringContainsString('<loc>https://www.gtechdigital.co.uk/industries/travel</loc>', $xml);

        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /', false);
        config(['gtech.blade_live' => true]);
        $this->get('/robots.txt')->assertSee('Sitemap: https://www.gtechdigital.co.uk/sitemap.xml', false)->assertSee('Disallow: /admin/', false);

        $this->get('/quote')->assertStatus(308)->assertRedirect('/contact');
        $this->get('/blog/some-post')->assertStatus(308)->assertRedirect('/blogs/some-post');
        $this->get('/services/digital-marketing/local-seo')->assertStatus(308)->assertRedirect('/services/local-seo');
    }

    public function test_custom_schema_validation(): void
    {
        $this->assertNull(Schema::validateCustom(''));
        $this->assertNull(Schema::validateCustom('{"@type":"Thing"}'));
        $this->assertSame('Custom schema is not valid JSON.', Schema::validateCustom('{nope'));
        $this->assertSame('Each custom schema object needs an @type (or an @graph).', Schema::validateCustom('[{"name":"x"}]'));
    }
}
