<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\IndustryResource\Pages;
use App\Filament\Support\ImageField;
use App\Filament\Support\Perms;
use App\Models\Industry;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class IndustryResource extends Resource
{
    use Perms;

    protected static string $perm = 'content';
    protected static ?string $model = Industry::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationGroup = 'Site structure';
    protected static ?string $navigationLabel = 'Industries list';
    protected static ?string $modelLabel = 'industry';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required()->maxLength(120),
            Forms\Components\TextInput::make('slug')->label('Page address')->prefix('/industries/')->required()->maxLength(80)->unique(ignoreRecord: true)->rule('regex:/^[a-z0-9-]+$/')->disabledOn('edit'),
            Forms\Components\TextInput::make('icon')->maxLength(80),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('sort')->reorderable('sort')
            ->description('Industries shown in the menu, footer and Industries page. Each links to its page under /industries/.')
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->description(fn ($record) => '/industries/'.$record->slug),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIndustry::route('/'),
            'create' => Pages\CreateIndustry::route('/create'),
            'edit' => Pages\EditIndustry::route('/{record}/edit'),
        ];
    }
}
