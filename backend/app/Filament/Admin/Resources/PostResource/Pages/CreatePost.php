<?php

namespace App\Filament\Admin\Resources\PostResource\Pages;

use App\Filament\Admin\Resources\PostResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return PostResource::beforeSave($data);
    }

    /** The Schema markup panel is saved with the page's SEO override once the post exists. */
    protected function afterCreate(): void
    {
        \App\Filament\Support\SchemaPanel::save('/blogs/'.$this->record->slug, $this->data['schema'] ?? null);
    }
}
