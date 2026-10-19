<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Post;
use App\Support\Site\PageCache;
use App\Support\Site\Repo;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Loads the posts copied from the old blog (https://www.gtechdigital.co.uk/blog/) into the database. The posts and
 * their images live in the repository (database/data/blog-posts.json and public/posts/blog/), so nothing is fetched
 * from the old server. Posts are matched by slug, so the addresses stay the same and running it again never duplicates.
 * Posts that already exist are left alone (they may have been edited in the admin) unless you pass --force.
 */
class LoadBlog extends Command
{
    protected $signature = 'gtech:load-blog {--force : Overwrite posts that already exist with the copied version}';

    protected $description = 'Load the posts copied from the old blog, with their images';

    public function handle(): int
    {
        $posts = json_decode((string) file_get_contents(database_path('data/blog-posts.json')), true) ?: [];
        $added = $updated = $skipped = 0;

        foreach ($posts as $p) {
            foreach ($p['categories'] as $name) Category::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name]);

            $post = Post::where('slug', $p['slug'])->first();
            if ($post && ! $this->option('force')) { $skipped++; continue; }

            $data = [
                'title' => $p['title'], 'slug' => $p['slug'], 'excerpt' => $p['excerpt'], 'body' => $p['body'], 'format' => 'html',
                'category' => $p['category'], 'categories' => $p['categories'], 'tags' => [], 'image' => $p['image'], 'image_alt' => $p['imageAlt'],
                'author' => $p['author'], 'status' => 'published', 'date' => $p['date'],
                'meta_title' => $p['metaTitle'], 'meta_description' => $p['metaDescription'],
            ];
            $post ? $post->update($data) : Post::create($data);
            $post ? $updated++ : $added++;
        }

        PageCache::flush();
        Repo::flush();
        $this->info("Blog: $added added, $updated updated, $skipped already there (use --force to overwrite).");

        return self::SUCCESS;
    }
}
