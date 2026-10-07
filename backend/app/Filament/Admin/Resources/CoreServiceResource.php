<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CoreServiceResource\Pages;
use App\Filament\Support\ImageField;
use App\Filament\Support\Perms;
use App\Models\CoreService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CoreServiceResource extends Resource
{
    use Perms;

    protected static string $section = 'structure';
    protected static ?string $model = CoreService::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Site structure';
    protected static ?string $navigationLabel = 'Home service cards';
    protected static ?string $modelLabel = 'home service card';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')->required()->maxLength(120),
            Forms\Components\TextInput::make('slug')->label('Links to')->prefix('/services/')->required()->maxLength(80),
            Forms\Components\TextInput::make('line')->label('One-line description')->maxLength(300),
            Forms\Components\TagsInput::make('points')->placeholder('Add a point and press Enter'),
            ImageField::make('image', 'Image'),
        ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('sort')->reorderable('sort')
            ->description('The large service cards on the home page (Digital Marketing, Web and Software Services).')
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable(),
                Tables\Columns\TextColumn::make('line')->limit(60),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCoreService::route('/'),
            'create' => Pages\CreateCoreService::route('/create'),
            'edit' => Pages\EditCoreService::route('/{record}/edit'),
        ];
    }
}
