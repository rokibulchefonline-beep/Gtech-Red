<?php

namespace App\Filament\Admin\Resources\CaseStudyResource\Pages;

use App\Filament\Admin\Resources\CaseStudyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCaseStudy extends EditRecord
{
    protected static string $resource = CaseStudyResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return CaseStudyResource::beforeFill($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return CaseStudyResource::beforeSave($data, $this->record);
    }
}
