<?php

namespace Tests\Feature;

use App\Filament\Admin\Pages\LinkMap;
use App\Filament\Admin\Pages\SeoAudit;
use App\Filament\Admin\Pages\SeoDashboard;
use App\Models\Page;
use App\Models\SeoKeyword;
use App\Models\User;
use App\Support\Html;
use App\Support\Seo\Audit;
use App\Support\Seo\LinkGraph;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Livewire\Livewire;
use Tests\TestCase;

class SeoToolsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Livewire\Features\SupportAutoInjectedAssets\SupportAutoInjectedAssets::$hasRenderedAComponentThisRequest = false;
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Artisan::call('gtech:seed-content');
        \App\Support\Site\Repo::flush();
    }

    private function as(string $role): User
    {
        $u = User::factory()->create(['role' => $role, 'active' => true]);
        $this->actingAs($u);
        return $u;
    }

    public function test_bullet_points_get_a_paragraph_in_the_editor_and_lose_it_on_save(): void
    {
        $stored = '<p>Intro</p><ul><li>Answer <strong>one</strong> question</li><li>Two<ul><li>nested</li></ul></li></ul>';
        $editor = Html::listsForEditor($stored);
        $this->assertSame('<p>Intro</p><ul><li><p>Answer <strong>one</strong> question</p></li><li><p>Two</p><ul><li><p>nested</p></li></ul></li></ul>', $editor);
        $this->assertSame($stored, Html::listsForSite($editor));
        $this->assertSame('<p>No lists</p>', Html::listsForEditor('<p>No lists</p>'));
    }

    public function test_the_link_picker_lists_live_pages_posts_and_case_studies(): void
    {
        $o = \App\Filament\Support\SimpleLinkAction::sitePages();
        $this->assertSame('Local SEO', $o['Services']['/services/local-seo']);
        $this->assertArrayHasKey('/about', $o['Main pages']);
        $this->assertNotEmpty($o['Blog posts']);
    }

    public function test_the_link_graph_and_audit_cover_every_page(): void
    {
        $g = LinkGraph::build();
        $stats = LinkGraph::stats($g);
        $this->assertArrayHasKey('/services/local-seo', $g['nodes']);
        $this->assertGreaterThan(0, $stats['/services/local-seo']['in']);
        $this->assertSame([], LinkGraph::broken($g));

        $a = Audit::site(true);
        $this->assertSame(Page::query()->whereIn('kind', ['service', 'industry'])->count(), $a['summary']['pages']);
        $p = collect($a['pages'])->firstWhere('path', '/services/local-seo');
        $this->assertSame('local seo services uk', $p['keyword']);
        $this->assertGreaterThan(500, $p['stats']['words']);
        foreach (['SEO', 'AEO', 'GEO'] as $g) $this->assertNotEmpty(array_filter($p['checks'], fn ($c) => $c['group'] === $g));
        $this->assertNotEmpty($a['posts']);
    }

    public function test_a_broken_semantic_link_and_an_orphan_are_reported(): void
    {
        $k = SeoKeyword::query()->findOrFail('local-seo');
        $k->update(['links' => [...$k->links, ['target' => 'no-such-service', 'anchor' => 'x']]]);
        $a = Audit::site(true);
        $this->assertContains('/services/local-seo → /services/no-such-service', $a['broken']);

        Page::query()->create(['key' => 'landing~lonely', 'kind' => 'landing', 'slug' => 'lonely', 'name' => 'Lonely', 'path' => '/lonely', 'published' => true,
            'hero' => ['h1' => 'Lonely', 'lead' => ''], 'sections' => [], 'faqs' => [], 'related' => [], 'data' => []]);
        $this->assertContains('/lonely', Audit::site(true)['orphans']);
    }

    public function test_a_less_than_sign_in_the_copy_does_not_hide_the_rest_of_the_page(): void
    {
        $p = Page::query()->findOrFail('service~local-seo');
        $before = collect(Audit::site(true)['pages'])->firstWhere('path', $p->path)['stats']['words'];
        $s = $p->sections;
        array_unshift($s, ['type' => 'text', 'id' => 'lt', 'heading' => 'Speed', 'paras' => ['Results in < 3 months']]);
        $p->update(['sections' => $s]);
        $after = collect(Audit::site(true)['pages'])->firstWhere('path', $p->path)['stats']['words'];
        $this->assertGreaterThan($before, $after);
    }

    public function test_the_seo_screens_load_for_seo_managers_only(): void
    {
        $this->as('seo');
        foreach ([SeoDashboard::getUrl(), SeoAudit::getUrl(), SeoAudit::getUrl(['tab' => 'posts']), SeoAudit::getUrl(['tab' => 'keywords']), SeoAudit::getUrl(['tab' => 'links']), LinkMap::getUrl()] as $url) {
            $this->get($url)->assertOk();
        }
        Livewire::test(SeoAudit::class)->call('toggle', '/services/local-seo')->assertSee('Primary keyword in the SEO title');
        $this->as('sales');
        $this->get(SeoDashboard::getUrl())->assertForbidden();
        $this->get(LinkMap::getUrl())->assertForbidden();
    }
}
