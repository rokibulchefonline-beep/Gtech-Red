<?php

namespace App\Filament\Admin\Resources\LeadResource\Pages;

use App\Filament\Admin\Resources\LeadResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListLead extends ListRecords
{
    protected static string $resource = LeadResource::class;

    protected function getHeaderActions(): array
    {
        return [\App\Filament\Admin\Resources\LeadResource::exportAction()];
    }

    /** Free audit requests have their own list. */
    protected function getTableQuery(): ?Builder
    {
        return static::getResource()::getEloquentQuery()->where(fn (Builder $q) => $q->where('source', '!=', 'audit')->orWhereNull('source'));
    }
}
