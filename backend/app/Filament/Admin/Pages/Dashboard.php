<?php

namespace App\Filament\Admin\Pages;

use App\Support\Publisher;
use Filament\Actions\Action;
use Filament\Pages\Dashboard as Base;

class Dashboard extends Base
{
    protected function getHeaderActions(): array
    {
        return [
            Action::make('site')->label('View website')->icon('heroicon-o-globe-alt')->color('gray')
                ->url(\App\Filament\Support\SiteLink::to('/'), shouldOpenInNewTab: true),
            // Before the switch to Blade, the Next.js site is rebuilt on Cloudflare to show changes.
            Action::make('publish')->label('Publish site')->icon('heroicon-o-rocket-launch')
                ->requiresConfirmation()->modalDescription('Rebuild the website so all saved changes go live. It takes a few minutes.')
                ->visible(fn () => ! config('gtech.blade_live') && (bool) auth()->user()?->hasPerm('content'))
                ->action(fn () => Publisher::publish()),
            // After it, saved changes show straight away; this only matters after editing the database directly.
            Action::make('refresh')->label('Refresh website')->icon('heroicon-o-arrow-path')->color('gray')
                ->requiresConfirmation()->modalDescription('Changes saved here already show on the website. Use this only if content was changed directly in the database: every page is rebuilt on its next visit.')
                ->visible(fn () => config('gtech.blade_live') && (bool) auth()->user()?->hasPerm('content'))
                ->action(function () { \App\Support\Site\PageCache::flush(); \Filament\Notifications\Notification::make()->title('Website refreshed')->success()->send(); }),
        ];
    }
}
