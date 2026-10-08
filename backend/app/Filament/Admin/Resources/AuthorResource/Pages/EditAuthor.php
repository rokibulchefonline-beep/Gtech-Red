<?php

namespace App\Filament\Admin\Resources\AuthorResource\Pages;

use App\Filament\Admin\Resources\AuthorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAuthor extends EditRecord
{
    use \App\Filament\Support\HasSchemaPanel;

    protected static string $resource = AuthorResource::class;

    protected function schemaPath(): string
    {
        return '/blogs/author/'.$this->record->slug;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return $this->fillSchema($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['schema']);
        return $data;
    }

    protected function afterSave(): void
    {
        $this->saveSchema();
    }

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
