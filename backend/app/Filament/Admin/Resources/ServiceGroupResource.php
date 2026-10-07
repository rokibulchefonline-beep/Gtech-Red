<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ServiceGroupResource\Pages;
use App\Filament\Support\ImageField;
use App\Filament\Support\Perms;
use App\Models\ServiceGroup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ServiceGroupResource extends Resource
{
    use Perms;

    protected static string $section = 'structure';
    protected static ?string $model = ServiceGroup::class;
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $navigationGroup = 'Site structure';
    protected static ?string $navigationLabel = 'Services menu';
    protected static ?string $modelLabel = 'service category';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')->required()->maxLength(120),
            Forms\Components\TextInput::make('slug')->label('Page address')->prefix('/services/')->required()->maxLength(80)->unique(ignoreRecord: true)->rule('regex:/^[a-z0-9-]+$/')->disabledOn('edit'),
            Forms\Components\Textarea::make('intro')->rows(2)->maxLength(400),
            Forms\Components\TextInput::make('icon')->helperText('Icon name, e.g. lucide:search. Leave as it is unless you know the icon set.')->maxLength(80),
            Forms\Components\Repeater::make('items')->label('Services in this category')->relationship('items')->orderColumn('sort')->collapsible()->collapsed()
                ->itemLabel(fn (array $state) => $state['name'] ?? null)
                ->schema([
                    Forms\Components\TextInput::make('name')->required()->maxLength(120),
                    Forms\Components\TextInput::make('slug')->label('Page address')->prefix('/services/')->required()->maxLength(80)->rule('regex:/^[a-z0-9-]+$/'),
                    Forms\Components\TextInput::make('blurb')->label('Short description')->maxLength(300),
                    Forms\Components\TextInput::make('icon')->maxLength(80),
                ])->columns(2),
        ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('sort')->reorderable('sort')
            ->description('The service categories and services in the menu, footer, Services page and links. Each service links to its page under /services/.')
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->description(fn ($record) => '/services/'.$record->slug),
                Tables\Columns\TextColumn::make('items_count')->counts('items')->label('Services'),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServiceGroup::route('/'),
            'create' => Pages\CreateServiceGroup::route('/create'),
            'edit' => Pages\EditServiceGroup::route('/{record}/edit'),
        ];
    }
}
