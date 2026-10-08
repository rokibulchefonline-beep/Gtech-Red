<?php

namespace Tests\Feature;

use App\Filament\Admin\Resources\PageResource\Pages\EditPage;
use App\Models\Page;
use App\Models\User;
use App\Support\Site\PageCache;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Livewire\Livewire;
use Tests\TestCase;

class PageLayoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Livewire\Features\SupportAutoInjectedAssets\SupportAutoInjectedAssets::$hasRenderedAComponentThisRequest = false;
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Artisan::call('gtech:seed-content');
        $this->flush();
        $this->actingAs(User::factory()->create(['role' => 'editor', 'active' => true]));
    }

    private function flush(): void
    {
        PageCache::flush();
        \App\Support\Site\Repo::flush();
        \App\View\SiteComposer::flush();
    }

    private function publish(string $key, callable $edit): void
    {
        $c = Livewire::test(EditPage::class, ['record' => $key]);
        $layout = $c->get('data.layout');
        $c->set('data.layout', $edit($layout))->call('save')->assertHasNoErrors();
        Page::find($key)->publish();
        $this->flush();
    }

    public function test_designed_pages_list_their_sections_in_order(): void
    {
        $layout = Livewire::test(EditPage::class, ['record' => 'page~home'])->get('data.layout');
        $this->assertSame(['partners', 'who', 'services', 'how', 'brands', 'cases', 'results', 'testimonials', 'faq', 'inquiry'], array_values(array_map(fn ($b) => $b['data']['key'], $layout)));
    }

    public function test_sections_on_a_designed_page_can_be_moved_hidden_and_inserted(): void
    {
        $this->publish('page~home', function (array $layout) {
            $items = array_values($layout);
            [$items[1], $items[2]] = [$items[2], $items[1]];              // services before who
            $items = array_values(array_filter($items, fn ($b) => $b['data']['key'] !== 'results')); // hide results
            array_splice($items, 1, 0, [['type' => 'cta', 'data' => ['heading' => 'Inserted [[Banner]]', 'text' => 'Hello', 'button' => 'Go', 'link' => '/contact', 'tone' => 'dark']]]);
            return array_combine(array_map(fn ($i) => "k$i", array_keys($items)), $items);
        });
        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('Inserted <span class="hl">Banner</span>', $html);
        $this->assertStringNotContainsString('class="results', $html);
        $this->assertTrue(strpos($html, 'Inserted') < strpos($html, 'class="ourservices"'));
        $this->assertTrue(strpos($html, 'class="ourservices"') < strpos($html, 'class="who"'));
    }

    public function test_the_default_order_is_not_stored(): void
    {
        $this->publish('page~contact', fn ($layout) => $layout);
        $this->assertArrayNotHasKey('layout', (array) Page::find('page~contact')->data);
    }

    public function test_about_sections_can_be_reordered(): void
    {
        $this->publish('page~about', fn ($layout) => array_reverse($layout, true));
        $html = $this->get('/about')->assertOk()->getContent();
        $this->assertTrue(strpos($html, 'id="inquiry"') < strpos($html, 'class="sp-after-hero"'));
    }

    public function test_the_industries_page_has_editable_sections(): void
    {
        $c = Livewire::test(EditPage::class, ['record' => 'page~industries-hub']);
        $secs = $c->get('data.secs');
        $this->assertCount(2, $secs);
        $k = array_key_first($secs);
        $c->set("data.secs.$k.heading", 'How we partner with [[your sector]]')->call('save')->assertHasNoErrors();
        Page::find('page~industries-hub')->publish();
        $this->flush();
        $this->get('/industries')->assertOk()->assertSee('How we partner with your sector')->assertSee('Industries <span class="hl">We Serve</span>', false);
    }

    public function test_widget_previews_render_for_staff_only(): void
    {
        foreach (array_keys(\App\Filament\Support\PageBlocks::LABELS) as $type) {
            $this->get("/preview/widget/$type")->assertOk()->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        }
        $this->get('/preview/widget/nope')->assertNotFound();
        $this->get('/admin/page-widgets')->assertOk()->assertSee('/preview/widget/cta', false);
        auth()->logout();
        $this->get('/preview/widget/cta')->assertForbidden();
    }

    public function test_custom_html_widget_keeps_html_and_css_but_drops_scripts(): void
    {
        $s = \App\Filament\Support\PageBlocks::fromBuilder([['type' => 'html', 'data' => [
            'heading' => 'Offer', 'html' => '<div class="offer" onclick="alert(1)"><h2>Deal</h2><a href="javascript:alert(1)">x</a></div><script>alert(1)</script>',
            'css' => '.offer { color: red } </style><script>alert(1)</script>',
        ]]])[0];
        $this->assertSame('<div class="offer"><h2>Deal</h2><a href="#">x</a></div>', $s['html']);
        $this->assertStringNotContainsString('<', $s['css']);
        $this->publish('page~home', fn ($layout) => ['new' => ['type' => 'html', 'data' => ['heading' => 'Offer', 'html' => '<div class="offer">Spring deal</div>', 'css' => '.offer { color: red }']]] + $layout);
        $this->get('/')->assertSee('<div class="offer">Spring deal</div>', false)->assertSee('{ .offer { color: red } }', false);
    }
}
