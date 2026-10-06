<?php

namespace App\Filament\Admin\Resources\ServiceGroupResource\Pages;

use App\Filament\Admin\Resources\ServiceGroupResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListServiceGroup extends ListRecords
{
    protected static string $resource = ServiceGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
