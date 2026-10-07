<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ContactResource\Pages;
use App\Models\Contact;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * People who have enquired, one row per email address with all their enquiries. Data protection requests start
 * here: download everything held about someone, or erase it.
 */
class ContactResource extends Resource
{
    protected static ?string $model = Contact::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Leads';
    protected static ?string $navigationLabel = 'Contacts';
    protected static ?int $navigationSort = 3;
    protected static ?string $recordTitleAttribute = 'name';

    /** Contacts span every lead, so they need "See everyone's leads". */
    public static function canViewAny(): bool { return (bool) auth()->user()?->hasPerm('leads.all'); }
    public static function canCreate(): bool { return false; }
    public static function canEdit($record): bool { return false; }
    public static function canDelete($record): bool { return false; }
    public static function canDeleteAny(): bool { return false; }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email', 'business', 'phone'];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make()->schema([
                Infolists\Components\TextEntry::make('name'),
                Infolists\Components\TextEntry::make('business')->placeholder('-'),
                Infolists\Components\TextEntry::make('email')->url(fn (Contact $r) => 'mailto:'.$r->email)->copyable(),
                Infolists\Components\TextEntry::make('phone')->placeholder('-'),
                Infolists\Components\TextEntry::make('first_seen_at')->label('First enquiry')->dateTime('j M Y'),
                Infolists\Components\TextEntry::make('last_seen_at')->label('Latest enquiry')->dateTime('j M Y')->helperText(fn (Contact $r) => $r->last_seen_at?->diffForHumans()),
                Infolists\Components\TextEntry::make('newsletter')->label('Newsletter')->state(fn (Contact $r) => $r->subscriber() ? 'Subscribed '.$r->subscriber()->created_at?->format('j M Y') : 'Not subscribed'),
            ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('last_seen_at', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount('leads')->withSum('leads', 'value'))
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(['name', 'email', 'business', 'phone'])->sortable()->description(fn (Contact $r) => $r->business),
                Tables\Columns\TextColumn::make('email')->visibleFrom('md'),
                Tables\Columns\TextColumn::make('leads_count')->label('Enquiries')->sortable()->badge()->color(fn (int $state) => $state > 1 ? 'warning' : 'gray'),
                Tables\Columns\TextColumn::make('leads_sum_value')->label('Deal value')->money('GBP')->placeholder('-')->sortable()->visibleFrom('lg'),
                Tables\Columns\TextColumn::make('last_seen_at')->label('Latest enquiry')->since()->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('repeat')->label('More than one enquiry')->toggle()->query(fn (Builder $query) => $query->has('leads', '>', 1)),
            ])
            ->actions([Tables\Actions\ViewAction::make()->label('Open')]);
    }

    public static function getRelations(): array
    {
        return [ContactResource\RelationManagers\LeadsRelationManager::class];
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListContacts::route('/'), 'view' => Pages\ViewContact::route('/{record}')];
    }
}
