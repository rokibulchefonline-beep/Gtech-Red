<?php

namespace App\Support\Seo;

use App\Models\Setting;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Http;

/**
 * Crawls the website the way a search engine does: starts at the home page and the sitemap, opens every internal
 * page it finds, and checks every link and image on them (internal and external). Records error pages (404, 500),
 * redirects, broken links, and each page's inbound and outbound links. Runs every night and from SEO audit >
 * "Crawl the site now".
 *
 * Internal pages are rendered in a separate copy of the application (same database), so a crawl started from the
 * admin panel never touches the signed-in person's session. External links are checked over the internet, ten at
 * a time; their results are kept for a day.
 */
class Crawler
{
    public const MAX_PAGES = 1500;
    public const MAX_EXTERNAL = 1500;
    public const UA = 'Mozilla/5.0 (compatible; GTechSiteAudit/1.0; +https://www.gtechdigital.co.uk)';

    /** Addresses never crawled (the admin panel, previews, APIs). */
    private const SKIP = ['/admin', '/livewire', '/preview', '/api/', '/blade-preview', '/storage/livewire-tmp', '/filament', '/up'];

    private ?\Illuminate\Foundation\Application $site = null;
    private string $host;
    private int $runId;

    public function __construct()
    {
        $this->host = strtolower((string) parse_url((string) (Setting::group('general')['siteUrl'] ?? config('app.url')), PHP_URL_HOST));
    }

    public static function latest(): ?object
    {
        return DB::table('crawl_runs')->where('status', 'done')->orderByDesc('id')->first();
    }

    public static function running(): ?object
    {
        return DB::table('crawl_runs')->where('status', 'running')->where('started_at', '>', now()->subMinutes(30))->orderByDesc('id')->first();
    }

    /** A new crawl, marked as running (so the screen can show progress before it starts). */
    public static function start(string $trigger = 'manual'): int
    {
        return DB::table('crawl_runs')->insertGetId(['status' => 'running', 'trigger' => $trigger, 'started_at' => now(), 'created_at' => now(), 'updated_at' => now()]);
    }

    /** Runs a full crawl (the one started with start(), or a new one) and returns the finished run. */
    public function run(string $trigger = 'manual', ?int $id = null): object
    {
        @set_time_limit(900);
        @ignore_user_abort(true);
        $t0 = microtime(true);
        // A started crawl runs once, even if asked twice.
        if ($id !== null && ! DB::table('crawl_runs')->where('id', $id)->where('status', 'running')->whereNull('finished_at')->whereNull('message')->update(['message' => 'running'])) {
            return DB::table('crawl_runs')->find($id) ?? (object) ['status' => 'failed', 'message' => 'Not found', 'pages' => 0, 'links' => 0, 'broken' => 0, 'errors' => 0, 'redirects' => 0];
        }
        $this->runId = $id ?? self::start($trigger);
        try {
            $this->crawl();
            $this->checkExternal();
            $this->summarise();
            DB::table('crawl_runs')->where('id', $this->runId)->update(['status' => 'done', 'message' => null, 'finished_at' => now(), 'seconds' => (int) round(microtime(true) - $t0), 'updated_at' => now()]);
        } catch (\Throwable $e) {
            report($e);
            DB::table('crawl_runs')->where('id', $this->runId)->update(['status' => 'failed', 'message' => mb_substr($e->getMessage(), 0, 1000), 'finished_at' => now(), 'updated_at' => now()]);
        }
        // Only the last 10 crawls are kept.
        $old = DB::table('crawl_runs')->orderByDesc('id')->skip(10)->take(100)->pluck('id');
        if ($old->isNotEmpty()) {
            DB::table('crawl_links')->whereIn('run_id', $old)->delete();
            DB::table('crawl_pages')->whereIn('run_id', $old)->delete();
            DB::table('crawl_runs')->whereIn('id', $old)->delete();
        }
        return DB::table('crawl_runs')->find($this->runId);
    }

    // ---------------- internal pages ----------------

    private function crawl(): void
    {
        $queue = [];
        $seen = [];
        $push = function (string $path, int $depth, string $from) use (&$queue, &$seen) {
            if (isset($seen[$path]) || count($seen) >= self::MAX_PAGES) return;
            $seen[$path] = true;
            $queue[] = [$path, $depth, $from];
        };
        $push('/', 0, '');
        foreach ($this->sitemap() as $p) $push($p, 1, 'sitemap.xml');

        $links = [];
        while ($queue) {
            [$path, $depth, $from] = array_shift($queue);
            $res = $this->fetch($path);
            $page = ['run_id' => $this->runId, 'path' => mb_substr($path, 0, 500), 'status' => $res['status'], 'redirect_to' => mb_substr($res['location'], 0, 500),
                'ms' => $res['ms'], 'bytes' => strlen($res['body']), 'depth' => $depth, 'found_on' => mb_substr($from, 0, 500)];
            if ($res['status'] >= 300 && $res['status'] < 400 && $res['location'] !== '') {
                $to = $this->internalPath($res['location'], $path);
                if ($to !== null) $push($to, $depth, $path);
            }
            if ($res['status'] === 200 && str_contains($res['type'], 'html')) {
                $info = $this->parse($res['body'], $path);
                $page += ['title' => mb_substr($info['title'], 0, 300), 'h1' => $info['h1'], 'words' => $info['words'], 'noindex' => $info['noindex'], 'canonical' => mb_substr($info['canonical'], 0, 500)];
                foreach ($info['links'] as $l) {
                    $links[] = ['run_id' => $this->runId, 'from_path' => mb_substr($path, 0, 500), 'url' => mb_substr($l['url'], 0, 1000), 'kind' => $l['kind'], 'internal' => $l['internal'],
                        'anchor' => mb_substr($l['anchor'], 0, 300), 'nofollow' => $l['nofollow'], 'status' => 0, 'ok' => true, 'error' => ''];
                    if ($l['internal'] && $l['kind'] === 'link' && ! $this->isAsset($l['url'])) $push($l['url'], $depth + 1, $path);
                }
            }
            DB::table('crawl_pages')->insert($page);
            if (count($seen) % 20 === 0) DB::table('crawl_runs')->where('id', $this->runId)->update(['pages' => DB::table('crawl_pages')->where('run_id', $this->runId)->count(), 'updated_at' => now()]);
            if (count($links) >= 500) { DB::table('crawl_links')->insert($links); $links = []; }
        }
        if ($links) foreach (array_chunk($links, 500) as $c) DB::table('crawl_links')->insert($c);
        $this->site = null;

        // Internal link and image targets: the crawled status, or a check of the file for images and downloads.
        $status = DB::table('crawl_pages')->where('run_id', $this->runId)->pluck('status', 'path');
        DB::table('crawl_links')->where('run_id', $this->runId)->where('internal', true)->orderBy('id')->chunkById(500, function ($rows) use ($status) {
            foreach ($rows as $l) {
                $code = $status[$l->url] ?? ($this->isAsset($l->url) ? $this->assetStatus($l->url) : 0);
                if ($code === 0 && ! isset($status[$l->url])) { $r = $this->fetch($l->url); $code = $r['status']; }
                DB::table('crawl_links')->where('id', $l->id)->update(['status' => $code, 'ok' => $code > 0 && $code < 400]);
            }
        });
        $this->site = null;
    }

    /** Addresses listed in the sitemap. */
    private function sitemap(): array
    {
        $res = $this->fetch('/sitemap.xml');
        if ($res['status'] !== 200) return [];
        preg_match_all('~<loc>\s*([^<\s]+)\s*</loc>~i', $res['body'], $m);
        return array_values(array_filter(array_map(fn ($u) => $this->internalPath(html_entity_decode($u), '/'), $m[1])));
    }

    /** One internal address rendered by the site, without following redirects. */
    private function fetch(string $path): array
    {
        $t = microtime(true);
        $main = app();
        try {
            $this->site ??= $this->bootSite();
            Facade::setFacadeApplication($this->site);
            Facade::clearResolvedInstances();
            \Illuminate\Container\Container::setInstance($this->site);
            $req = Request::create($path, 'GET', server: ['HTTP_USER_AGENT' => self::UA, 'HTTP_ACCEPT' => 'text/html,*/*', 'HTTPS' => 'on', 'HTTP_HOST' => $this->host ?: 'localhost']);
            $kernel = $this->site->make(Kernel::class);
            $res = $kernel->handle($req);
            $kernel->terminate($req, $res);
            $body = method_exists($res, 'getContent') ? (string) $res->getContent() : '';
            $out = ['status' => $res->getStatusCode(), 'location' => (string) $res->headers->get('Location', ''), 'type' => (string) $res->headers->get('Content-Type', ''), 'body' => $body];
        } catch (\Throwable $e) {
            $out = ['status' => 500, 'location' => '', 'type' => '', 'body' => ''];
        } finally {
            Facade::setFacadeApplication($main);
            Facade::clearResolvedInstances();
            \Illuminate\Container\Container::setInstance($main);
        }
        return $out + ['ms' => (int) round((microtime(true) - $t) * 1000)];
    }

    /** A separate copy of the application for rendering pages, sharing this one's database connection. */
    private function bootSite(): \Illuminate\Foundation\Application
    {
        $main = app();
        $pdo = DB::connection()->getPdo();
        $site = require base_path('bootstrap/app.php');
        $site->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        $site['db']->connection()->setPdo($pdo)->setReadPdo($pdo);
        if ($main->bound('config')) $site['config']->set('cache.default', $main['config']->get('cache.default'));
        Facade::setFacadeApplication($main);
        \Illuminate\Container\Container::setInstance($main);
        return $site;
    }

    /** Title, H1 count, words, robots, canonical and every link and image on a page. */
    private function parse(string $html, string $base): array
    {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NOWARNING | LIBXML_NOERROR);
        libxml_clear_errors();
        $xp = new \DOMXPath($dom);
        $title = trim((string) ($xp->query('//title')->item(0)?->textContent ?? ''));
        $robots = strtolower((string) ($xp->query('//meta[@name="robots"]/@content')->item(0)?->nodeValue ?? ''));
        $canonical = (string) ($xp->query('//link[@rel="canonical"]/@href')->item(0)?->nodeValue ?? '');
        $main = $xp->query('//main')->item(0);
        $words = str_word_count(strip_tags(preg_replace('~<(script|style)\b[\s\S]*?</\1>~i', '', (string) ($main ? $dom->saveHTML($main) : $html))));
        $links = [];
        $seen = [];
        $add = function (string $raw, string $kind, string $anchor, bool $nofollow) use (&$links, &$seen, $base) {
            $raw = trim($raw);
            if ($raw === '' || preg_match('~^(#|mailto:|tel:|javascript:|data:|sms:|whatsapp:)~i', $raw)) return;
            $int = $this->internalPath($raw, $base);
            $url = $int ?? $this->absolute($raw);
            if ($url === null) return;
            $key = $kind.'|'.$url;
            if (isset($seen[$key])) return; // one row per target per page
            $seen[$key] = true;
            $links[] = ['url' => $url, 'internal' => $int !== null, 'kind' => $kind, 'anchor' => trim(preg_replace('/\s+/', ' ', $anchor)), 'nofollow' => $nofollow];
        };
        foreach ($xp->query('//a[@href]') as $a) {
            $label = $a->textContent ?: ($a->getAttribute('aria-label') ?: (($img = $xp->query('.//img/@alt', $a)->item(0)) ? $img->nodeValue : ''));
            $add($a->getAttribute('href'), 'link', (string) $label, str_contains(strtolower($a->getAttribute('rel')), 'nofollow'));
        }
        foreach ($xp->query('//img[@src]') as $img) $add($img->getAttribute('src'), 'image', $img->getAttribute('alt'), false);
        return ['title' => $title, 'h1' => $xp->query('//h1')->length, 'words' => $words, 'noindex' => str_contains($robots, 'noindex'), 'canonical' => $canonical, 'links' => $links];
    }

    /** The site path of an internal link, or null for external links. */
    private function internalPath(string $url, string $base): ?string
    {
        if (str_starts_with($url, '//')) $url = 'https:'.$url;
        $p = parse_url($url);
        if ($p === false) return null;
        if (isset($p['host'])) {
            $h = strtolower($p['host']);
            if (! in_array($h, array_filter([$this->host, 'www.'.ltrim($this->host, 'w.'), preg_replace('/^www\./', '', $this->host), strtolower((string) parse_url((string) config('app.url'), PHP_URL_HOST))]), true)) return null;
        } elseif (isset($p['scheme'])) {
            return null;
        }
        $path = $p['path'] ?? '';
        if ($path === '' && ! isset($p['host'])) return null;
        if ($path !== '' && $path[0] !== '/') $path = rtrim(dirname($base), '/').'/'.$path;
        $path = '/'.ltrim($path ?: '/', '/');
        if ($path !== '/' ) $path = rtrim($path, '/') ?: '/';
        foreach (self::SKIP as $s) if (str_starts_with($path, $s)) return null;
        return $path.(isset($p['query']) && ! preg_match('/^(utm_|fbclid|gclid)/', $p['query']) ? '?'.$p['query'] : '');
    }

    private function absolute(string $url): ?string
    {
        if (str_starts_with($url, '//')) $url = 'https:'.$url;
        return preg_match('~^https?://[^\s/$.?#].[^\s]*$~i', $url) ? strtok($url, '#') : null;
    }

    private function isAsset(string $path): bool
    {
        return (bool) preg_match('~\.(webp|png|jpe?g|gif|svg|avif|ico|pdf|zip|css|js|mp4|webm|woff2?|txt|xml)$~i', parse_url($path, PHP_URL_PATH) ?? '') || str_starts_with($path, '/api/media/');
    }

    /** Images and files: found in the public folder (or uploads) = 200, otherwise 404. */
    private function assetStatus(string $path): int
    {
        $p = rawurldecode((string) parse_url($path, PHP_URL_PATH));
        if (str_contains($p, '..')) return 404;
        if (str_starts_with($p, '/api/media/')) {
            $id = substr($p, 11);
            $m = \App\Models\Media::query()->where('legacy_id', $id)->orWhere('id', ctype_digit($id) ? (int) $id : 0)->first();
            return $m && \Illuminate\Support\Facades\Storage::disk('public')->exists($m->path) ? 200 : 404;
        }
        if (is_file(public_path(ltrim($p, '/')))) return 200;
        if (str_starts_with($p, '/storage/') && \Illuminate\Support\Facades\Storage::disk('public')->exists(substr($p, 9))) return 200;
        return 404;
    }

    // ---------------- external links ----------------

    private function checkExternal(): void
    {
        $urls = DB::table('crawl_links')->where('run_id', $this->runId)->where('internal', false)->distinct()->limit(self::MAX_EXTERNAL)->pluck('url')->all();
        $results = [];
        $todo = [];
        foreach ($urls as $u) {
            $c = Cache::get('crawl-ext:'.sha1($u));
            $c ? $results[$u] = $c : $todo[] = $u;
        }
        foreach (array_chunk($todo, 10) as $chunk) {
            $responses = Http::pool(fn (Pool $pool) => array_map(fn ($u) => $pool->as($u)->withHeaders(['User-Agent' => self::UA, 'Accept' => 'text/html,*/*'])
                ->timeout(12)->connectTimeout(6)->withOptions(['allow_redirects' => ['max' => 5]])->head($u), $chunk));
            foreach ($chunk as $u) {
                $r = $responses[$u] ?? null;
                $code = $r instanceof \Illuminate\Http\Client\Response ? $r->status() : ($r instanceof \Illuminate\Http\Client\RequestException ? $r->response->status() : 0);
                // Many sites refuse HEAD or bots: ask again with GET before calling a link broken.
                if ($code === 0 || in_array($code, [403, 405, 429, 501], true) || $code >= 500) {
                    try { $code = Http::withHeaders(['User-Agent' => self::UA])->timeout(15)->connectTimeout(6)->get($u)->status(); }
                    catch (\Illuminate\Http\Client\RequestException $e) { $code = $e->response->status(); }
                    catch (\Throwable $e) { $code = 0; $err = $e->getMessage(); }
                }
                $results[$u] = ['status' => $code, 'error' => $code === 0 ? mb_substr($err ?? 'Could not connect', 0, 300) : ''];
                Cache::put('crawl-ext:'.sha1($u), $results[$u], now()->addDay());
                unset($err);
            }
        }
        foreach ($results as $u => $r) {
            // 401, 403 and 429 mean the site is there but refuses robots: not broken.
            $ok = ($r['status'] > 0 && $r['status'] < 400) || in_array($r['status'], [401, 403, 429, 999], true);
            DB::table('crawl_links')->where('run_id', $this->runId)->where('url', $u)->where('internal', false)->update(['status' => $r['status'], 'ok' => $ok, 'error' => $r['error']]);
        }
    }

    // ---------------- totals ----------------

    private function summarise(): void
    {
        $id = $this->runId;
        $inbound = DB::table('crawl_links')->where('run_id', $id)->where('internal', true)->where('kind', 'link')->selectRaw('url, count(distinct from_path) n')->groupBy('url')->pluck('n', 'url');
        $out = DB::table('crawl_links')->where('run_id', $id)->selectRaw("from_path, sum(case when internal and kind = 'link' then 1 else 0 end) i, sum(case when internal or kind <> 'link' then 0 else 1 end) e, sum(case when ok then 0 else 1 end) b")
            ->groupBy('from_path')->get()->keyBy('from_path');
        DB::table('crawl_pages')->where('run_id', $id)->orderBy('id')->chunkById(500, function ($pages) use ($inbound, $out) {
            foreach ($pages as $p) DB::table('crawl_pages')->where('id', $p->id)->update(['inbound' => (int) ($inbound[$p->path] ?? 0),
                'out_internal' => (int) ($out[$p->path]->i ?? 0), 'out_external' => (int) ($out[$p->path]->e ?? 0), 'broken_links' => (int) ($out[$p->path]->b ?? 0)]);
        });
        DB::table('crawl_runs')->where('id', $id)->update([
            'pages' => DB::table('crawl_pages')->where('run_id', $id)->count(),
            'links' => DB::table('crawl_links')->where('run_id', $id)->count(),
            'broken' => DB::table('crawl_links')->where('run_id', $id)->where('ok', false)->count(),
            'errors' => DB::table('crawl_pages')->where('run_id', $id)->where('status', '>=', 400)->count(),
            'redirects' => DB::table('crawl_pages')->where('run_id', $id)->whereBetween('status', [300, 399])->count(),
            'external' => DB::table('crawl_links')->where('run_id', $id)->where('internal', false)->distinct()->count('url'),
        ]);
    }

    /** After the nightly crawl: an email when there are broken links or error pages that the previous crawl did not have. */
    public static function alert(object $run): void
    {
        $prev = DB::table('crawl_runs')->where('status', 'done')->where('id', '<', $run->id)->orderByDesc('id')->first();
        $new = DB::table('crawl_links')->where('run_id', $run->id)->where('ok', false)
            ->when($prev, fn ($q) => $q->whereNotExists(fn ($e) => $e->from('crawl_links as o')->where('o.run_id', $prev->id)->where('o.ok', false)->whereColumn('o.url', 'crawl_links.url')->whereColumn('o.from_path', 'crawl_links.from_path')))
            ->limit(30)->get();
        $to = (Setting::group('smtp')['notifyTo'] ?? '') ?: (Setting::group('contact')['email'] ?? '');
        if ($new->isEmpty() || ! $to) return;
        $rows = $new->map(fn ($l) => '<tr><td style="padding:4px 10px 4px 0">'.e($l->from_path).'</td><td style="padding:4px 10px 4px 0">'.e($l->url).'</td><td>'.($l->status ?: 'no reply').'</td></tr>')->implode('');
        try {
            \App\Support\SiteMailer::send($to, $new->count().' new broken '.str('link')->plural($new->count()).' on the website',
                '<p>The nightly crawl found '.$run->broken.' broken links and '.$run->errors.' error pages in total. New since the last crawl:</p><table style="font-size:13px"><tr><th align="left">On page</th><th align="left">Link</th><th align="left">Status</th></tr>'.$rows.'</table><p>See SEO > SEO audit > Site health.</p>');
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
