<?php

namespace App\Filament\Admin\Pages;

use Filament\Actions\Action;
use Filament\Pages\Dashboard as Base;

class Dashboard extends Base
{
    protected function getHeaderActions(): array
    {
        return [
            Action::make('site')->label('View website')->icon('heroicon-o-globe-alt')->color('gray')
                ->url(\App\Filament\Support\SiteLink::to('/'), shouldOpenInNewTab: true),
            // Saved changes show straight away; this only matters after editing the database directly.
            Action::make('refresh')->label('Refresh website')->icon('heroicon-o-arrow-path')->color('gray')
                ->requiresConfirmation()->modalDescription('Changes saved here already show on the website. Use this only if content was changed directly in the database: every page is rebuilt on its next visit.')
                ->visible(fn () => (bool) auth()->user()?->hasPerm('pages.edit'))
                ->action(function () { \App\Support\Site\PageCache::flush(); \Filament\Notifications\Notification::make()->title('Website refreshed')->success()->send(); }),
        ];
    }
}
