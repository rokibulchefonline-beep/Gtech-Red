<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TestimonialResource\Pages;
use App\Filament\Support\ImageField;
use App\Filament\Support\Perms;
use App\Models\Testimonial;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TestimonialResource extends Resource
{
    use Perms;

    protected static string $section = 'structure';
    protected static ?string $model = Testimonial::class;
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationGroup = 'Site structure';
    protected static ?string $navigationLabel = 'Testimonials';
    protected static ?string $modelLabel = 'testimonial';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')->label('Headline')->required()->maxLength(160),
            Forms\Components\Textarea::make('text')->label('Quote')->required()->rows(4)->maxLength(1500),
            Forms\Components\TextInput::make('name')->label('Client name')->required()->maxLength(80),
            Forms\Components\Toggle::make('visible')->default(true),
        ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('sort')->reorderable('sort')
            ->description('Client quotes in the What Our Clients Say section.')
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->description(fn ($record) => $record->name),
                Tables\Columns\ToggleColumn::make('visible'),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTestimonial::route('/'),
            'create' => Pages\CreateTestimonial::route('/create'),
            'edit' => Pages\EditTestimonial::route('/{record}/edit'),
        ];
    }
}
