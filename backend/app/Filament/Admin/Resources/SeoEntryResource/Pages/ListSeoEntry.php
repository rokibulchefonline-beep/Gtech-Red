<?php

namespace App\Filament\Admin\Resources\SeoEntryResource\Pages;

use App\Filament\Admin\Resources\SeoEntryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSeoEntry extends ListRecords
{
    protected static string $resource = SeoEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make(),];
    }
}
