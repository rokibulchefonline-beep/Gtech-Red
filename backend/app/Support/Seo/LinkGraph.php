<?php

namespace App\Support\Seo;

use App\Models\Page;
use App\Models\SeoKeyword;
use App\Support\Site\Repo;

/**
 * The internal link graph of the website, built from the same data the pages render from: menus and hubs,
 * related services, industry sections and the keyword map's semantic links. Used by the SEO audit, the
 * dashboard and the link map.
 */
class LinkGraph
{
    /** Editorial links in a page's body (they pass topical relevance); breadcrumbs, hubs and menus do not count. */
    public const CONTEXTUAL = ['related', 'semantic', 'industries', 'sector-services', 'category-list'];

    public const TYPES = [
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

        $items = Repo::allItems();
        foreach (Page::query()->whereIn('kind', ['service', 'industry', 'landing'])->where('published', true)->get() as $p) {
            $type = $p->kind === 'industry' ? 'sector-services' : 'related';
            foreach ((array) $p->related as $r) if ($it = $items[$r] ?? null) $add($p->path, "/services/$r", $type, $it->name);
            foreach ((array) $p->sections as $s) {
                if (($s['type'] ?? '') !== 'industries') continue;
                foreach ((array) ($s['items'] ?? []) as $it) if ($ind = Repo::industry($it['slug'] ?? '')) $add($p->path, "/industries/{$ind->slug}", 'industries', $ind->name);
            }
        }
        foreach (SeoKeyword::query()->get() as $k) {
            $from = $inds->firstWhere('slug', $k->slug) ? "/industries/{$k->slug}" : "/services/{$k->slug}";
            foreach ((array) $k->links as $l) if (! empty($l['target'])) $add($from, self::pathOf($l['target']), 'semantic', (string) ($l['anchor'] ?? ''));
        }
        return ['nodes' => $nodes, 'edges' => $edges];
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

    /** Semantic links pointing at a page that does not exist (or is unpublished). */
    public static function broken(array $g): array
    {
        return array_values(array_map(fn ($e) => $e['from'].' → '.$e['to'], array_filter($g['edges'], fn ($e) => $e['type'] === 'semantic' && ! isset($g['nodes'][$e['to']]))));
    }

    /** Service, category, industry and landing pages that no page body links to. */
    public static function orphans(array $g, array $stats): array
    {
        return array_values(array_keys(array_filter($g['nodes'], fn ($n) => in_array($n['kind'], ['service', 'industry', 'category', 'landing'], true) && ($stats[$n['id']]['in'] ?? 0) === 0)));
    }
}
