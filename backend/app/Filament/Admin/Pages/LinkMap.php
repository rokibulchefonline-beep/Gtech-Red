<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Resources\PageResource;
use App\Support\Seo\Audit;
use App\Support\Seo\LinkGraph;
use App\Support\Site\Repo;
use Filament\Pages\Page;

/**
 * The internal links drawn as a map: the home page in the middle, service categories around it, their services
 * and the industries on the outside. Click a page to see what links to it and where it links.
 */
class LinkMap extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-share';
    protected static ?string $navigationGroup = 'SEO';
    protected static ?string $navigationLabel = 'Internal link map';
    protected static ?string $title = 'Internal link map';
    protected static ?string $slug = 'link-map';
    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.admin.pages.seo.link-map';

    /** Categorical colours (validated palette, shared with Analytics): light and dark mode. */
    private const PALETTE = [['#2a78d6', '#3987e5'], ['#eb6834', '#d95926'], ['#1baf7a', '#199e70'], ['#eda100', '#c98500'], ['#e87ba4', '#d55181'], ['#4a3aa7', '#9085e9'], ['#008300', '#2e9a2e'], ['#e34948', '#e66767']];

    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\Action::make('rebuild')->label('Rebuild now')->icon('heroicon-o-arrow-path')->color('gray')->action(fn () => Audit::site(true))];
    }

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->hasPerm('seo.view');
    }

    protected function getViewData(): array
    {
        $a = Audit::site();
        $g = $a['graph'];
        $nodes = collect($g['nodes'])->keyBy('id');
        $stats = $g['stats'];

        // Clusters around the centre: each service category, then industries, then landing pages if there are any.
        $clusters = Repo::groups()->pluck('title', 'slug')->all() + ['industries' => 'Industries'];
        if ($nodes->contains(fn ($n) => $n['kind'] === 'case')) $clusters['cases'] = 'Case studies';
        if ($nodes->contains(fn ($n) => $n['kind'] === 'post')) $clusters['blog'] = 'Blog posts';
        if ($nodes->contains(fn ($n) => $n['kind'] === 'landing')) $clusters['landing'] = 'Landing pages';
        $colour = [];
        foreach (array_keys($clusters) as $i => $c) $colour[$c] = $i < count(self::PALETTE) ? $i : null; // a 9th group is drawn in grey

        $pos = ['/' => [0, 0, 22]];
        $keys = array_keys($clusters);
        foreach ($keys as $i => $c) {
            $a0 = $i / count($keys) * M_PI * 2 - M_PI / 2;
            $span = M_PI * 2 / count($keys) - 0.12;
            $hub = $c === 'industries' ? '/industries' : "/services/$c";
            if ($nodes->has($hub)) $pos[$hub] = [cos($a0) * 150, sin($a0) * 150, 15];
            $kids = $nodes->filter(fn ($n) => $n['cluster'] === $c && $n['id'] !== $hub && in_array($n['kind'], ['service', 'industry', 'landing', 'post', 'case'], true))->values();
            // Many pages in a group (blog posts) are spread over up to six rings.
            $rings = max(2, min(6, (int) ceil($kids->count() / 16)));
            $perRing = (int) ceil($kids->count() / $rings);
            foreach ($kids as $j => $k) {
                $ring = $j % $rings;
                $idx = intdiv($j, $rings);
                $aa = $a0 - $span / 2 + ($perRing > 1 ? $idx / ($perRing - 1) * $span : $span / 2);
                $rad = 300 + $ring * ($rings > 2 ? 34 : 70);
                $small = in_array($k['kind'], ['post', 'case'], true);
                $pos[$k['id']] = [cos($aa) * $rad, sin($aa) * $rad, ($small ? 4 : 7) + min($small ? 5 : 7, ($stats[$k['id']]['in'] ?? 0) * 0.9)];
            }
        }
        foreach (['/services', '/case-studies', '/blogs', '/about', '/contact'] as $i => $id) $pos[$id] = [cos($i * 1.256 + 0.6) * 70, sin($i * 1.256 + 0.6) * 70, 8];

        $edit = function (string $id): ?string {
            $key = match (true) {
                str_starts_with($id, '/services/') => 'service~'.substr($id, 10),
                str_starts_with($id, '/industries/') => 'industry~'.substr($id, 12),
                default => \App\Models\Page::query()->where('path', $id)->value('key'),
            };
            return $key && \App\Models\Page::query()->whereKey($key)->exists() ? PageResource::getUrl('edit', ['record' => $key]) : null;
        };
        $outNodes = [];
        foreach ($pos as $id => [$x, $y, $r]) {
            if (! $nodes->has($id)) continue;
            $n = $nodes[$id];
            $outNodes[$id] = ['id' => $id, 'label' => $n['label'], 'x' => round($x, 1), 'y' => round($y, 1), 'r' => round($r, 1), 'post' => in_array($n['kind'], ['post', 'case'], true),
                'c' => $n['cluster'] === 'site' ? null : ($colour[$n['cluster']] ?? null), 'in' => $stats[$id]['in'] ?? 0, 'out' => $stats[$id]['out'] ?? 0];
        }
        $edges = array_values(array_filter($g['edges'], fn ($e) => isset($outNodes[$e['from']], $outNodes[$e['to']]) && ! in_array($e['type'], ['home', 'hub', 'breadcrumb', 'sector-peer'], true)));
        $edges = array_map(fn ($e) => $e + ['post' => $outNodes[$e['from']]['post'] || $outNodes[$e['to']]['post']], $edges);
        $links = [];
        foreach ($edges as $e) {
            $links[$e['from']]['out'][] = [$e['to'], $outNodes[$e['to']]['label'], $e['anchor'], LinkGraph::TYPES[$e['type']] ?? $e['type']];
            $links[$e['to']]['in'][] = [$e['from'], $outNodes[$e['from']]['label'], $e['anchor'], LinkGraph::TYPES[$e['type']] ?? $e['type']];
        }
        $info = [];
        $postIds = \App\Models\Post::query()->pluck('id', 'slug');
        $caseIds = \App\Models\CaseStudy::query()->pluck('id', 'slug');
        foreach ($outNodes as $id => $n) {
            $url = match (true) {
                str_starts_with($id, '/blogs/') && isset($postIds[substr($id, 7)]) => \App\Filament\Admin\Resources\PostResource::getUrl('edit', ['record' => $postIds[substr($id, 7)]]),
                str_starts_with($id, '/case-studies/') && isset($caseIds[substr($id, 14)]) => \App\Filament\Admin\Resources\CaseStudyResource::getUrl('edit', ['record' => $caseIds[substr($id, 14)]]),
                default => $n['post'] ? null : $edit($id),
            };
            $info[$id] = ['label' => $n['label'], 'edit' => $url, 'view' => \App\Filament\Support\SiteLink::to($id), 'out' => $links[$id]['out'] ?? [], 'in' => $links[$id]['in'] ?? [], 'post' => $n['post']];
        }
        $posts = count(array_filter($outNodes, fn ($n) => $n['post']));

        return ['nodes' => $outNodes, 'edges' => $edges, 'info' => $info, 'clusters' => $clusters, 'colour' => $colour, 'palette' => self::PALETTE,
            'postCount' => $posts, 'builtAt' => $a['at'], 'broken' => LinkGraph::broken($g),
            'types' => array_intersect_key(LinkGraph::TYPES, array_flip(['content', 'semantic', 'related', 'industries', 'sector-services', 'cases', 'category-list']))];
    }
}
