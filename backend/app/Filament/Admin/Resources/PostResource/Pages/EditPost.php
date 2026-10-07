<?php

namespace App\Filament\Admin\Resources\PostResource\Pages;

use App\Filament\Admin\Resources\PostResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPost extends EditRecord
{
    use \App\Filament\Support\HasSchemaPanel;

    protected function schemaPath(): string
    {
        return '/blogs/'.$this->record->slug;
    }

    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('history')->label('Version history')->icon('heroicon-o-clock')->color('gray')
                ->url(fn () => \App\Filament\Admin\Pages\VersionHistory::urlFor($this->record)),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return $this->fillSchema(PostResource::beforeFill($data));
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return PostResource::beforeSave($data, $this->record);
    }

    protected function afterSave(): void
    {
        $this->saveSchema();
    }
}
