<?php

namespace Tests\Feature;

use App\Filament\Admin\Resources\PageResource\Pages\EditPage;
use App\Filament\Admin\Resources\SiteIconResource\Pages\CreateSiteIcon;
use App\Models\Page;
use App\Models\SiteIcon;
use App\Models\User;
use App\Support\Site\Icons;
use App\Support\Site\SvgIcon;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class IconLibraryTest extends TestCase
{
    use RefreshDatabase;

    private const SVG = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" onload="alert(1)"><title>x</title><defs><linearGradient id="g"><stop offset="0" stop-color="#f00"/></linearGradient></defs><script>alert(1)</script><path fill="#ff0000" d="M2 2h20v20H2z"/><circle cx="12" cy="12" r="4" fill="url(#g)"/></svg>';

    protected function setUp(): void
    {
        parent::setUp();
        \Livewire\Features\SupportAutoInjectedAssets\SupportAutoInjectedAssets::$hasRenderedAComponentThisRequest = false;
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Artisan::call('gtech:seed-content');
        \App\Support\Site\Repo::flush();
        Storage::fake('tmp-for-tests');
        $this->actingAs(User::factory()->create(['role' => 'super_admin', 'active' => true]));
    }

    public function test_the_full_libraries_are_searchable(): void
    {
        $this->assertGreaterThan(2000, Icons::count('lucide'));
        $this->assertGreaterThan(3000, Icons::count('simple-icons'));
        $this->assertContains('simple-icons:googleads', Icons::search('google ads'));
        $this->assertSame('lucide:rocket', Icons::search('rocket')[0]);
        $this->assertStringContainsString('<svg', Icons::svg('lucide:brain-circuit'));
    }

    public function test_uploaded_svgs_are_cleaned_and_recoloured(): void
    {
        $r = SvgIcon::parse(self::SVG, 'logo', true);
        $this->assertStringNotContainsString('script', $r['body']);
        $this->assertStringNotContainsString('onload', $r['body']);
        $this->assertStringNotContainsString('<title', $r['body']);
        $this->assertStringContainsString('fill="currentColor"', $r['body']);
        $this->assertStringContainsString('id="i-logo-g"', $r['body']);
        $this->assertStringContainsString('url(#i-logo-g)', $r['body']);
        $this->assertSame('0 0 24 24', $r['viewbox']);
        $colour = SvgIcon::parse(self::SVG, 'logo', false);
        $this->assertStringContainsString('#ff0000', $colour['body']);

        foreach (['<svg xmlns="http://www.w3.org/2000/svg"><path d="M0 0h1"/></svg>' => 'viewBox', '<html></html>' => 'not an SVG',
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 2 2"><path d="'.str_repeat('M1 1', 6000).'"/></svg>' => 'larger than'] as $bad => $msg) {
            try { SvgIcon::parse($bad, 'x', true); $this->fail("Accepted: $msg"); } catch (\RuntimeException $e) { $this->assertStringContainsString($msg, $e->getMessage()); }
        }
    }

    public function test_an_uploaded_icon_can_be_used_on_a_page(): void
    {
        Livewire::test(CreateSiteIcon::class)
            ->fillForm(['name' => 'Partner Badge', 'slug' => 'partner-badge', 'mono' => true, 'file' => [UploadedFile::fake()->createWithContent('badge.svg', self::SVG)]])
            ->call('create')->assertHasNoFormErrors();
        $icon = SiteIcon::query()->where('slug', 'partner-badge')->firstOrFail();
        $this->assertSame('Partner Badge', $icon->name);
        $this->assertSame('custom:partner-badge', Icons::search('partner')[0]);
        $this->assertStringContainsString('viewBox="0 0 24 24"', Icons::svg('custom:partner-badge'));

        // A designed page's card icon is changed from the page editor.
        $c = Livewire::test(EditPage::class, ['record' => 'page~services-hub']);
        $secs = $c->get('data.secs');
        foreach ($secs as $k => $s) foreach ($s['items'] ?? [] as $j => $it) if (in_array('icon', $it['_k'], true)) { $c->set("data.secs.$k.items.$j.icon", 'custom:partner-badge'); break 2; }
        $c->call('save')->assertHasNoErrors();
        Page::find('page~services-hub')->publish();
        \App\Support\Site\PageCache::flush();
        $this->get('/services')->assertOk()->assertSee('i-partner-badge-g', false);
        $this->get('/admin/site-icons')->assertOk()->assertSee('Partner Badge');
    }

    public function test_an_unknown_icon_is_refused(): void
    {
        Livewire::test(EditPage::class, ['record' => 'service~local-seo'])
            ->set('data.sections', collect(Livewire::test(EditPage::class, ['record' => 'service~local-seo'])->get('data.sections'))
                ->map(function ($b) { if ($b['type'] === 'cards') $b['data']['cards'] = array_map(fn ($c) => ['icon' => 'lucide:not-a-real-icon'] + $c, $b['data']['cards']); return $b; })->all())
            ->call('save')->assertHasErrors();
    }
}
