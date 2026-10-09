<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\AuditRequestResource\Pages;
use Illuminate\Database\Eloquent\Builder;

/** Free audit requests from /free-audit: leads with source "audit", in their own list. Same fields and follow-up as leads. */
class AuditRequestResource extends LeadResource
{
    protected static ?string $navigationIcon = 'heroicon-o-magnifying-glass-circle';
    protected static ?string $navigationLabel = 'Free audit requests';
    protected static ?string $modelLabel = 'free audit request';
    protected static ?string $slug = 'audit-requests';
    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('source', 'audit');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuditRequests::route('/'),
            'view' => Pages\ViewAuditRequest::route('/{record}'),
            'edit' => Pages\EditAuditRequest::route('/{record}/edit'),
        ];
    }
}
