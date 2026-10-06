<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SubscriberResource\Pages;
use App\Filament\Support\Csv;
use App\Filament\Support\HooksDefault;
use App\Models\Subscriber;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SubscriberResource extends Resource
{
    use HooksDefault;

    protected static ?string $model = Subscriber::class;
    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static ?string $navigationGroup = 'Leads';
    protected static ?string $navigationLabel = 'Newsletter';
    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool { return (bool) auth()->user()?->hasPerm('leads'); }

    public static function canCreate(): bool { return false; }

    public static function form(Form $form): Form
    {
        return $form->schema([Forms\Components\TextInput::make('email')->email()->required()->maxLength(160)]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('created_at', 'desc')->columns([
            Tables\Columns\TextColumn::make('email')->searchable()->copyable(),
            Tables\Columns\TextColumn::make('source')->visibleFrom('md'),
            Tables\Columns\TextColumn::make('created_at')->label('Joined')->dateTime('d M Y')->sortable(),
        ])->actions([Tables\Actions\DeleteAction::make()])->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function exportAction(): Action
    {
        return Action::make('export')->label('Export CSV')->icon('heroicon-o-arrow-down-tray')->color('gray')
            ->action(fn () => Csv::download('newsletter-'.now()->format('Y-m-d').'.csv', ['Email', 'Source', 'Joined'],
                Subscriber::query()->latest()->cursor()->map(fn ($s) => [$s->email, $s->source, $s->created_at?->format('Y-m-d')])));
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListSubscriber::route('/'), 'edit' => Pages\EditSubscriber::route('/{record}/edit')];
    }
}
