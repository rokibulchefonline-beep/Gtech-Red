<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PartnerResource\Pages;
use App\Filament\Support\HooksDefault;
use App\Filament\Support\ImageField;
use App\Filament\Support\Perms;
use App\Models\Partner;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class PartnerResource extends Resource
{
    use HooksDefault, Perms;

    protected static string $perm = 'content';
    protected static ?string $model = Partner::class;
    protected static ?string $navigationIcon = 'heroicon-o-check-badge';
    protected static ?string $navigationGroup = 'Website content';
    protected static ?string $navigationLabel = 'Partner badges';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Placeholder::make('where')->label('Where these appear')->content(new HtmlString('Home: Platform Partners strip and the first 4 in Who We Are. About and Contact: partner strip.'))->columnSpanFull(),
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
            ->description(new HtmlString('Home: Platform Partners strip and the first 4 in Who We Are. About and Contact: partner strip.'))
            ->columns([
                Tables\Columns\ImageColumn::make('logo')->getStateUsing(fn (Partner $r) => $r->logo ? ImageField::preview($r->logo) : null)->height(36),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\ToggleColumn::make('visible')->label('Shown'),
                Tables\Columns\TextColumn::make('order')->sortable(),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPartner::route('/'),
            'create' => Pages\CreatePartner::route('/create'),
            'edit' => Pages\EditPartner::route('/{record}/edit'),
        ];
    }
}
