<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Resources\PageResource;
use App\Models\SeoKeyword;
use App\Support\Seo\Audit;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Livewire\Attributes\Url;

/**
 * Every check for every page (SEO, AEO, GEO), the blog posts, the keyword and entity map, and the internal links:
 * orphans, broken semantic links and pages competing for the same keyword.
 */
class SeoAudit extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = 'SEO';
    protected static ?string $navigationLabel = 'SEO audit';
    protected static ?string $title = 'SEO audit';
    protected static ?string $slug = 'seo-audit';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.admin.pages.seo.audit';

    public const TABS = ['pages' => 'Pages', 'posts' => 'Blog posts', 'keywords' => 'Keyword map', 'links' => 'Internal links'];

    #[Url] public string $tab = 'pages';
    #[Url] public string $q = '';
    #[Url] public string $kind = '';
    #[Url] public string $sort = 'total';
    #[Url] public ?string $open = null;
    #[Url] public int $p = 1;

    public const PER_PAGE = 25;

    /** A new search, filter, sort or tab starts at page 1. */
    public function updated(string $name): void
    {
        if (in_array($name, ['q', 'kind', 'sort', 'tab'], true)) { $this->p = 1; $this->open = null; }
    }

    public function goTo(int $page): void
    {
        $this->p = max(1, $page);
        $this->open = null;
    }

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->hasPerm('seo.view');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('rerun')->label('Run again')->icon('heroicon-o-arrow-path')->color('gray')->action(fn () => Audit::site(true)),
            Action::make('map')->label('Link map')->icon('heroicon-o-share')->color('gray')->url(LinkMap::getUrl()),
        ];
    }

    public function toggle(string $path): void
    {
        $this->open = $this->open === $path ? null : $path;
    }

    public static function editUrl(array $p): string
    {
        return PageResource::getUrl('edit', ['record' => $p['key']]);
    }

    protected function getViewData(): array
    {
        $a = Audit::site();
        $q = mb_strtolower(trim($this->q));
        $rows = array_values(array_filter($a['pages'], fn ($p) => (! $this->kind || $p['kind'] === $this->kind)
            && ($q === '' || str_contains(mb_strtolower($p['name'].' '.$p['path'].' '.$p['keyword']), $q))));
        $sort = in_array($this->sort, ['total', 'seo', 'aeo', 'geo', 'name'], true) ? $this->sort : 'total';
        usort($rows, fn ($x, $y) => $sort === 'name' ? strcmp($x['name'], $y['name']) : $x['scores'][$sort] <=> $y['scores'][$sort]);
        $posts = array_values(array_filter($a['posts'], fn ($p) => $q === '' || str_contains(mb_strtolower($p['title'].' '.$p['path'].' '.$p['keyword']), $q)));
        usort($posts, fn ($x, $y) => match ($sort) { 'name' => strcmp($x['title'], $y['title']), default => $x['score'] <=> $y['score'] });
        // One page of results for the open tab.
        $list = $this->tab === 'posts' ? $posts : $rows;
        $pages = max(1, (int) ceil(count($list) / self::PER_PAGE));
        $this->p = min(max(1, $this->p), $pages);
        $slice = fn (array $l) => array_slice($l, ($this->p - 1) * self::PER_PAGE, self::PER_PAGE);
        $g = $a['graph'];
        $linkRows = array_values(array_filter($g['nodes'], fn ($n) => in_array($n['kind'], ['service', 'industry', 'category', 'landing'], true)));
        usort($linkRows, fn ($x, $y) => ($g['stats'][$x['id']]['in'] ?? 0) <=> ($g['stats'][$y['id']]['in'] ?? 0));
        return [
            'a' => $a, 'rows' => $slice($rows), 'posts' => $slice($posts), 'total' => count($list), 'pages' => $pages, 'perPage' => self::PER_PAGE, 'map' => SeoKeyword::query()->get()->keyBy('slug'),
            'linkRows' => $linkRows, 'stats' => $g['stats'], 'tabs' => self::TABS,
        ];
    }
}
