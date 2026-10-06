<?php

namespace App\Filament\Admin\Resources\RedirectResource\Pages;

use App\Filament\Admin\Resources\RedirectResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRedirects extends ListRecords
{
    protected static string $resource = RedirectResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()->label('Add redirect')];
    }
}
