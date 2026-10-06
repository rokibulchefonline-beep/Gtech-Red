<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Support\Site\Blog;
use App\Support\Site\Repo;
use App\View\SiteComposer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class BladePagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('gtech:seed-content');
        Repo::flush();
        SiteComposer::flush();
    }

    public function test_every_page_type_renders(): void
    {
        $pages = [
            '/' => 'Digital Marketing',
            '/services' => 'Explore Services',
            '/services/local-seo' => 'class="sp-faq-aside"',
            '/services/social-media-marketing' => 'Services Related to',
            '/industries' => 'Explore Industries',
            '/industries/healthcare' => 'Other industries we serve:',
            '/about' => 'id="case-studies"',
            '/contact' => 'Get in touch',
            '/case-studies' => 'Digital Marketing Case Studies of',
            '/blogs' => 'Popular Posts',
            '/terms' => 'Last updated:',
            '/privacy-policy' => 'Table of Contents',
            '/cookie-policy' => 'Table of Contents',
        ];
        foreach ($pages as $url => $text) {
            $res = $this->get($url)->assertOk()->assertSee($text, false);
            $this->assertSame('noindex, nofollow', $res->headers->get('X-Robots-Tag'), $url);
            $this->assertStringNotContainsString(">\n<", $res->getContent(), "$url is not minified");
        }
        $slug = Post::query()->value('slug');
        $this->get("/blogs/$slug")->assertOk()->assertSee('Written by');
    }

    public function test_unknown_pages_use_the_site_404(): void
    {
        $this->get('/services/not-a-service')->assertNotFound()->assertSee('Page not found');
        $this->get('/industries/nope')->assertNotFound();
        $this->get('/case-studies/nope')->assertNotFound();
        $this->get('/blogs/nope')->assertNotFound();
    }

    public function test_blog_search_and_categories_run_on_the_server(): void
    {
        $this->get('/blogs?q=zzzz-no-match')->assertOk()->assertSee('No articles found.')->assertSee('0 articles for “zzzz-no-match”', false);
        $this->get('/blogs?category=seo')->assertOk()->assertSee('in SEO', false);
    }

    public function test_blog_helpers_match_the_website(): void
    {
        $p = new Post(['date' => '2026-09-24 00:00:00']);
        $this->assertSame('24 Sept 2026', Blog::date($p));
        $this->assertSame(1, Blog::readTime('one two'));
        $this->assertSame([['type' => 'h2', 'text' => 'Intro', 'id' => 'intro'], ['type' => 'ul', 'items' => ['a', 'b']], ['type' => 'p', 'text' => 'x y']], Blog::parse("## Intro\n- a\n- b\n\nx\ny"));
        $this->assertSame('<strong>b</strong> and <a href="https://x.com" target="_blank" rel="noopener noreferrer">x</a> &lt;i&gt;', Blog::rich('**b** and [x](https://x.com) <i>'));
    }
}
