<?php

namespace App\Filament\Admin\Resources\PageContentResource\Pages;

use App\Filament\Admin\Resources\PageContentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPageContent extends EditRecord
{
    protected static string $resource = PageContentResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return PageContentResource::beforeFill($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return PageContentResource::beforeSave($data, $this->record);
    }
}
