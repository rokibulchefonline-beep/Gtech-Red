<?php

namespace App\Filament\Admin\Resources\IndustryResource\Pages;

use App\Filament\Admin\Resources\IndustryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListIndustry extends ListRecords
{
    protected static string $resource = IndustryResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
