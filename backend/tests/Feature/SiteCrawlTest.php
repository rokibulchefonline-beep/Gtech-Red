<?php

namespace Tests\Feature;

use App\Filament\Admin\Pages\SeoAudit;
use App\Models\Post;
use App\Models\User;
use App\Support\Seo\Crawler;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class SiteCrawlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Livewire\Features\SupportAutoInjectedAssets\SupportAutoInjectedAssets::$hasRenderedAComponentThisRequest = false;
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Artisan::call('gtech:seed-content');
        \App\Support\Site\Repo::flush();
        Http::fake([
            'dead.example.com/*' => Http::response('', 404),
            '*' => Http::response('ok', 200),
        ]);
        Post::create(['title' => 'Crawl me', 'slug' => 'crawl-me', 'status' => 'published',
            'body' => '<p>See <a href="/services/no-such-service">our missing service</a>, <a href="https://dead.example.com/page">a dead site</a> and <a href="https://www.google.com/">Google</a>.</p><p><img src="/pages/missing.webp" alt="Missing"></p>']);
    }

    public function test_the_crawler_finds_error_pages_broken_links_and_counts_links(): void
    {
        $run = (new Crawler())->run('test');
        $this->assertSame('done', $run->status, (string) $run->message);
        $this->assertGreaterThan(50, $run->pages);
        $broken = DB::table('crawl_links')->where('run_id', $run->id)->where('ok', false)->where('from_path', '/blogs/crawl-me')->pluck('status', 'url');
        $this->assertEquals(['/services/no-such-service' => 404, 'https://dead.example.com/page' => 404, '/pages/missing.webp' => 404], $broken->all());
        $this->assertSame(404, (int) DB::table('crawl_pages')->where('run_id', $run->id)->where('path', '/services/no-such-service')->value('status'));
        $post = DB::table('crawl_pages')->where('run_id', $run->id)->where('path', '/blogs/crawl-me')->first();
        $this->assertSame(200, (int) $post->status);
        $this->assertGreaterThan(0, $post->inbound);
        $this->assertGreaterThanOrEqual(2, $post->out_external);
        $this->assertSame(3, (int) $post->broken_links);
        $this->assertGreaterThanOrEqual(1, $run->errors);
    }

    public function test_the_crawl_tabs_show_the_results_and_the_admin_session_survives(): void
    {
        $u = User::factory()->create(['role' => 'super_admin', 'active' => true]);
        $this->actingAs($u);
        Livewire::test(SeoAudit::class)->assertSee('The site has not been crawled yet')
            ->callAction('crawl')->assertHasNoActionErrors()->assertSee('Crawling the website');
        // The crawl runs after the response (here when the test request ends, or straight away).
        if ($id = DB::table('crawl_runs')->where('status', 'running')->value('id')) (new Crawler())->run('manual', (int) $id);
        $this->assertSame(1, DB::table('crawl_runs')->where('status', 'done')->count());
        
        Livewire::test(SeoAudit::class)
            ->set('tab', 'health')->assertSee('Error pages')->assertSee('/services/no-such-service')
            ->set('tab', 'broken')->assertSee('https://dead.example.com/page')->assertSee('/pages/missing.webp')
            ->set('show', 'images')->assertDontSee('https://dead.example.com/page')
            ->set('show', 'all')->set('tab', 'linking')->set('q', 'crawl-me')->call('toggle', '/blogs/crawl-me')->assertSee('Linked from')
            ->set('q', '')->set('tab', 'external')->assertSee('dead.example.com');
        $this->assertSame($u->id, auth()->id());
        $this->get('/admin/seo-audit')->assertOk()->assertSee('Site health');
    }

    public function test_new_broken_links_are_emailed_after_the_nightly_crawl(): void
    {
        \App\Models\Setting::put('smtp', ['notifyTo' => 'team@example.com'] + \App\Models\Setting::group('smtp'));
        Artisan::call('gtech:crawl', ['--alert' => true]);
        $this->assertDatabaseHas('emails', ['to' => 'team@example.com', 'subject' => '3 new broken links on the website']);
        Artisan::call('gtech:crawl', ['--alert' => true]);
        $this->assertSame(1, DB::table('emails')->where('subject', 'like', '%broken link%')->count());
    }
}
