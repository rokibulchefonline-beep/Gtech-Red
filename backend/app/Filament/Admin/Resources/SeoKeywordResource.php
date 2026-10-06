<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SeoKeywordResource\Pages;
use App\Filament\Support\ImageField;
use App\Filament\Support\Perms;
use App\Models\SeoKeyword;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SeoKeywordResource extends Resource
{
    use Perms;

    protected static string $perm = 'content';
    protected static ?string $model = SeoKeyword::class;
    protected static ?string $navigationIcon = 'heroicon-o-key';
    protected static ?string $navigationGroup = 'Site structure';
    protected static ?string $navigationLabel = 'Keyword map';
    protected static ?string $modelLabel = 'keyword entry';
    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('slug')->label('Page slug')->required()->maxLength(80)->disabledOn('edit'),
            Forms\Components\TextInput::make('kw')->label('Target keyword')->required()->maxLength(160),
            Forms\Components\TagsInput::make('sec')->label('Related keywords'),
            Forms\Components\TagsInput::make('ent')->label('Entities (brands, tools, concepts)'),
            Forms\Components\Repeater::make('links')->label('Contextual internal links')->schema([
                Forms\Components\TextInput::make('target')->label('Page slug')->required()->maxLength(80),
                Forms\Components\TextInput::make('anchor')->label('Link text')->required()->maxLength(120),
                Forms\Components\TextInput::make('why')->label('Short reason')->maxLength(160),
            ])->columns(3)->defaultItems(0)->reorderable(),
        ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('slug')
            ->description('Advanced SEO: target keyword, related keywords, entities and contextual internal links for each service and industry page.')
            ->columns([
                Tables\Columns\TextColumn::make('slug')->searchable(),
                Tables\Columns\TextColumn::make('kw')->label('Target keyword')->searchable(),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSeoKeyword::route('/'),
            'create' => Pages\CreateSeoKeyword::route('/create'),
            'edit' => Pages\EditSeoKeyword::route('/{record}/edit'),
        ];
    }
}
