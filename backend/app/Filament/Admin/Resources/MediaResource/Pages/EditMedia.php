<?php

namespace App\Filament\Admin\Resources\MediaResource\Pages;

use App\Filament\Admin\Resources\MediaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMedia extends EditRecord
{
    protected static string $resource = MediaResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return MediaResource::beforeFill($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return MediaResource::beforeSave($data, $this->record);
    }
}
