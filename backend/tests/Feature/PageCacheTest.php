<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Post;
use App\Models\Setting;
use App\Support\Site\PageCache;
use App\Support\Site\Repo;
use App\View\SiteComposer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class PageCacheTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Livewire remembers rendering an admin page in a static flag; a real request starts fresh.
        \Livewire\Features\SupportAutoInjectedAssets\SupportAutoInjectedAssets::$hasRenderedAComponentThisRequest = false;
        Artisan::call('gtech:seed-content');
        $this->fresh();
    }

    /** Each test request runs in the same PHP process; forget the per-request memos as a new request would. */
    private function fresh(): void
    {
        Repo::flush();
        SiteComposer::flush();
    }

    public function test_pages_are_cached_and_refresh_when_content_is_saved(): void
    {
        $this->get('/services/local-seo')->assertHeader('X-Page-Cache', 'MISS');
        $this->get('/services/local-seo')->assertHeader('X-Page-Cache', 'HIT')->assertSee('Local SEO That Puts You on the', false);

        $p = Page::query()->find('service~local-seo');
        $p->hero = ['h1' => 'A Brand New [[Heading]]'] + $p->hero;
        $p->save();
        $this->fresh();
        $this->get('/services/local-seo')->assertHeader('X-Page-Cache', 'MISS')->assertSee('A Brand New <span class="hl">Heading</span>', false);

        // Settings shown in the footer count as content too.
        $this->get('/about')->assertHeader('X-Page-Cache', 'MISS');
        Setting::put('contact', ['email' => 'new@example.com'] + Setting::all_()['contact']);
        $this->fresh();
        $this->get('/about')->assertHeader('X-Page-Cache', 'MISS')->assertSee('new@example.com');
    }

    public function test_only_plain_addresses_and_topic_filters_are_cached(): void
    {
        $this->get('/blogs?q=seo')->assertOk()->assertHeaderMissing('X-Page-Cache');
        $this->get('/blogs?category=seo')->assertHeader('X-Page-Cache', 'MISS');
        $this->get('/blogs?category=seo')->assertHeader('X-Page-Cache', 'HIT');
        $this->get('/services/not-a-page')->assertNotFound()->assertHeaderMissing('X-Page-Cache');
    }

    public function test_public_pages_set_no_cookies_and_answer_304_when_unchanged(): void
    {
        $res = $this->get('/');
        $this->assertSame([], $res->headers->getCookies());
        $this->assertStringContainsString('must-revalidate', $res->headers->get('Cache-Control'));
        $this->get('/', ['If-None-Match' => $res->headers->get('ETag')])->assertStatus(304)->assertContent('');
    }

    public function test_cache_never_outlives_the_next_scheduled_post(): void
    {
        $this->assertSame((int) config('gtech.page_cache.ttl'), PageCache::ttl());
        Post::query()->create(['title' => 'Later', 'slug' => 'later', 'status' => 'scheduled', 'date' => now()->addMinutes(10)]);
        $this->assertLessThanOrEqual(600, PageCache::ttl());
    }
}
