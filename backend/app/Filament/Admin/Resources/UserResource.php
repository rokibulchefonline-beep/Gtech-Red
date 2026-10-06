<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UserResource\Pages;
use App\Filament\Support\HooksDefault;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserResource extends Resource
{
    use HooksDefault;

    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?string $navigationLabel = 'Users and roles';
    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool { return (bool) auth()->user()?->hasPerm('users'); }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required()->maxLength(80),
            Forms\Components\TextInput::make('email')->email()->required()->maxLength(160)->unique(ignoreRecord: true),
            Forms\Components\Select::make('role')->options(User::roleOptions())->required()->default('editor')
                ->helperText('Super admin: everything. Admin: content, leads, settings. Editor: content. Sales: leads.'),
            Forms\Components\Toggle::make('active')->default(true)->helperText('Inactive users cannot sign in.'),
            Forms\Components\TextInput::make('password')->password()->revealable()->minLength(10)
                ->rule('regex:/^(?=.*[A-Za-z])(?=.*\d).+$/')->validationMessages(['regex' => 'Use letters and numbers.'])
                ->required(fn (string $operation) => $operation === 'create')->dehydrated(fn ($state) => filled($state))
                ->helperText(fn (string $operation) => $operation === 'edit' ? 'Leave empty to keep the current password.' : 'At least 10 characters with letters and numbers.'),
        ])->columns(2);
    }

    public static function beforeSave(array $data, $record = null): array
    {
        $me = auth()->user();
        if ($record && $record->is($me) && (($data['role'] ?? $record->role) !== $record->role || empty($data['active']))) {
            throw ValidationException::withMessages(['data.role' => 'You cannot change your own role or deactivate yourself.']);
        }
        $leavingSuper = $record && $record->role === 'super_admin' && (($data['role'] ?? '') !== 'super_admin' || empty($data['active']));
        if ($leavingSuper && User::query()->where('role', 'super_admin')->where('active', true)->count() <= 1) {
            throw ValidationException::withMessages(['data.role' => 'There must be at least one active super admin.']);
        }
        if (! empty($data['password'])) $data['password'] = Hash::make($data['password']);
        return $data;
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->searchable()->description(fn (User $r) => $r->email),
            Tables\Columns\TextColumn::make('role')->badge()->formatStateUsing(fn ($state) => User::ROLES[$state]['label'] ?? $state),
            Tables\Columns\IconColumn::make('active')->boolean(),
            Tables\Columns\TextColumn::make('created_at')->label('Added')->date('d M Y')->visibleFrom('md'),
        ])->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make()->hidden(fn (User $r) => $r->is(auth()->user()))
                ->before(function (User $r, Tables\Actions\DeleteAction $action) {
                    if ($r->role === 'super_admin' && User::query()->where('role', 'super_admin')->where('active', true)->count() <= 1) $action->cancel();
                }),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUser::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
