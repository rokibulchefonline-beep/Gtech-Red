<?php

namespace App\Filament\Admin\Resources\LeadResource\Pages;

use App\Filament\Admin\Resources\LeadResource;
use Filament\Resources\Pages\ViewRecord;

/** Read-only lead, for roles that can see leads but not update them. */
class ViewLead extends ViewRecord
{
    protected static string $resource = LeadResource::class;

    public function getTitle(): string
    {
        return 'Lead: '.$this->record->name;
    }
}
