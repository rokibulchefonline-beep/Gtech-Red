<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Support\Csv;
use App\Support\Analytics\Classifier;
use App\Support\Analytics\Report;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Livewire\Attributes\Url;

/**
 * Website traffic: live visitors, headline numbers against the previous period, a trend chart, channels and
 * devices, a conversion funnel, the busiest days and hours, and where visitors come from (search, AI
 * assistants, social, referrals, campaigns) and what they read.
 */
class Analytics extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Analytics';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.admin.pages.analytics';

    /** Chart colours per channel (validated categorical palette; dark mode has its own steps). */
    public const COLOURS = [
        'Search' => ['#2a78d6', '#3987e5'], 'AI' => ['#eb6834', '#d95926'], 'Direct' => ['#1baf7a', '#199e70'], 'Social' => ['#eda100', '#c98500'],
        'Referral' => ['#e87ba4', '#d55181'], 'Paid' => ['#008300', '#2e9a2e'], 'Email' => ['#4a3aa7', '#9085e9'], 'Campaign' => ['#e34948', '#e66767'],
    ];

    public const METRICS = ['visits' => 'Visits', 'people' => 'Visitors', 'views' => 'Page views', 'leads' => 'Leads'];
    public const TABS = ['acquisition' => 'Acquisition', 'content' => 'Content', 'ai' => 'AI & search bots', 'audience' => 'Audience'];

    #[Url] public string $period = '30';
    #[Url] public string $channel = '';
    #[Url] public string $source = '';
    #[Url] public string $page = '';
    #[Url] public string $metric = 'visits';
    #[Url] public bool $compare = true;
    #[Url] public string $tab = 'acquisition';
    #[Url] public bool $stacked = false;

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->hasPerm('analytics.view');
    }

    public function filter(string $key, string $value): void
    {
        if (in_array($key, ['channel', 'source', 'page'], true)) $this->{$key} = $this->{$key} === $value ? '' : $value;
    }

    public function clear(): void
    {
        $this->channel = $this->source = $this->page = '';
    }

    private function report(): Report
    {
        $days = in_array($this->period, ['1', '7', '30', '90', '365'], true) ? (int) $this->period : 30;
        return new Report($days, in_array($this->channel, Classifier::CHANNELS, true) ? $this->channel : '', mb_substr($this->source, 0, 60), mb_substr($this->page, 0, 300));
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')->label('Export CSV')->icon('heroicon-o-arrow-down-tray')->color('gray')
                ->action(function () {
                    $r = $this->report();
                    $rows = [];
                    foreach ($r->daily()['points'] as $day => $m) $rows[] = ['Trend', $day, $m['visits'], $m['people'], $m['views'], $m['leads']];
                    foreach ($r->channels() as $c) $rows[] = ['Channel', $c['k'], $c['visits'], $c['people'], '', $c['leads']];
                    foreach ($r->sources() as $c) $rows[] = ['Source', $c['k'], $c['visits'], $c['people'], '', $c['leads']];
                    foreach ($r->pages() as $p) $rows[] = ['Page', $p['k'], $p['entries'], $p['people'], $p['views'], $p['leads']];
                    return Csv::download('analytics-'.$r->from->format('Y-m-d').'-to-'.$r->to->format('Y-m-d').'.csv', ['Report', 'Day / name', 'Visits', 'Visitors', 'Page views', 'Leads'], $rows);
                }),
        ];
    }

    /** % change from the previous period, or null when there is nothing to compare with. */
    public static function change(int|float $now, int|float $before): ?float
    {
        if ($before == 0) return $now == 0 ? 0.0 : null;
        return round(($now - $before) / $before * 100, 1);
    }

    protected function getViewData(): array
    {
        $r = $this->report();
        $prev = $r->previous();
        $metric = isset(self::METRICS[$this->metric]) ? $this->metric : 'visits';
        $data = [
            'r' => $r, 'totals' => $r->totals(), 'before' => $prev->totals(), 'daily' => $r->daily(), 'prevDaily' => $this->compare ? $prev->daily() : null, 'metric' => $metric,
            'series' => $this->stacked ? $r->series() : null, 'channels' => $r->channels(), 'devices' => $r->devices(), 'funnel' => $r->funnel(),
            'newReturning' => $r->newReturning(), 'heatmap' => $r->heatmap(), 'live' => Report::live(), 'colours' => self::COLOURS, 'tabs' => self::TABS,
        ];
        return $data + match ($this->tab) {
            'content' => ['pages' => $r->pages(), 'landing' => $r->landing()],
            'ai' => ['ai' => $r->ai(), 'aiBots' => $r->bots('ai'), 'searchBots' => $r->bots('search'), 'botPages' => $r->botPages()],
            'audience' => ['browsers' => $r->browsers(), 'countries' => $r->countries()],
            default => ['sources' => $r->sources(), 'search' => $r->search(), 'social' => $r->social(), 'referrers' => $r->referrers(), 'links' => $r->referrerLinks(), 'campaigns' => $r->campaigns()],
        };
    }
}
