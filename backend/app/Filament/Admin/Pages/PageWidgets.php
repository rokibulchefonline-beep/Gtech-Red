<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Support\PageBlocks;
use App\Filament\Support\WidgetCatalog;
use App\Models\Page as SitePage;
use Filament\Pages\Page;

/** Information about every page builder widget: what it is for, its fields, tips, an example and where it is used. */
class PageWidgets extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-squares-plus';
    protected static ?string $navigationGroup = 'Website content';
    protected static ?string $navigationLabel = 'Page builder widgets';
    protected static ?string $title = 'Page builder widgets';
    protected static ?string $slug = 'page-widgets';
    protected static ?int $navigationSort = 9;
    protected static string $view = 'filament.admin.pages.page-widgets';

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->hasPerm('pages.view');
    }

    /** Widgets by group, each with its usage across the site's pages. */
    public function getGroups(): array
    {
        $usage = [];
        foreach (SitePage::query()->get(['key', 'name', 'sections']) as $p) {
            foreach ((array) $p->sections as $s) {
                $type = $s['type'] ?? '';
                if (! isset(PageBlocks::LABELS[$type])) continue;
                $usage[$type]['count'] = ($usage[$type]['count'] ?? 0) + 1;
                $usage[$type]['pages'][$p->name] = true;
            }
        }
        $catalog = WidgetCatalog::all();
        $groups = [];
        foreach (WidgetCatalog::GROUPS as $g) {
            foreach ($catalog as $key => $w) {
                if ($w['group'] !== $g) continue;
                $u = $usage[$key] ?? ['count' => 0, 'pages' => []];
                $groups[$g][] = $w + ['key' => $key, 'used' => $u['count'], 'pageNames' => array_slice(array_keys($u['pages']), 0, 5), 'pageTotal' => count($u['pages'])];
            }
        }
        return $groups;
    }

    public function getTotals(): array
    {
        return ['widgets' => count(WidgetCatalog::all()), 'pages' => SitePage::query()->count(), 'sections' => SitePage::query()->get()->sum(fn ($p) => count((array) $p->sections))];
    }
}
