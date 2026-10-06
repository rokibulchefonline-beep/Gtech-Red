<?php

namespace App\Filament\Admin\Resources\LeadResource\Pages;

use App\Filament\Admin\Resources\LeadResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLead extends ListRecords
{
    protected static string $resource = LeadResource::class;

    protected function getHeaderActions(): array
    {
        return [\App\Filament\Admin\Resources\LeadResource::exportAction()];
    }
}
