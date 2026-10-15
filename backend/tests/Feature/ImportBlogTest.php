<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImportBlogTest extends TestCase
{
    use RefreshDatabase;

    private const PNG = "\x89PNG\r\n\x1a\n0000";
    private const BODY = '<h2>Why it matters</h2><p>Read our <a href="https://www.gtechdigital.co.uk/blog/other-post/">other post</a> and <a href="https://example.org/x">this</a>.</p><figure><img src="https://www.gtechdigital.co.uk/wp-content/uploads/2025/01/chart.png" srcset="https://www.gtechdigital.co.uk/wp-content/uploads/2025/01/chart-300x200.png 300w" alt="Traffic chart"></figure>';

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::fake('local');
    }

    public function test_wordpress_posts_are_copied_unchanged_with_their_images(): void
    {
        Http::fake([
            'www.gtechdigital.co.uk/wp-json/wp/v2/posts*' => Http::response([[
                'id' => 42, 'slug' => 'seo-guide', 'link' => 'https://www.gtechdigital.co.uk/blog/seo-guide/', 'date' => '2025-03-01T10:00:00',
                'title' => ['rendered' => 'SEO Guide &amp; Tips'], 'content' => ['rendered' => self::BODY], 'excerpt' => ['rendered' => '<p>Short summary.</p>'],
                'yoast_head_json' => ['title' => 'SEO Guide | GTech', 'description' => 'Meta description here.', 'canonical' => 'https://www.gtechdigital.co.uk/blog/seo-guide/'],
                '_embedded' => ['author' => [['name' => 'Jane Writer']], 'wp:featuredmedia' => [['source_url' => 'https://www.gtechdigital.co.uk/wp-content/uploads/2025/01/cover.png', 'alt_text' => 'Cover alt']],
                    'wp:term' => [[['taxonomy' => 'category', 'name' => 'SEO']], [['taxonomy' => 'post_tag', 'name' => 'Local']]]],
            ]]),
            'www.gtechdigital.co.uk/wp-content/*' => Http::response(self::PNG, 200, ['Content-Type' => 'image/png']),
        ]);

        $this->artisan('gtech:import-blog')->assertSuccessful();
        $this->artisan('gtech:import-blog')->assertSuccessful(); // again: updates, never duplicates

        $p = Post::query()->sole();
        $this->assertSame(['seo-guide', 'SEO Guide & Tips', 'Jane Writer', ['SEO'], ['Local'], 'SEO Guide | GTech', 'Meta description here.', '2025-03-01'],
            [$p->slug, $p->title, $p->author, $p->categories, $p->tags, $p->meta_title, $p->meta_description, $p->date->format('Y-m-d')]);
        // Text, headings and links unchanged; only the image address moved into the media library.
        $this->assertStringContainsString('<h2>Why it matters</h2><p>Read our <a href="https://www.gtechdigital.co.uk/blog/other-post/">other post</a>', $p->body);
        $this->assertMatchesRegularExpression('#<img src="/api/media/\d+" alt="Traffic chart">#', $p->body);
        $this->assertMatchesRegularExpression('#^/api/media/\d+$#', $p->image);
        $this->assertSame('Cover alt', $p->image_alt);
        $report = json_decode(Storage::disk('local')->get('blog-import-report.json'), true);
        $this->assertTrue($report[0]['ok'], json_encode($report[0]['problems']));
        $this->get('/blog/seo-guide')->assertRedirect('/blogs/seo-guide');
        $this->get('/blogs/seo-guide')->assertOk()->assertSee('Why it matters');
    }

    public function test_other_sites_are_crawled_from_the_blog_listing(): void
    {
        Http::fake([
            'www.gtechdigital.co.uk/wp-json/*' => Http::response('Not found', 404),
            'www.gtechdigital.co.uk/blog/' => Http::response('<a href="/blog/seo-guide/">SEO guide</a><a href="/blog/page/2/">Next</a><a href="/services/">x</a>'),
            'www.gtechdigital.co.uk/blog/page/2/' => Http::response('<a href="/blog/second-post/">Second</a>'),
            'www.gtechdigital.co.uk/blog/seo-guide/' => Http::response('<html><head><title>SEO Guide | GTech</title><meta name="description" content="Meta here."><meta property="og:image" content="/img/cover.png"><meta property="article:published_time" content="2025-02-02T09:00:00Z"></head><body><article><h1>SEO Guide</h1>'.self::BODY.'</article></body></html>'),
            'www.gtechdigital.co.uk/blog/second-post/' => Http::response('<html><body><article><h1>Second Post</h1><p>Hello there.</p></article></body></html>'),
            'www.gtechdigital.co.uk/*' => Http::response(self::PNG, 200, ['Content-Type' => 'image/png']),
        ]);

        $this->artisan('gtech:import-blog')->assertSuccessful();
        $this->assertSame(['second-post', 'seo-guide'], Post::query()->orderBy('slug')->pluck('slug')->all());
        $p = Post::query()->where('slug', 'seo-guide')->first();
        $this->assertSame(['SEO Guide', 'Meta here.', '2025-02-02'], [$p->title, $p->meta_description, $p->date->format('Y-m-d')]);
        $this->assertStringNotContainsString('<h1>', $p->body);
        $this->assertStringContainsString('href="https://www.gtechdigital.co.uk/blog/other-post/"', $p->body);
    }
}
