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
 * Posts that already exist are left alone (they may have been edited in the admin) unless you pass --force. An
 * untouched demo post with the same address as a real one (e.g. local-seo-checklist) is replaced, and the other
 * untouched demo posts are removed.
 */
class LoadBlog extends Command
{
    protected $signature = 'gtech:load-blog {--force : Overwrite posts that already exist with the copied version}
        {--remove-demo : Kept for older instructions; demo posts are always removed now}';

    protected $description = 'Load the posts copied from the old blog, with their images';

    /** Addresses of the old website linked from the posts => their page on the new site. */
    public const OLD_PAGES = [
        '/digital-marketing' => '/services/digital-marketing',
        '/seo-service' => '/services/search-engine-optimization',
        '/service-social-media' => '/services/social-media-marketing',
        '/web-development' => '/services/web-design-development',
        '/get-offer' => '/free-audit',
    ];

    public function handle(): int
    {
        $posts = json_decode((string) file_get_contents(database_path('data/blog-posts.json')), true) ?: [];
        $added = $updated = $skipped = 0;
        // The demo posts the site was installed with, by slug => title (to recognise ones never edited).
        $demo = collect((array) \App\Support\Content::get('demo.posts'))->mapWithKeys(fn ($d) => [$d['slug'] => $d['title']])->all();
        $isDemo = fn (?Post $p) => $p && isset($demo[$p->slug]) && $p->title === $demo[$p->slug];

        foreach ($posts as $p) {
            foreach ($p['categories'] as $name) Category::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name]);

            $post = Post::where('slug', $p['slug'])->first();
            if ($post && ! $this->option('force') && ! $isDemo($post)) { $skipped++; continue; }

            $data = [
                'title' => $p['title'], 'slug' => $p['slug'], 'excerpt' => $p['excerpt'], 'body' => $p['body'], 'format' => 'html',
                'category' => $p['category'], 'categories' => $p['categories'], 'tags' => [], 'image' => $p['image'], 'image_alt' => $p['imageAlt'],
                'author' => $p['author'], 'status' => 'published', 'date' => $p['date'],
                'meta_title' => $p['metaTitle'], 'meta_description' => $p['metaDescription'],
            ];
            $post ? $post->update($data) : Post::create($data);
            $post ? $updated++ : $added++;
        }

        $removed = 0;
        // The demo posts the site was once installed with are removed (only ones never edited).
        {
            $real = array_column($posts, 'slug');
            foreach (Post::query()->whereIn('slug', array_keys($demo))->whereNotIn('slug', $real)->get() as $d) {
                if ($isDemo($d)) { $d->delete(); $removed++; }
            }
        }

        // Old-site addresses that the copied posts link to: sent permanently to the matching page of the new site,
        // so the links in the articles (and any Google traffic to them) keep working without editing the posts.
        foreach (self::OLD_PAGES as $from => $to) {
            \App\Models\Redirect::query()->firstOrCreate(['from_path' => $from], ['to_path' => $to, 'status_code' => 301]);
        }

        PageCache::flush();
        Repo::flush();
        $this->info("Blog: $added added, $updated updated, $skipped already there (use --force to overwrite)".($removed ? ", $removed demo posts removed" : '').'.');

        return self::SUCCESS;
    }
}
