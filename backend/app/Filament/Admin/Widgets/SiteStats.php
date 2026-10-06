<?php

namespace App\Filament\Admin\Widgets;

use App\Models\CaseStudy;
use App\Models\Lead;
use App\Models\Post;
use App\Models\Subscriber;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SiteStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('New leads', Lead::query()->where('status', 'new')->count())->description(Lead::query()->where('created_at', '>=', now()->subDays(7))->count().' in the last 7 days')->color('danger'),
            Stat::make('Blog posts', Post::query()->where('status', 'published')->count())->description(Post::query()->where('status', 'draft')->count().' drafts'),
            Stat::make('Case studies', CaseStudy::query()->where('status', 'published')->count())->description('published'),
            Stat::make('Newsletter', Subscriber::query()->count())->description('subscribers'),
        ];
    }
}
