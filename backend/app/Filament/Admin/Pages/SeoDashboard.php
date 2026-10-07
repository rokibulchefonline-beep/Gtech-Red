<?php

namespace App\Filament\Admin\Pages;

use App\Models\Page as SitePage;
use App\Models\Redirect;
use App\Models\SeoEntry;
use App\Support\Analytics\Report;
use App\Support\Seo\Audit;
use Filament\Actions\Action;
use Filament\Pages\Page;

/** One screen for search health: audit scores, the commonest problems, weakest pages, search and AI traffic. */
class SeoDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static ?string $navigationGroup = 'SEO';
    protected static ?string $navigationLabel = 'SEO dashboard';
    protected static ?string $title = 'SEO dashboard';
    protected static ?string $slug = 'seo';
    protected static ?int $navigationSort = 1;
    protected static string $view = 'filament.admin.pages.seo.dashboard';

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->hasPerm('seo.view');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('rerun')->label('Run the audit again')->icon('heroicon-o-arrow-path')->color('gray')->action(fn () => Audit::site(true)),
            Action::make('audit')->label('Full audit')->icon('heroicon-o-clipboard-document-check')->url(SeoAudit::getUrl()),
        ];
    }

    public static function tone(int $n): string
    {
        return $n >= 85 ? 'success' : ($n >= 65 ? 'warning' : 'danger');
    }

    protected function getViewData(): array
    {
        $a = Audit::site();
        // The problems that appear on most pages, with how many pages have them.
        $issues = [];
        foreach ($a['pages'] as $p) foreach ($p['checks'] as $c) {
            if ($c['level'] === 'pass') continue;
            $k = $c['id'];
            $issues[$k] ??= ['label' => preg_replace('/\s*\(.*\)$/', '', $c['label']), 'group' => $c['group'], 'fix' => $c['fix'], 'fail' => 0, 'warn' => 0];
            $issues[$k][$c['level']]++;
        }
        uasort($issues, fn ($x, $y) => ($y['fail'] * 2 + $y['warn']) <=> ($x['fail'] * 2 + $x['warn']));
        $pages = $a['pages'];
        usort($pages, fn ($x, $y) => $x['scores']['total'] <=> $y['scores']['total']);
        $posts = $a['posts'];
        usort($posts, fn ($x, $y) => $x['score'] <=> $y['score']);

        $r = new Report(30);
        $channels = collect($r->channels())->keyBy('k');
        return [
            'a' => $a, 's' => $a['summary'], 'issues' => array_slice($issues, 0, 8, true), 'weakest' => array_slice($pages, 0, 6), 'posts' => array_slice($posts, 0, 5),
            'traffic' => ['search' => (int) ($channels['Search']['visits'] ?? 0), 'ai' => (int) ($channels['AI']['visits'] ?? 0), 'leads' => (int) ($channels['Search']['leads'] ?? 0) + (int) ($channels['AI']['leads'] ?? 0)],
            'search' => array_slice($r->search(), 0, 5), 'ai' => array_slice($r->ai(), 0, 5), 'aiBots' => array_slice($r->bots('ai'), 0, 6),
            'health' => [
                'live' => SitePage::query()->where('published', true)->count() + count($a['posts']),
                'noindex' => SeoEntry::query()->where('noindex', true)->count(),
                'overrides' => SeoEntry::query()->count(),
                'redirects' => Redirect::query()->count(),
                'redirectHits' => (int) Redirect::query()->sum('hits'),
                'missingDesc' => SitePage::query()->where('published', true)->where('meta_description', '')->count()
                    + \App\Models\Post::query()->whereIn('status', ['published', 'scheduled'])->where('meta_description', '')->where('excerpt', '')->count(),
            ],
        ];
    }
}
