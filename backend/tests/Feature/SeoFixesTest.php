<?php

namespace Tests\Feature;

use App\Models\CaseStudy;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

/** The fixes from the SEO and crawl report. */
class SeoFixesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('gtech:seed-content');
        \App\Support\Site\Repo::flush();
    }

    public function test_no_borrowed_logos_or_placeholder_case_studies(): void
    {
        $this->assertSame(0, Client::query()->where('logo', 'like', '%growmemarketing%')->count());
        $this->assertSame(0, CaseStudy::query()->where('slug', 'like', 'demo-brand-%')->count());
        // With no client logos the logo strips are left out instead of showing empty.
        Client::query()->update(['visible' => false]);
        $this->get('/')->assertOk()->assertDontSee('class="brands"', false);
        $this->get('/services/local-seo')->assertOk()->assertDontSee('sp-logo-row', false);
    }

    public function test_robots_lets_google_fetch_uploaded_images(): void
    {
        config(['gtech.blade_live' => true]);
        $txt = $this->get('/robots.txt')->assertOk()->getContent();
        $this->assertStringContainsString("Allow: /api/media/\nDisallow: /api/", $txt);
    }

    public function test_blog_list_is_paged_with_self_canonical_pages(): void
    {
        foreach (range(1, 20) as $i) \App\Models\Post::create(['title' => "Paged $i", 'slug' => "paged-$i", 'status' => 'published', 'body' => '<p>x</p>', 'date' => now()->subDays(30 + $i)]);
        \App\Support\Site\Repo::flush();
        $first = $this->get('/blogs')->assertOk()->getContent();
        $this->assertStringContainsString('href="/blogs?page=2" rel="next"', $first);
        $two = $this->get('/blogs?page=2')->assertOk()->getContent();
        $this->assertStringContainsString('<link rel="canonical" href="https://www.gtechdigital.co.uk/blogs?page=2">', $two);
        $this->assertStringContainsString('rel="prev"', $two);
        $this->assertStringContainsString('<title>Blog – Page 2 | GTech Digital</title>', $two);
        $this->get('/blogs?page=99')->assertNotFound();
        $this->assertStringContainsString('noindex', $this->get('/blogs?q=paged')->getContent());
    }

    public function test_menu_and_footer_titles_are_not_headings_and_the_outline_starts_with_h1(): void
    {
        foreach (['/', '/contact', '/case-studies', '/industries', '/blogs', '/services/local-seo'] as $u) {
            $html = $this->get($u)->assertOk()->getContent();
            $html = preg_replace('#<template\b.*?</template>#s', '', $html);
            preg_match_all('#<h([1-6])\b#', $html, $m);
            $this->assertSame('1', $m[1][0] ?? null, "$u: first heading");
            $prev = 0;
            foreach ($m[1] as $l) { $this->assertLessThanOrEqual($prev + 1, (int) $l, "$u: heading level jump"); $prev = (int) $l; }
        }
    }

    public function test_every_local_image_gets_its_size(): void
    {
        $html = \App\Support\Site\ImageDims::add('<img src="/logo.png" alt="a"><img src="/og-default.jpg"><img src="https://other.example/x.png"><img src="/logo.png" width="5" height="5">');
        [$w, $h] = getimagesize(public_path('logo.png'));
        $this->assertStringContainsString("<img width=\"$w\" height=\"$h\" src=\"/logo.png\"", $html);
        $this->assertStringContainsString('<img width="1200" height="630" src="/og-default.jpg"', $html);
        $this->assertStringContainsString('<img src="https://other.example/x.png">', $html);
        $this->assertStringContainsString('width="5" height="5"', $html);
        $page = $this->get('/')->assertOk()->getContent();
        preg_match_all('/<img\b[^>]*>/', $page, $m);
        foreach ($m[0] as $img) if (! str_contains($img, 'src="http')) $this->assertMatchesRegularExpression('/\swidth="\d+"/', $img);
    }

    public function test_titles_fit_and_every_page_has_a_share_image(): void
    {
        foreach (['/', '/about', '/contact', '/industries', '/blogs', '/terms', '/cookie-policy', '/case-studies/chefonline', '/blogs/aeo-geo-guide'] as $u) {
            $html = $this->get($u)->assertOk()->getContent();
            preg_match('#<title>(.*?)</title>#', $html, $t);
            $this->assertLessThanOrEqual(60, mb_strlen(html_entity_decode($t[1])), "$u title: {$t[1]}");
            $this->assertMatchesRegularExpression('#<meta property="og:image" content="https://[^"]+">#', $html, "$u og:image");
            preg_match('#<meta name="description" content="([^"]*)"#', $html, $d);
            $this->assertLessThanOrEqual(160, mb_strlen(html_entity_decode($d[1])), "$u description");
        }
        $this->assertStringContainsString('og-default.jpg', $this->get('/about')->getContent());
        $this->assertGreaterThanOrEqual(120, mb_strlen(\App\Support\Site\Seo::fillDescription('Short.', 'How GTech Digital helped a client with SEO and Google Ads: +212% organic traffic and £1.4M revenue in twelve months.')));
    }

    public function test_named_authors_get_a_profile_page_and_person_schema(): void
    {
        $a = \App\Models\Author::create(['name' => 'Sam Writer', 'job_title' => 'Head of SEO', 'bio' => 'Ten years of SEO for UK firms.', 'linkedin' => 'https://www.linkedin.com/in/sam']);
        \App\Models\Post::query()->where('slug', 'aeo-geo-guide')->update(['author' => 'Sam Writer']);
        \App\Support\Site\Repo::flush();
        $post = $this->get('/blogs/aeo-geo-guide')->assertOk()->getContent();
        $this->assertStringContainsString('<a href="/blogs/author/sam-writer" rel="author">Sam Writer</a>', $post);
        $this->assertMatchesRegularExpression('#<time datetime="\d{4}-\d\d-\d\d">#', $post);
        $this->assertStringContainsString('"author":{"@id":"https://www.gtechdigital.co.uk/blogs/author/sam-writer#person"}', $post);
        $this->assertStringContainsString('"@type":"Person"', $post);
        $page = $this->get('/blogs/author/sam-writer')->assertOk()->getContent();
        $this->assertStringContainsString('<h1>Sam Writer</h1>', $page);
        $this->assertStringContainsString('"@type":"ProfilePage"', $page);
        $this->assertStringContainsString('"sameAs":["https://www.linkedin.com/in/sam"]', $page);
        $this->assertStringContainsString('/blogs/author/sam-writer', $this->get('/sitemap.xml')->getContent());
        $this->get('/blogs/author/nobody')->assertNotFound();
        // Renaming keeps the posts.
        $a->update(['name' => 'Sam Writer-Jones']);
        $this->assertSame('Sam Writer-Jones', \App\Models\Post::query()->where('slug', 'aeo-geo-guide')->value('author'));
    }
}
