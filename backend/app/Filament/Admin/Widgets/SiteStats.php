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
        $u = auth()->user();
        // Each number only for people allowed to see that section.
        return array_values(array_filter([
            $u?->hasPerm('leads.view') ? Stat::make('New leads', Lead::query()->visibleTo($u)->where('status', 'new')->count())->description(Lead::query()->visibleTo($u)->where('created_at', '>=', now()->subDays(7))->count().' in the last 7 days')->color('danger') : null,
            $u?->hasPerm('posts.view') ? Stat::make('Blog posts', Post::query()->where('status', 'published')->count())->description(Post::query()->where('status', 'draft')->count().' drafts') : null,
            $u?->hasPerm('case_studies.view') ? Stat::make('Case studies', CaseStudy::query()->where('status', 'published')->count())->description('published') : null,
            $u?->hasPerm('subscribers.view') ? Stat::make('Newsletter', Subscriber::query()->count())->description('subscribers') : null,
        ]));
    }
}
