<?php

namespace App\Filament\Admin\Resources\SeoKeywordResource\Pages;

use App\Filament\Admin\Resources\SeoKeywordResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSeoKeyword extends ListRecords
{
    protected static string $resource = SeoKeywordResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
