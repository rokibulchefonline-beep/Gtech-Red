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
}
