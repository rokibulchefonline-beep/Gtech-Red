<?php

namespace Tests\Feature;

use App\Filament\Admin\Pages\SeoAudit;
use App\Filament\Support\SchemaPanel;
use App\Models\Industry;
use App\Models\Page;
use App\Models\Post;
use App\Models\SeoEntry;
use App\Models\User;
use App\Support\Seo\LinkGraph;
use App\Support\Site\RemoveIndustry;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Livewire\Livewire;
use Tests\TestCase;

class SeoEditorsTest extends TestCase
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

    public function test_removed_industries_are_gone_from_the_seed_content(): void
    {
        $this->assertSame(0, Industry::query()->whereIn('slug', ['education', 'healthcare', 'finance'])->count());
        $this->assertNull(Page::find('industry~education'));
    }

    public function test_removing_an_industry_removes_its_page_and_every_link_to_it(): void
    {
        $page = Page::find('industry~travel');
        $about = Page::find('page~about');
        $about->forceFill(['faqs' => [['q' => 'Sectors?', 'a' => 'See <a href="/industries/travel">travel</a> and <a href="/industries/automotive">cars</a>.']]])->save();
        Post::create(['title' => 'Hi', 'slug' => 'hi', 'status' => 'published', 'body' => '<p>Read <a href="https://www.gtechdigital.co.uk/industries/travel/">our travel work</a>.</p>']);

        RemoveIndustry::run(['travel']);
        \App\Support\Site\Repo::flush();

        $this->assertNull(Industry::query()->where('slug', 'travel')->first());
        $this->assertNull(Page::find($page->key));
        $this->assertSame('See travel and <a href="/industries/automotive">cars</a>.', Page::find('page~about')->faqs[0]['a']);
        $this->assertSame('<p>Read our travel work.</p>', Post::query()->where('slug', 'hi')->value('body'));
        $this->get('/industries/travel')->assertRedirect('/industries');
        $this->get('/')->assertOk()->assertDontSee('/industries/travel', false);
        $this->get('/industries')->assertOk()->assertDontSee('/industries/travel', false);
        $this->assertSame([], array_filter(LinkGraph::build()['edges'], fn ($e) => $e['to'] === '/industries/travel'));
    }

    public function test_schema_panel_saves_custom_json_ld_that_appears_on_the_page(): void
    {
        Post::create(['title' => 'Guide', 'slug' => 'guide', 'status' => 'published', 'body' => '<p>x</p>']);
        SchemaPanel::save('/blogs/guide', ['off' => false, 'custom' => '{"@context":"https://schema.org","@type":"HowTo","name":"Do it"}']);
        $this->assertSame('{"@context":"https://schema.org","@type":"HowTo","name":"Do it"}', SchemaPanel::fill('/blogs/guide')['custom']);
        $this->get('/blogs/guide')->assertOk()->assertSee('"@type":"HowTo"', false);

        // Nothing set: no empty override is created.
        SchemaPanel::save('/blogs/other', ['off' => false, 'custom' => '']);
        $this->assertNull(SeoEntry::query()->find(SeoEntry::keyFor('/blogs/other')));
    }

    public function test_the_audit_pages_through_many_posts(): void
    {
        foreach (range(1, 30) as $i) Post::create(['title' => "Post $i", 'slug' => "post-$i", 'status' => 'published', 'body' => '<p>x</p>']);
        $this->actingAs(User::factory()->create(['role' => 'admin', 'active' => true]));
        $c = Livewire::test(SeoAudit::class)->set('tab', 'posts');
        $c->assertSee('Post 1')->call('goTo', 2)->assertSet('p', 2);
        $c->set('q', 'Post 3')->assertSet('p', 1);
    }

    public function test_links_written_in_a_post_show_on_the_link_map_by_themselves(): void
    {
        $this->assertSame([['/services/local-seo', 'local SEO']], LinkGraph::links('<p><a href="https://gtechdigital.co.uk/services/local-seo/?x=1">local SEO</a> <a href="/images/a.png">img</a> <a href="https://other.com/x">x</a></p>'));
        Post::create(['title' => 'Linky', 'slug' => 'linky', 'status' => 'published', 'body' => '<p>Try <a href="/services/local-seo">local SEO</a> and <a href="/services/nope">this</a>.</p>']);
        $g = LinkGraph::build();
        $this->assertArrayHasKey('/blogs/linky', $g['nodes']);
        $this->assertNotEmpty(array_filter($g['edges'], fn ($e) => $e['from'] === '/blogs/linky' && $e['to'] === '/services/local-seo' && $e['type'] === 'content'));
        $this->assertContains('/blogs/linky → /services/nope (link in the text: “this”)', LinkGraph::broken($g));
        // The link map gets the graph as a list of nodes: the same count.
        $this->assertCount(1, LinkGraph::broken(['nodes' => array_values($g['nodes']), 'edges' => $g['edges']]));
    }

    public function test_the_admin_has_day_and_night_buttons(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin', 'active' => true]));
        $this->get('/admin')->assertOk()->assertSee('theme-changed', false);
    }
}
