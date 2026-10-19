<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoadBlogTest extends TestCase
{
    use RefreshDatabase;

    public function test_loads_the_copied_posts_once_with_their_images(): void
    {
        $this->artisan('gtech:load-blog')->assertSuccessful();
        $this->artisan('gtech:load-blog')->assertSuccessful();

        $this->assertSame(16, Post::count());
        $post = Post::where('slug', 'local-seo-checklist')->firstOrFail();
        $this->assertSame('html', $post->format);
        $this->assertFileExists(public_path($post->image));
        $this->get('/blog/local-seo-checklist')->assertOk()->assertSee('Google Maps');
    }

    public function test_real_posts_replace_untouched_demo_posts_but_never_edited_ones(): void
    {
        \Illuminate\Support\Facades\Artisan::call('gtech:seed-content');
        $real = collect(json_decode(file_get_contents(database_path('data/blog-posts.json')), true))->keyBy('slug');
        Post::where('slug', 'aeo-geo-guide')->update(['title' => 'My edited demo post']);

        $this->artisan('gtech:load-blog --remove-demo')->assertSuccessful();

        // The demo post with the same address as a real post is replaced by the real one.
        $this->assertSame($real['local-seo-checklist']['title'], Post::where('slug', 'local-seo-checklist')->value('title'));
        // Untouched demo posts are removed; the edited one is kept.
        $this->assertSame(['My edited demo post'], Post::whereNotIn('slug', $real->keys())->pluck('title')->all());
        $this->assertSame(17, Post::count());
    }
}
