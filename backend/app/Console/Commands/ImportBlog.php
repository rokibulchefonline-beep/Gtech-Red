<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Media;
use App\Models\Post;
use App\Support\Site\PageCache;
use App\Support\Site\Repo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Copies the blog posts of the current website (default https://www.gtechdigital.co.uk) into this site, unchanged:
 * the article HTML, headings, internal and external links, dates, author, categories, tags, SEO title and description
 * stay exactly as they are. Images (featured and in the article) are downloaded into the media library as they are
 * (no resizing or re-compression); only their address changes, because the old server goes away.
 *
 * WordPress sites are read through their REST API (/wp-json/wp/v2/posts), which gives the exact stored content;
 * any other site is crawled from /blog/. Safe to run again: posts are matched by their original id or address and
 * updated, never duplicated. A check report is written to storage/app/blog-import-report.json.
 */
class ImportBlog extends Command
{
    protected $signature = 'gtech:import-blog {site=https://www.gtechdigital.co.uk : The current website}
        {--blog=/blog/ : Blog listing path, used when the site is not WordPress}
        {--dry : Only list what would be imported}';

    protected $description = 'Import the blog posts of the current website, unchanged, with their images';

    private string $site;
    private string $host;
    /** Old image address => new address, so an image used twice is downloaded once. */
    private array $images = [];
    private array $report = [];

    public function handle(): int
    {
        $this->site = rtrim((string) $this->argument('site'), '/');
        $this->host = preg_replace('/^www\./', '', strtolower((string) parse_url($this->site, PHP_URL_HOST)));
        if (! $this->host) { $this->error('Give the full website address, e.g. https://www.gtechdigital.co.uk'); return self::FAILURE; }

        $posts = $this->fromWordPress();
        if ($posts === null) {
            $this->info('No WordPress API found; crawling '.$this->site.$this->option('blog'));
            $posts = $this->fromCrawl();
        }
        if (! $posts) { $this->error('No posts found.'); return self::FAILURE; }
        $this->info(count($posts).' posts found.');

        if ($this->option('dry')) {
            foreach ($posts as $p) $this->line(' - '.$p['date'].'  '.$p['slug'].'  '.$p['title']);
            return self::SUCCESS;
        }

        foreach ($posts as $p) {
            $this->line('Importing: '.$p['title']);
            $this->save($p);
        }
        PageCache::flush();
        Repo::flush();
        Storage::disk('local')->put('blog-import-report.json', json_encode($this->report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $bad = array_filter($this->report, fn ($r) => ! $r['ok']);
        $this->info(count($posts).' posts imported. Report: storage/app/blog-import-report.json'.($bad ? ' ('.count($bad).' need a look)' : ''));
        foreach ($bad as $r) $this->warn(' ! '.$r['slug'].': '.implode('; ', $r['problems']));
        return self::SUCCESS;
    }

    // ---- Reading the old site -----------------------------------------------------------------------------

    private function get(string $url): ?\Illuminate\Http\Client\Response
    {
        try {
            $r = Http::timeout(40)->retry(2, 800, throw: false)->withHeaders(['User-Agent' => 'GTech blog import'])->get($url);
            return $r->successful() ? $r : null;
        } catch (\Throwable) {
            return null;
        }
    }

    /** All posts from the WordPress REST API, or null when the site is not WordPress. */
    private function fromWordPress(): ?array
    {
        $out = [];
        for ($page = 1; $page <= 50; $page++) {
            $r = $this->get($this->site.'/wp-json/wp/v2/posts?per_page=100&page='.$page.'&_embed=1&status=publish');
            if (! $r || ! is_array($r->json())) return $page === 1 ? null : $out;
            $rows = $r->json();
            if (! $rows || ! array_is_list($rows)) break;
            foreach ($rows as $w) {
                $e = $w['_embedded'] ?? [];
                $media = $e['wp:featuredmedia'][0] ?? [];
                $terms = collect($e['wp:term'] ?? [])->flatten(1);
                $y = $w['yoast_head_json'] ?? [];
                $rm = $w['rank_math'] ?? [];
                $out[] = [
                    'id' => 'wp:'.$w['id'],
                    'url' => (string) ($w['link'] ?? ''),
                    'slug' => (string) $w['slug'],
                    'title' => html_entity_decode(strip_tags((string) ($w['title']['rendered'] ?? '')), ENT_QUOTES | ENT_HTML5),
                    'body' => (string) ($w['content']['rendered'] ?? ''),
                    'excerpt' => trim(html_entity_decode(strip_tags((string) ($w['excerpt']['rendered'] ?? '')), ENT_QUOTES | ENT_HTML5)),
                    'date' => (string) ($w['date'] ?? ''),
                    'author' => (string) ($e['author'][0]['name'] ?? ''),
                    'categories' => $terms->where('taxonomy', 'category')->pluck('name')->map(fn ($n) => html_entity_decode($n, ENT_QUOTES | ENT_HTML5))->values()->all(),
                    'tags' => $terms->where('taxonomy', 'post_tag')->pluck('name')->map(fn ($n) => html_entity_decode($n, ENT_QUOTES | ENT_HTML5))->values()->all(),
                    'image' => (string) ($media['source_url'] ?? ''),
                    'image_alt' => (string) ($media['alt_text'] ?? ''),
                    'image_caption' => trim(strip_tags((string) ($media['caption']['rendered'] ?? ''))),
                    'meta_title' => (string) ($y['title'] ?? $rm['title'] ?? ''),
                    'meta_description' => (string) ($y['description'] ?? $y['og_description'] ?? $rm['description'] ?? ''),
                    'canonical' => (string) ($y['canonical'] ?? ''),
                    'focus_keyword' => '',
                ];
            }
            if (count($rows) < 100) break;
        }
        return $out;
    }

    /** Any other site: follow the blog listing (and its pages), then read each post. */
    private function fromCrawl(): array
    {
        $blog = '/'.trim((string) $this->option('blog'), '/').'/';
        $queue = [$this->site.$blog];
        $seen = [];
        $links = [];
        while ($queue && count($seen) < 60) {
            $url = array_shift($queue);
            if (isset($seen[$url])) continue;
            $seen[$url] = true;
            $r = $this->get($url);
            if (! $r) continue;
            foreach ($this->hrefs((string) $r->body(), $url) as $h) {
                $path = (string) parse_url($h, PHP_URL_PATH);
                if (! $this->sameSite($h) || ! str_starts_with(rtrim($path, '/').'/', $blog)) continue;
                $rest = trim(substr($path, strlen($blog)), '/');
                if ($rest === '') continue;
                // Listing pages (/blog/page/2/, ?page=2) are followed; anything else under /blog/ is a post.
                if (preg_match('#^(page/\d+|category/|tag/|author/)#', $rest) || preg_match('#[?&]page=\d+#', $h)) { $queue[] = strtok($h, '#'); continue; }
                if (! str_contains($rest, '/')) $links[strtok($h, '?#')] = true;
            }
        }
        $out = [];
        foreach (array_keys($links) as $url) {
            $r = $this->get($url);
            if ($r && ($p = $this->readPost((string) $r->body(), $url))) $out[] = $p;
        }
        return $out;
    }

    /** One post page: the article element and the page's meta data. */
    private function readPost(string $html, string $url): ?array
    {
        $dom = new \DOMDocument();
        @$dom->loadHTML('<?xml encoding="utf-8"?>'.$html, LIBXML_NOERROR | LIBXML_NOWARNING);
        $x = new \DOMXPath($dom);
        $meta = function (string $attr, string $name) use ($x): string {
            $n = $x->query("//meta[@$attr='$name']/@content")->item(0);
            return $n ? trim((string) $n->nodeValue) : '';
        };
        $body = null;
        foreach (["//*[@itemprop='articleBody']", "//*[contains(concat(' ',normalize-space(@class),' '),' entry-content ')]",
            "//*[contains(concat(' ',normalize-space(@class),' '),' post-content ')]", '//article', '//main'] as $q) {
            if ($n = $x->query($q)->item(0)) { $body = $n; break; }
        }
        if (! $body) return null;
        $h1 = $x->query('//h1')->item(0);
        $inner = '';
        foreach ($body->childNodes as $c) $inner .= $dom->saveHTML($c);
        // The post's own H1 (shown by this site's template) is not repeated inside the article.
        if ($h1 && $body->contains($h1)) $inner = str_replace($dom->saveHTML($h1), '', $inner);
        $slug = trim(substr((string) parse_url($url, PHP_URL_PATH), strlen('/'.trim((string) $this->option('blog'), '/').'/')), '/');
        $canon = $x->query("//link[@rel='canonical']/@href")->item(0);
        $cats = [];
        foreach ($x->query("//meta[@property='article:section']/@content") as $n) $cats[] = trim($n->nodeValue);
        $tags = [];
        foreach ($x->query("//meta[@property='article:tag']/@content") as $n) $tags[] = trim($n->nodeValue);
        $titleTag = $x->query('//title')->item(0);
        return [
            'id' => 'url:'.parse_url($url, PHP_URL_PATH),
            'url' => $url,
            'slug' => $slug,
            'title' => trim($h1?->textContent ?: $meta('property', 'og:title')),
            'body' => trim($inner),
            'excerpt' => $meta('name', 'description'),
            'date' => $meta('property', 'article:published_time'),
            'author' => $meta('name', 'author'),
            'categories' => $cats,
            'tags' => $tags,
            'image' => $meta('property', 'og:image'),
            'image_alt' => $meta('property', 'og:image:alt'),
            'image_caption' => '',
            'meta_title' => trim((string) $titleTag?->textContent),
            'meta_description' => $meta('name', 'description'),
            'canonical' => $canon ? (string) $canon->nodeValue : '',
            'focus_keyword' => '',
        ];
    }

    private function hrefs(string $html, string $base): array
    {
        preg_match_all('/<a\s[^>]*href=["\']([^"\'#]+)/i', $html, $m);
        return array_values(array_unique(array_map(fn ($h) => $this->absolute(html_entity_decode($h), $base), $m[1])));
    }

    private function absolute(string $u, string $base): string
    {
        if (preg_match('#^https?://#i', $u)) return $u;
        if (str_starts_with($u, '//')) return 'https:'.$u;
        $b = parse_url($base);
        $root = $b['scheme'].'://'.$b['host'];
        if (str_starts_with($u, '/')) return $root.$u;
        return $root.rtrim(dirname($b['path'] ?? '/'), '/').'/'.$u;
    }

    private function sameSite(string $u): bool
    {
        return preg_replace('/^www\./', '', strtolower((string) parse_url($u, PHP_URL_HOST))) === $this->host;
    }

    // ---- Saving -----------------------------------------------------------------------------------------

    private function save(array $p): void
    {
        $body = $this->localImages($p['body'], $p['url']);
        $image = $p['image'] ? $this->download($this->absolute($p['image'], $p['url'])) : '';
        foreach ($p['categories'] as $c) Category::query()->firstOrCreate(['slug' => Str::slug($c)], ['name' => $c]);
        $date = $p['date'] ? \Illuminate\Support\Carbon::parse($p['date']) : now();

        $post = Post::query()->where('legacy_id', $p['id'])->first() ?? Post::query()->where('slug', $p['slug'])->first() ?? new Post();
        $post->fill([
            'legacy_id' => $p['id'], 'title' => $p['title'], 'slug' => $p['slug'], 'body' => $body, 'format' => 'html',
            'excerpt' => mb_substr($p['excerpt'], 0, 400), 'categories' => $p['categories'], 'category' => $p['categories'][0] ?? '',
            'tags' => $p['tags'], 'image' => $image ?: ($post->image ?? ''), 'image_alt' => $p['image_alt'], 'image_caption' => $p['image_caption'],
            'author' => $p['author'] ?: ($post->author ?: \App\Support\Site\Blog::AUTHOR), 'status' => 'published', 'visibility' => 'public', 'date' => $date,
            'meta_title' => $p['meta_title'], 'meta_description' => mb_substr($p['meta_description'], 0, 300),
            'canonical' => $this->ownCanonical($p['canonical']), 'focus_keyword' => $p['focus_keyword'] ?: ($post->focus_keyword ?? ''),
        ]);
        $post->save();
        $this->check($p, $post->fresh(), $image);
    }

    /** A canonical pointing at the old address of the same post is dropped (this site makes its own). */
    private function ownCanonical(string $c): string
    {
        return $c === '' || $this->sameSite($c) ? '' : $c;
    }

    /** Every image in the article is downloaded; only its src changes. srcset (sizes of the old server) is dropped. */
    private function localImages(string $html, string $base): string
    {
        return preg_replace_callback('/<img\b[^>]*>/i', function ($m) use ($base) {
            $tag = $m[0];
            if (! preg_match('/\ssrc=(["\'])(.*?)\1/i', $tag, $s)) return $tag;
            $src = html_entity_decode($s[2]);
            // Lazy-loading plugins keep the real file in data-src.
            if (preg_match('/\sdata-(?:lazy-)?src=(["\'])(.*?)\1/i', $tag, $d) && ! str_starts_with($d[2], 'data:')) $src = html_entity_decode($d[2]);
            if (str_starts_with($src, 'data:')) return $tag;
            $new = $this->download($this->absolute($src, $base));
            if ($new === '') return $tag;
            $tag = preg_replace('/\ssrc=(["\']).*?\1/i', ' src="'.e($new).'"', $tag, 1);
            return preg_replace('/\s(srcset|sizes|data-(?:lazy-)?src|data-(?:lazy-)?srcset)=(["\']).*?\2/i', '', $tag);
        }, $html);
    }

    /** Downloads one image as it is into the media library; returns its new address ('' when it fails). */
    private function download(string $url): string
    {
        if ($url === '') return '';
        if (isset($this->images[$url])) return $this->images[$url];
        $r = $this->get($url);
        $type = $r ? strtolower(strtok((string) $r->header('Content-Type'), ';')) : '';
        if (! $r || ! str_starts_with($type, 'image/')) return $this->images[$url] = '';
        $name = Str::limit(preg_replace('/[^A-Za-z0-9._-]+/', '-', basename((string) parse_url($url, PHP_URL_PATH))) ?: 'image', 120, '');
        $path = 'media/blog/'.$name;
        $disk = Storage::disk('public');
        if ($disk->exists($path) && $disk->size($path) !== strlen($r->body())) $path = 'media/blog/'.Str::random(6).'-'.$name;
        $disk->put($path, $r->body());
        $m = Media::query()->firstOrCreate(['path' => $path], ['name' => $name, 'alt' => '', 'type' => $type, 'size' => strlen($r->body()), 'uploaded_by' => 'blog import']);
        return $this->images[$url] = '/api/media/'.$m->id;
    }

    // ---- Checks ------------------------------------------------------------------------------------------

    private function check(array $p, Post $saved, string $image): void
    {
        $count = fn (string $re, string $h) => preg_match_all($re, $h);
        $words = fn (string $h) => str_word_count(strip_tags($h));
        $problems = [];
        if ($words($p['body']) !== $words((string) $saved->body)) $problems[] = 'word count changed ('.$words($p['body']).' -> '.$words((string) $saved->body).')';
        if ($count('/<a\s[^>]*href=/i', $p['body']) !== $count('/<a\s[^>]*href=/i', (string) $saved->body)) $problems[] = 'link count changed';
        $imgs = $count('/<img\b/i', $p['body']);
        $local = $count('/<img\b[^>]*src="\/api\/media\/\d+"/i', (string) $saved->body);
        if ($imgs !== $local) $problems[] = ($imgs - $local).' article image(s) could not be downloaded';
        if ($p['image'] && $image === '') $problems[] = 'featured image could not be downloaded';
        if (! $p['image']) $problems[] = 'the old post has no featured image';
        $this->report[] = ['slug' => $saved->slug, 'title' => $saved->title, 'old_url' => $p['url'], 'new_url' => '/blog/'.$saved->slug,
            'words' => $words((string) $saved->body), 'links' => $count('/<a\s[^>]*href=/i', (string) $saved->body), 'images' => $imgs,
            'featured_image' => $image, 'ok' => ! $problems, 'problems' => $problems];
    }
}
