<?php

namespace App\Support\Seo;

use App\Models\Page;
use App\Models\SeoKeyword;
use App\Support\Site\Repo;

/**
 * The internal link graph of the website, built from the same data the pages render from: menus and hubs,
 * related services, industry sections, the keyword map's semantic links, the case studies shown on service pages,
 * and every link written in the content (page text, FAQ answers, blog posts, case studies). It is rebuilt
 * whenever content is saved, so new pages, posts and links appear by themselves. Used by the SEO audit, the
 * dashboard and the link map.
 */
class LinkGraph
{
    /** Editorial links in a page's body (they pass topical relevance); breadcrumbs, hubs and menus do not count. */
    public const CONTEXTUAL = ['related', 'semantic', 'industries', 'sector-services', 'category-list', 'content', 'cases'];

    public const TYPES = [
        'content' => 'Links in the text', 'cases' => 'Case studies shown',
        'semantic' => 'Semantic links', 'related' => 'Related services', 'industries' => 'Industry links', 'sector-services' => 'Sector → service',
        'category-list' => 'Category lists', 'breadcrumb' => 'Breadcrumbs', 'hub' => 'Hub pages', 'home' => 'Home page', 'sector-peer' => 'Other industries',
    ];

    public static function pathOf(string $target): string
    {
        return str_starts_with($target, '/') ? $target : (str_starts_with($target, 'i:') ? '/industries/'.substr($target, 2) : "/services/$target");
    }

    /** @return array{nodes: array<string, array{id:string,label:string,kind:string,cluster:string}>, edges: array<array{from:string,to:string,type:string,anchor:string}>} */
    public static function build(): array
    {
        $nodes = [];
        $edges = [];
        $node = function (string $id, string $label, string $kind, string $cluster) use (&$nodes) { $nodes[$id] = compact('id', 'label', 'kind', 'cluster'); };
        $add = function (string $from, string $to, string $type, string $anchor) use (&$edges) { if ($from !== $to) $edges[] = compact('from', 'to', 'type', 'anchor'); };

        $node('/', 'Home', 'home', 'site');
        foreach (['/services' => 'Services', '/industries' => 'Industries'] as $id => $l) $node($id, $l, 'hub', 'site');
        foreach (['/case-studies' => 'Case studies', '/blogs' => 'Blog', '/about' => 'About', '/contact' => 'Contact'] as $id => $l) $node($id, $l, 'page', 'site');
        foreach (['/services' => 'Services', '/industries' => 'Industries', '/case-studies' => 'Case studies', '/blogs' => 'Blog', '/about' => 'About us', '/contact' => 'Contact'] as $to => $l) $add('/', $to, 'home', $l);

        foreach (Repo::groups() as $g) {
            $gid = "/services/{$g->slug}";
            $node($gid, $g->title, 'category', $g->slug);
            $add('/', $gid, 'home', $g->title);
            $add('/services', $gid, 'hub', $g->title);
            foreach ($g->items as $it) {
                $id = "/services/{$it->slug}";
                $node($id, $it->name, 'service', $g->slug);
                $add($gid, $id, 'category-list', $it->name);
                $add($id, $gid, 'breadcrumb', $g->title);
                $add('/services', $id, 'hub', $it->name);
            }
        }
        $inds = Repo::industries();
        foreach ($inds as $i) {
            $id = "/industries/{$i->slug}";
            $node($id, $i->name, 'industry', 'industries');
            $add('/industries', $id, 'hub', $i->name);
            $add($id, '/industries', 'breadcrumb', 'Industries');
            foreach ($inds as $o) $add($id, "/industries/{$o->slug}", 'sector-peer', $o->name);
        }
        foreach (Page::query()->where('kind', 'landing')->where('published', true)->get() as $p) $node($p->path, $p->name, 'landing', 'landing');
        foreach (Page::query()->where('kind', 'legal')->where('published', true)->get() as $p) $node($p->path, $p->name, 'page', 'site');

        $items = Repo::allItems();
        foreach (Page::query()->whereIn('kind', ['service', 'industry', 'landing'])->where('published', true)->get() as $p) {
            $type = $p->kind === 'industry' ? 'sector-services' : 'related';
            foreach ((array) $p->related as $r) if ($it = $items[$r] ?? null) $add($p->path, "/services/$r", $type, $it->name);
            foreach ((array) $p->sections as $s) {
                if (($s['type'] ?? '') !== 'industries') continue;
                foreach ((array) ($s['items'] ?? []) as $it) if ($ind = Repo::industry($it['slug'] ?? '')) $add($p->path, "/industries/{$ind->slug}", 'industries', $ind->name);
            }
        }
        // Blog posts and case studies, with the links written in them.
        $posts = Repo::posts();
        foreach ($posts as $p) $node("/blogs/{$p->slug}", $p->title, 'post', 'blog');
        $cases = Repo::caseStudies(1000);
        foreach ($cases as $c) $node("/case-studies/{$c->slug}", $c->title, 'case', 'cases');
        $text = [];
        foreach (Page::query()->where('published', true)->get() as $p) {
            $parts = [(string) ($p->hero['lead'] ?? '')];
            foreach ((array) $p->sections as $s) array_walk_recursive($s, function ($v) use (&$parts) { if (is_string($v) && str_contains($v, '<a')) $parts[] = $v; });
            foreach ((array) $p->faqs as $f) $parts[] = (string) ($f['a'] ?? '');
            $text[$p->path] = implode(' ', $parts);
            // Service pages show the case studies tagged with their service.
            if (in_array($p->kind, ['service', 'landing'], true) && in_array('cases', array_column((array) $p->sections, 'type'), true)) {
                $svc = $p->kind === 'service' ? $p->slug : (((array) $p->data)['service_slug'] ?? '');
                foreach ($cases->filter(fn ($c) => in_array($svc, (array) $c->services, true))->take(6) as $c) $add($p->path, "/case-studies/{$c->slug}", 'cases', $c->title);
            }
        }
        foreach ($posts as $p) $text["/blogs/{$p->slug}"] = $p->format === 'html' ? (string) $p->body : \App\Support\Site\Blog::markdownToHtml((string) $p->body);
        foreach ($cases as $c) $text["/case-studies/{$c->slug}"] = implode(' ', array_filter([(string) $c->body, (string) $c->challenge, (string) $c->solution, ...array_filter((array) $c->results, 'is_string')]));
        $redirects = \App\Models\Redirect::query()->pluck('to_path', 'from_path')->all();
        foreach ($text as $from => $html) {
            foreach (self::links($html) as [$to, $anchor]) $add($from, $redirects[$to] ?? $to, 'content', $anchor);
        }

        foreach (SeoKeyword::query()->get() as $k) {
            $from = $inds->firstWhere('slug', $k->slug) ? "/industries/{$k->slug}" : "/services/{$k->slug}";
            foreach ((array) $k->links as $l) if (! empty($l['target'])) $add($from, self::pathOf($l['target']), 'semantic', (string) ($l['anchor'] ?? ''));
        }
        return ['nodes' => $nodes, 'edges' => $edges];
    }

    /** Internal links in a piece of HTML: [path, anchor text]. Links to this site's full address count too. */
    public static function links(string $html): array
    {
        if (! str_contains($html, '<a')) return [];
        $host = parse_url((string) config('gtech.public_url'), PHP_URL_HOST);
        preg_match_all('#<a\b[^>]*href\s*=\s*["\']([^"\']+)["\'][^>]*>(.*?)</a>#is', $html, $m, PREG_SET_ORDER);
        $out = [];
        foreach ($m as [, $href, $inner]) {
            $href = html_entity_decode(trim($href));
            if (preg_match('#^https?://([^/]+)(/[^?\#]*)?#i', $href, $u)) {
                if (! $host || preg_replace('/^www\./', '', strtolower($u[1])) !== preg_replace('/^www\./', '', strtolower($host))) continue;
                $path = $u[2] ?? '/';
            } elseif (str_starts_with($href, '/') && ! str_starts_with($href, '//')) {
                $path = preg_replace('/[?#].*$/', '', $href);
            } else continue;
            $path = $path === '/' ? '/' : rtrim($path, '/');
            if (preg_match('#^/(storage|media|build|js|css|images|api)/|\.(png|jpe?g|webp|gif|svg|pdf|mp4|css|js)$#i', $path)) continue;
            $out[] = [$path, trim(preg_replace('/\s+/', ' ', strip_tags($inner)))];
        }
        return $out;
    }

    /** Links in and out of every page: all of them, and the contextual (body) ones. */
    public static function stats(array $g): array
    {
        $out = array_map(fn () => ['in' => 0, 'out' => 0, 'allIn' => 0, 'allOut' => 0], $g['nodes']);
        foreach ($g['edges'] as $e) {
            if (! isset($out[$e['from']], $out[$e['to']])) continue;
            $out[$e['from']]['allOut']++;
            $out[$e['to']]['allIn']++;
            if (in_array($e['type'], self::CONTEXTUAL, true)) { $out[$e['from']]['out']++; $out[$e['to']]['in']++; }
        }
        return $out;
    }

    /** Semantic and in-text links pointing at a page that does not exist (or is unpublished). */
    public static function broken(array $g): array
    {
        $ids = array_flip(array_column($g['nodes'], 'id')); // nodes may be keyed by address or a plain list
        $known = fn (string $p) => isset($ids[$p]);
        return array_values(array_unique(array_map(fn ($e) => $e['from'].' → '.$e['to'].($e['type'] === 'content' ? ' (link in the text: “'.$e['anchor'].'”)' : ''),
            array_filter($g['edges'], fn ($e) => in_array($e['type'], ['semantic', 'content'], true) && ! $known($e['to'])))));
    }

    /** Service, category, industry and landing pages that no page body links to. */
    public static function orphans(array $g, array $stats): array
    {
        return array_values(array_keys(array_filter($g['nodes'], fn ($n) => in_array($n['kind'], ['service', 'industry', 'category', 'landing'], true) && ($stats[$n['id']]['in'] ?? 0) === 0)));
    }
}
