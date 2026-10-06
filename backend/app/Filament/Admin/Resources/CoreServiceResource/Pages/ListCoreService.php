<?php

namespace App\Filament\Admin\Resources\CoreServiceResource\Pages;

use App\Filament\Admin\Resources\CoreServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCoreService extends ListRecords
{
    protected static string $resource = CoreServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
