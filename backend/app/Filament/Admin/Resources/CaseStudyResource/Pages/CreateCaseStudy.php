<?php

namespace App\Filament\Admin\Resources\CaseStudyResource\Pages;

use App\Filament\Admin\Resources\CaseStudyResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCaseStudy extends CreateRecord
{
    protected static string $resource = CaseStudyResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return CaseStudyResource::beforeSave($data);
    }
}
