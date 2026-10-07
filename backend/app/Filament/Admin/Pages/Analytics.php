<?php

namespace App\Filament\Admin\Pages;

use App\Support\Analytics\Classifier;
use App\Support\Analytics\Report;
use Filament\Pages\Page;
use Livewire\Attributes\Url;

/** Website traffic: where visitors come from (search, AI assistants, social, referrals, campaigns) and what they read. */
class Analytics extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Analytics';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.admin.pages.analytics';

    /** Chart colours per channel (validated categorical palette; dark mode has its own steps). */
    public const COLOURS = [
        'Search' => ['#2a78d6', '#3987e5'], 'AI' => ['#eb6834', '#d95926'], 'Direct' => ['#1baf7a', '#199e70'], 'Social' => ['#eda100', '#c98500'],
        'Referral' => ['#e87ba4', '#d55181'], 'Paid' => ['#008300', '#008300'], 'Email' => ['#4a3aa7', '#9085e9'], 'Campaign' => ['#e34948', '#e66767'],
    ];

    #[Url] public string $period = '30';
    #[Url] public string $channel = '';
    #[Url] public string $source = '';
    #[Url] public string $page = '';

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->hasPerm('analytics');
    }

    public function filter(string $key, string $value): void
    {
        if (in_array($key, ['channel', 'source', 'page'], true)) $this->{$key} = $this->{$key} === $value ? '' : $value;
    }

    public function clear(): void
    {
        $this->channel = $this->source = $this->page = '';
    }

    protected function getViewData(): array
    {
        $days = in_array($this->period, ['1', '7', '30', '90', '365'], true) ? (int) $this->period : 30;
        $r = new Report($days, in_array($this->channel, Classifier::CHANNELS, true) ? $this->channel : '', mb_substr($this->source, 0, 60), mb_substr($this->page, 0, 300));
        return [
            'r' => $r, 'totals' => $r->totals(), 'series' => $r->series(), 'channels' => $r->channels(), 'sources' => $r->sources(),
            'ai' => $r->ai(), 'search' => $r->search(), 'social' => $r->social(), 'referrers' => $r->referrers(), 'links' => $r->referrerLinks(),
            'campaigns' => $r->campaigns(), 'pages' => $r->pages(), 'landing' => $r->landing(), 'devices' => $r->devices(),
            'browsers' => $r->browsers(), 'countries' => $r->countries(), 'aiBots' => $r->bots('ai'), 'searchBots' => $r->bots('search'), 'botPages' => $r->botPages(),
            'colours' => self::COLOURS,
        ];
    }
}
