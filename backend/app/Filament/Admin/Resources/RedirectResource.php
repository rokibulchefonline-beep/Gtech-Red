<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\RedirectResource\Pages;
use App\Filament\Support\Perms;
use App\Models\Redirect;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/** Old addresses that forward to new ones. Changing a post or case study address adds one automatically. */
class RedirectResource extends Resource
{
    use Perms;

    protected static string $perm = 'content';
    protected static ?string $model = Redirect::class;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-uturn-right';
    protected static ?string $navigationGroup = 'Website content';
    protected static ?string $navigationLabel = 'Redirects';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('from_path')->label('Old address')->required()->maxLength(300)->prefix(fn () => rtrim(config('gtech.public_url'), '/'))
                ->placeholder('/blog/old-post-name')->helperText('Only used when this address no longer exists, so it can never hide a live page.')
                ->dehydrateStateUsing(fn (?string $state) => Redirect::normalise((string) $state))
                ->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('to_path')->label('New address')->required()->maxLength(500)
                ->placeholder('/blogs/new-post-name or https://…')->helperText('A page on this site (starting with /) or a full address.')
                ->rule('regex:#^(/|https?://)#')->validationMessages(['regex' => 'Start with / or https://']),
            Forms\Components\Select::make('status_code')->label('Type')->options([301 => 'Permanent (301), the usual choice', 302 => 'Temporary (302)'])->default(301)->required(),
        ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('from_path')->label('Old address')->searchable()->wrap(),
                Tables\Columns\TextColumn::make('to_path')->label('Goes to')->searchable()->wrap()->url(fn (Redirect $r) => $r->to_path, true),
                Tables\Columns\TextColumn::make('status_code')->label('Type')->badge()->formatStateUsing(fn (int $state) => $state === 301 ? 'Permanent' : 'Temporary')->visibleFrom('md'),
                Tables\Columns\IconColumn::make('automatic')->label('Auto')->boolean()->tooltip('Added when an address was changed')->visibleFrom('md'),
                Tables\Columns\TextColumn::make('hits')->label('Visits')->sortable()->description(fn (Redirect $r) => $r->last_hit_at ? 'last '.$r->last_hit_at->diffForHumans() : null)->visibleFrom('md'),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()])
            ->emptyStateHeading('No redirects yet')
            ->emptyStateDescription('Changing the address of a blog post or case study adds one here automatically. You can also add your own, e.g. for old pages.');
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListRedirects::route('/'), 'create' => Pages\CreateRedirect::route('/create'), 'edit' => Pages\EditRedirect::route('/{record}/edit')];
    }
}
