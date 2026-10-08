<?php

namespace App\Filament\Admin\Resources\SiteIconResource\Pages;

use App\Filament\Admin\Resources\SiteIconResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSiteIcons extends ListRecords
{
    protected static string $resource = SiteIconResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Upload icon')->icon('heroicon-o-arrow-up-tray')];
    }
}
