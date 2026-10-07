<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\RoleResource\Pages;
use App\Filament\Support\Perms;
use App\Models\Role;
use App\Support\Permissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/** Roles and what each one may do, as a tick-box grid per section. */
class RoleResource extends Resource
{
    use Perms;

    protected static string $section = 'users';
    protected static ?string $model = Role::class;
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?string $navigationLabel = 'Roles';
    protected static ?int $navigationSort = 3;

    public static function canCreate(): bool { return static::allows('manage'); }
    public static function canEdit(Model $record): bool { return static::allows('manage') && ! $record->isLocked(); }
    public static function canDelete(Model $record): bool { return static::allows('manage') && ! $record->isLocked() && ! $record->users()->exists(); }
    public static function canDeleteAny(): bool { return false; }

    public static function form(Form $form): Form
    {
        $grid = [];
        foreach (Permissions::SECTIONS as $key => $s) {
            $grid[] = Forms\Components\CheckboxList::make("grid.$key")->label($s['label'])->helperText($s['hint'] ?? null)
                ->options($s['actions'])->columns(count($s['actions']) > 3 ? 5 : 3)->gridDirection('row')->bulkToggleable();
        }
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\TextInput::make('name')->required()->maxLength(60),
                Forms\Components\TextInput::make('description')->maxLength(200)->placeholder('What this role is for'),
            ])->columns(2),
            Forms\Components\Section::make('What this role can do')
                ->description('Tick what people with this role may do. Changes apply on their next page load.')
                ->schema($grid),
        ]);
    }

    /** perms list -> grid of sections. */
    public static function beforeFill(array $data): array
    {
        $perms = (array) ($data['perms'] ?? []);
        foreach (array_keys(Permissions::SECTIONS) as $s) {
            $data['grid'][$s] = array_values(array_map(fn ($p) => substr($p, strlen($s) + 1), array_filter($perms, fn ($p) => str_starts_with($p, "$s."))));
        }
        return $data;
    }

    /** grid -> perms list; anything ticked also grants "view" for that section. */
    public static function beforeSave(array $data, ?Role $record = null): array
    {
        $perms = [];
        foreach ((array) ($data['grid'] ?? []) as $s => $actions) {
            if (! isset(Permissions::SECTIONS[$s]) || ! $actions) continue;
            $actions = array_intersect((array) $actions, array_keys(Permissions::SECTIONS[$s]['actions']));
            if ($actions) $actions[] = 'view';
            foreach (array_unique($actions) as $a) $perms[] = "$s.$a";
        }
        unset($data['grid']);
        $data['perms'] = array_values(array_intersect(Permissions::all(), $perms));
        if (! $record) $data['key'] = Str::slug($data['name'], '_').'_'.Str::lower(Str::random(4));
        return $data;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->description(fn (Role $r) => $r->description)->wrap(),
                Tables\Columns\TextColumn::make('users_count')->counts('users')->label('People'),
                Tables\Columns\TextColumn::make('perms')->label('Access')->visibleFrom('md')->wrap()
                    ->getStateUsing(fn (Role $r) => $r->isLocked() ? 'Everything' : (collect(Permissions::SECTIONS)->filter(fn ($s, $k) => in_array("$k.view", (array) $r->perms, true))->pluck('label')->implode(', ') ?: 'Nothing')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->iconButton()->tooltip('Edit'),
                Tables\Actions\Action::make('copy')->icon('heroicon-o-document-duplicate')->iconButton()->tooltip('Copy as a new role')->color('gray')
                    ->visible(fn () => static::allows('manage'))
                    ->action(function (Role $r) {
                        $n = Role::query()->create(['key' => Str::slug($r->name, '_').'_'.Str::lower(Str::random(4)), 'name' => $r->name.' (copy)', 'description' => $r->description, 'perms' => $r->isLocked() ? Permissions::all() : $r->perms]);
                        return redirect(static::getUrl('edit', ['record' => $n]));
                    }),
                Tables\Actions\DeleteAction::make()->iconButton()->tooltip('Delete (only roles nobody has)'),
            ])
            ->paginated(false);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListRoles::route('/'), 'create' => Pages\CreateRole::route('/create'), 'edit' => Pages\EditRole::route('/{record}/edit')];
    }
}
