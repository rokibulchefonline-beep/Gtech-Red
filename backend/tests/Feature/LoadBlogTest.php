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
}
