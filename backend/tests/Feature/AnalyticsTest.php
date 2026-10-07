<?php

namespace Tests\Feature;

use App\Models\AnalyticsVisit;
use App\Models\User;
use App\Support\Analytics\Classifier;
use App\Support\Analytics\Report;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    private const UA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0 Safari/537.36';

    public function test_sources_are_classified(): void
    {
        $c = fn (string $ref, array $q = []) => array_slice(Classifier::source($ref, $q, 'gtechdigital.co.uk'), 0, 2);
        $this->assertSame(['channel' => 'Search', 'source' => 'Google'], $c('https://www.google.co.uk/'));
        $this->assertSame(['channel' => 'Search', 'source' => 'Bing'], $c('https://www.bing.com/search?q=x'));
        $this->assertSame(['channel' => 'Search', 'source' => 'Yahoo'], $c('https://uk.search.yahoo.com/'));
        $this->assertSame(['channel' => 'AI', 'source' => 'ChatGPT'], $c('', ['utm_source' => 'chatgpt.com']));
        $this->assertSame(['channel' => 'AI', 'source' => 'ChatGPT'], $c('https://chatgpt.com/'));
        $this->assertSame(['channel' => 'AI', 'source' => 'Perplexity'], $c('https://www.perplexity.ai/search/x'));
        $this->assertSame(['channel' => 'AI', 'source' => 'Claude'], $c('https://claude.ai/'));
        $this->assertSame(['channel' => 'AI', 'source' => 'Gemini'], $c('https://gemini.google.com/'));
        $this->assertSame(['channel' => 'AI', 'source' => 'Grok'], $c('https://grok.com/'));
        $this->assertSame(['channel' => 'AI', 'source' => 'Copilot'], $c('https://copilot.microsoft.com/'));
        $this->assertSame(['channel' => 'Social', 'source' => 'LinkedIn'], $c('https://lnkd.in/abc'));
        $this->assertSame(['channel' => 'Social', 'source' => 'Facebook'], $c('https://l.facebook.com/'));
        $this->assertSame(['channel' => 'Paid', 'source' => 'Google Ads'], $c('https://www.google.com/', ['gclid' => 'x']));
        $this->assertSame(['channel' => 'Paid', 'source' => 'Facebook'], $c('', ['utm_source' => 'facebook', 'utm_medium' => 'cpc']));
        $this->assertSame(['channel' => 'Email', 'source' => 'Newsletter'], $c('', ['utm_source' => 'newsletter', 'utm_medium' => 'email']));
        $this->assertSame(['channel' => 'Campaign', 'source' => 'partner-site'], $c('', ['utm_source' => 'partner-site']));
        $this->assertSame(['channel' => 'Referral', 'source' => 'someblog.co.uk'], $c('https://www.someblog.co.uk/post'));
        $this->assertSame(['channel' => 'Direct', 'source' => 'Direct'], $c(''));
        $this->assertSame(['channel' => 'Direct', 'source' => 'Direct'], $c('https://www.gtechdigital.co.uk/about'));
    }

    public function test_bots_are_named_and_never_counted_as_visits(): void
    {
        $this->assertSame(['ChatGPT (live fetch for a user)', 'ai'], Classifier::bot('Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; ChatGPT-User/1.0)'));
        $this->assertSame(['ClaudeBot (Anthropic)', 'ai'], Classifier::bot('Mozilla/5.0 (compatible; ClaudeBot/1.0)'));
        $this->assertSame(['Googlebot', 'search'], Classifier::bot('Mozilla/5.0 (compatible; Googlebot/2.1)'));
        $this->postJson('/api/t', ['sid' => str_repeat('a', 32), 'p' => '/', 'r' => '', 'q' => ''], ['User-Agent' => 'Mozilla/5.0 (compatible; GPTBot/1.2)']);
        $this->postJson('/api/t', ['sid' => str_repeat('b', 32), 'p' => '/', 'r' => '', 'q' => ''], ['User-Agent' => 'Mozilla/5.0 HeadlessChrome/130']);
        $this->assertSame(0, AnalyticsVisit::query()->count());
    }

    public function test_a_visit_its_pages_time_and_lead_are_recorded(): void
    {
        $sid = str_repeat('c', 32);
        $pv = $this->postJson('/api/t', ['sid' => $sid, 'p' => '/blogs/aeo-geo-guide', 'r' => 'https://www.perplexity.ai/search/x', 'q' => '?utm_campaign=autumn'], ['User-Agent' => self::UA])->json('pv');
        $this->postJson('/api/t', ['sid' => $sid, 'pv' => $pv, 'sec' => 75], ['User-Agent' => self::UA])->assertNoContent();
        $this->postJson('/api/t', ['sid' => $sid, 'p' => '/contact', 'r' => '', 'q' => ''], ['User-Agent' => self::UA])->assertOk();
        $this->postJson('/api/t', ['sid' => $sid, 'p' => '/admin/leads', 'r' => '', 'q' => ''], ['User-Agent' => self::UA]); // never counted

        $v = AnalyticsVisit::query()->firstOrFail();
        $this->assertSame(['AI', 'Perplexity', '/blogs/aeo-geo-guide', 'autumn', 2, 75], [$v->channel, $v->source, $v->landing_path, $v->utm_campaign, $v->pageviews, $v->seconds]);

        $this->postJson('/api/contact', ['name' => 'Amy Lee', 'business' => 'Lee Dental', 'email' => 'amy@example.com', 'phone' => '07700 900123', 'service' => 'Local SEO', 'budget' => 'Under £500', 'sid' => $sid])->assertOk();
        $this->assertNotNull($v->fresh()->lead_id);

        $r = new Report(30);
        $this->assertSame(1, $r->totals()['leads']);
        $this->assertSame('Perplexity', $r->ai()[0]['k']);
        $this->assertSame(1, (new Report(30, page: '/contact'))->totals()['visits']);
        $this->assertSame(0, (new Report(30, channel: 'Search'))->totals()['visits']);
    }

    public function test_ai_crawlers_are_recorded_on_public_pages(): void
    {
        \Illuminate\Support\Facades\Artisan::call('gtech:seed-content');
        \App\Support\Site\Repo::flush();
        \App\View\SiteComposer::flush();
        $this->get('/services/local-seo', ['User-Agent' => 'Mozilla/5.0 (compatible; PerplexityBot/1.0)'])->assertOk();
        $this->assertSame('PerplexityBot', DB::table('analytics_bot_hits')->value('bot'));
    }

    public function test_page_views_do_not_use_up_the_form_allowance(): void
    {
        for ($i = 0; $i < 70; $i++) $this->postJson('/api/t', ['sid' => str_repeat('d', 32), 'p' => '/', 'r' => '', 'q' => ''], ['User-Agent' => self::UA]);
        $this->postJson('/api/subscribe', ['email' => 'x@example.com'])->assertOk();
    }

    public function test_editors_cannot_open_analytics(): void
    {
        $editor = User::query()->create(['name' => 'E', 'email' => 'e@example.com', 'password' => bcrypt('p-'.uniqid()), 'role' => 'editor', 'active' => true]);
        $this->actingAs($editor)->get('/admin/analytics')->assertForbidden();
    }

    public function test_admins_see_analytics(): void
    {
        $admin = User::query()->create(['name' => 'A', 'email' => 'a@example.com', 'password' => bcrypt('p-'.uniqid()), 'role' => 'admin', 'active' => true]);
        $this->actingAs($admin)->get('/admin/analytics')->assertOk()->assertSee('Visits by channel');
    }
}
