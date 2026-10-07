<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ClientResource\Pages;
use App\Filament\Support\HooksDefault;
use App\Filament\Support\ImageField;
use App\Filament\Support\Perms;
use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class ClientResource extends Resource
{
    use HooksDefault, Perms;

    protected static string $section = 'structure';
    protected static ?string $model = Client::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationGroup = 'Website content';
    protected static ?string $navigationLabel = 'Client logos';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Placeholder::make('where')->label('Where these appear')->content(new HtmlString('Home: the two scrolling Industry Leading Brands rows. Service pages: the first 6 in the Trusted by row.'))->columnSpanFull(),
            Forms\Components\TextInput::make('name')->required()->maxLength(80),
            ImageField::make('logo', 'Logo'),
            Forms\Components\TextInput::make('url')->label('Link (optional)')->url()->maxLength(500),
            Forms\Components\TextInput::make('order')->numeric()->default(100)->helperText('Lower numbers show first.'),
            Forms\Components\Toggle::make('visible')->label('Show on the website')->default(true),
        ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('order')
            ->reorderable('order')
            ->description(new HtmlString('Home: the two scrolling Industry Leading Brands rows. Service pages: the first 6 in the Trusted by row.'))
            ->columns([
                Tables\Columns\ImageColumn::make('logo')->getStateUsing(fn (Client $r) => $r->logo ? ImageField::preview($r->logo) : null)->height(36),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\ToggleColumn::make('visible')->label('Shown'),
                Tables\Columns\TextColumn::make('order')->sortable(),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClient::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }
}
