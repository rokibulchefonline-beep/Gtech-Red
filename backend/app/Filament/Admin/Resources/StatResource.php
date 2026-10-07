<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\StatResource\Pages;
use App\Filament\Support\ImageField;
use App\Filament\Support\Perms;
use App\Models\Stat;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StatResource extends Resource
{
    use Perms;

    protected static string $section = 'structure';
    protected static ?string $model = Stat::class;
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'Site structure';
    protected static ?string $navigationLabel = 'Company numbers';
    protected static ?string $modelLabel = 'number';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('value')->numeric()->required(),
            Forms\Components\TextInput::make('suffix')->placeholder('+ or /5')->maxLength(10),
            Forms\Components\TextInput::make('label')->required()->maxLength(80),
            Forms\Components\Select::make('decimals')->options([0 => 'Whole number', 1 => 'One decimal (4.9)'])->default(0),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('sort')->reorderable('sort')
            ->description('The numbers bar on the home page hero (years, projects, clients, industries, rating).')
            ->columns([
                Tables\Columns\TextColumn::make('value')->formatStateUsing(fn ($state, $record) => rtrim(rtrim(number_format((float) $state, $record->decimals), '0'), '.').$record->suffix),
                Tables\Columns\TextColumn::make('label'),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStat::route('/'),
            'create' => Pages\CreateStat::route('/create'),
            'edit' => Pages\EditStat::route('/{record}/edit'),
        ];
    }
}
