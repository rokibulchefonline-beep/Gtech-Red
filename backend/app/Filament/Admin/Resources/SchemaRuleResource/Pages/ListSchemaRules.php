<?php

namespace App\Filament\Admin\Resources\SchemaRuleResource\Pages;

use App\Filament\Admin\Resources\SchemaRuleResource;
use Filament\Resources\Pages\ListRecords;

class ListSchemaRules extends ListRecords
{
    protected static string $resource = SchemaRuleResource::class;
    protected function getHeaderActions(): array { return [\Filament\Actions\CreateAction::make()->label('Add schema')]; }
}
