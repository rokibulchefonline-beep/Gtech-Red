<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\EmailTemplateResource\Pages;
use App\Models\EmailTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

/** Saved emails for replying to leads ("Send email" on a lead). Placeholders such as {first_name} are filled in. */
class EmailTemplateResource extends Resource
{
    protected static ?string $model = EmailTemplate::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Leads';
    protected static ?string $navigationLabel = 'Email templates';
    protected static ?int $navigationSort = 4;

    private static function allowed(): bool { return (bool) auth()->user()?->hasPerm('leads.templates'); }
    public static function canViewAny(): bool { return self::allowed(); }
    public static function canCreate(): bool { return self::allowed(); }
    public static function canEdit($record): bool { return self::allowed(); }
    public static function canDelete($record): bool { return self::allowed(); }
    public static function canDeleteAny(): bool { return self::allowed(); }

    public static function form(Form $form): Form
    {
        $help = collect(EmailTemplate::PLACEHOLDERS)->map(fn ($d, $k) => '<code>'.e($k).'</code> '.e($d))->implode('<br>');
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\TextInput::make('name')->label('Template name')->required()->maxLength(80)->placeholder('e.g. First reply'),
                Forms\Components\TextInput::make('subject')->required()->maxLength(200),
                Forms\Components\RichEditor::make('body')->required()->toolbarButtons(['bold', 'italic', 'underline', 'link', 'bulletList', 'orderedList', 'undo', 'redo']),
            ])->columnSpan(2),
            Forms\Components\Section::make('Placeholders')->schema([
                Forms\Components\Placeholder::make('ph')->hiddenLabel()->content(new HtmlString('<div style="font-size:13px;line-height:1.9">'.$help.'</div>')),
            ])->columnSpan(1),
        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table->defaultSort('name')->columns([
            Tables\Columns\TextColumn::make('name')->searchable()->description(fn (EmailTemplate $r) => $r->subject),
            Tables\Columns\TextColumn::make('updated_at')->label('Last change')->since()->visibleFrom('md'),
        ])->actions([Tables\Actions\EditAction::make()->iconButton(), Tables\Actions\ReplicateAction::make()->iconButton()->tooltip('Copy')
            ->mutateRecordDataUsing(fn (array $data) => ['name' => $data['name'].' (copy)'] + $data), Tables\Actions\DeleteAction::make()->iconButton()]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListEmailTemplates::route('/'), 'create' => Pages\CreateEmailTemplate::route('/create'), 'edit' => Pages\EditEmailTemplate::route('/{record}/edit')];
    }
}
