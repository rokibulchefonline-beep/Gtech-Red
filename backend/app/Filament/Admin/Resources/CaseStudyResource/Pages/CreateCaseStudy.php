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

    /** The Schema markup panel is saved with the page's SEO override once the post exists. */
    protected function afterCreate(): void
    {
        \App\Filament\Support\SchemaPanel::save('/case-studies/'.$this->record->slug, $this->data['schema'] ?? null);
    }
}
