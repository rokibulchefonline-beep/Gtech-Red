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
            Action::make('site')->label('View website')->icon('heroicon-o-globe-alt')->color('gray')->url(config('gtech.site_url'), shouldOpenInNewTab: true),
            Action::make('publish')->label('Publish site')->icon('heroicon-o-rocket-launch')
                ->requiresConfirmation()->modalDescription('Rebuild the website so all saved changes go live. It takes a few minutes.')
                ->visible(fn () => (bool) auth()->user()?->hasPerm('content'))
                ->action(fn () => Publisher::publish()),
        ];
    }
}
