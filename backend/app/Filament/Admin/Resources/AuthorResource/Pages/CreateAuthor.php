<?php

namespace App\Filament\Admin\Resources\AuthorResource\Pages;

use App\Filament\Admin\Resources\AuthorResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAuthor extends CreateRecord
{
    protected static string $resource = AuthorResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        unset($data['schema']);
        return $data;
    }

    protected function afterCreate(): void
    {
        \App\Filament\Support\SchemaPanel::save('/blogs/author/'.$this->record->slug, $this->data['schema'] ?? null);
    }

}
