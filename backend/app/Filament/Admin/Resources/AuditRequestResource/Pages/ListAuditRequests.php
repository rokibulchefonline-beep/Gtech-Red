<?php

namespace App\Filament\Admin\Resources\AuditRequestResource\Pages;

use App\Filament\Admin\Resources\AuditRequestResource;
use Filament\Resources\Pages\ListRecords;

class ListAuditRequests extends ListRecords
{
    protected static string $resource = AuditRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [AuditRequestResource::exportAction()];
    }
}
