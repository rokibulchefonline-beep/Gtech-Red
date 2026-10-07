<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Support\Perms;
use App\Filament\Admin\Resources\UserResource\Pages;
use App\Filament\Support\HooksDefault;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserResource extends Resource
{
    use HooksDefault, Perms;

    protected static string $section = 'users';

    public static function canCreate(): bool { return static::allows('manage'); }
    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool { return static::allows('manage'); }
    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool { return static::allows('manage') && ! $record->is(auth()->user()); }
    public static function canDeleteAny(): bool { return false; }
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?string $navigationLabel = 'Users and roles';
    protected static ?int $navigationSort = 2;


    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required()->maxLength(80),
            Forms\Components\TextInput::make('email')->email()->required()->maxLength(160)->unique(ignoreRecord: true),
            Forms\Components\Select::make('role')->options(fn () => \App\Models\Role::options())->required()->default('editor')->live()
                ->helperText(fn (Get $get) => \App\Models\Role::query()->where('key', $get('role'))->value('description').' Roles are set up in Settings > Roles.'),
            Forms\Components\Toggle::make('active')->default(true)->helperText('Inactive users cannot sign in.'),
            Forms\Components\Toggle::make('invite')->label('Email an invitation')->default(true)->live()->dehydrated(false)
                ->visible(fn (string $operation) => $operation === 'create')
                ->helperText('They get a link to set their own password, so you never share one.'),
            Forms\Components\TextInput::make('password')->password()->revealable()->minLength(10)
                ->rule('regex:/^(?=.*[A-Za-z])(?=.*\d).+$/')->validationMessages(['regex' => 'Use letters and numbers.'])
                ->visible(fn (string $operation, Get $get) => $operation === 'edit' || ! $get('invite'))
                ->required(fn (string $operation, Get $get) => $operation === 'create' && ! $get('invite'))->dehydrated(fn ($state) => filled($state))
                ->helperText(fn (string $operation) => $operation === 'edit' ? 'Leave empty to keep the current password.' : 'At least 10 characters with letters and numbers.'),
            Forms\Components\Placeholder::make('history')->label('Recent sign-ins')->columnSpanFull()
                ->visible(fn (string $operation) => $operation === 'edit')
                ->content(fn (?User $record) => $record ? self::history($record) : ''),
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
        elseif (! $record) $data['password'] = Hash::make(\Illuminate\Support\Str::random(40)); // invited: they choose their own
        return $data;
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->searchable()->description(fn (User $r) => $r->email),
            Tables\Columns\TextColumn::make('role')->badge()->formatStateUsing(fn ($state) => \App\Models\Role::options()[$state] ?? $state),
            Tables\Columns\TextColumn::make('last_login_at')->label('Last sign-in')->since()->placeholder(fn (User $r) => $r->invited_at ? 'Invited, not signed in yet' : 'Never')->visibleFrom('md'),
            Tables\Columns\IconColumn::make('active')->boolean(),
            Tables\Columns\TextColumn::make('created_at')->label('Added')->date('d M Y')->visibleFrom('md'),
        ])->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\Action::make('reinvite')->label('Resend invitation')->icon('heroicon-o-envelope')->color('gray')
                ->visible(fn (User $r) => ! $r->last_login_at && self::canEdit($r))->requiresConfirmation()
                ->action(fn (User $r) => self::invite($r)),
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

    /** Email a "set your password" invitation. */
    public static function invite(User $user): void
    {
        $token = \Illuminate\Support\Facades\Password::broker(\Filament\Facades\Filament::getAuthPasswordBroker())->createToken($user);
        try {
            $user->notify(new \App\Notifications\InviteUser($token, (string) auth()->user()?->name));
            $user->forceFill(['invited_at' => now()])->save();
            \Filament\Notifications\Notification::make()->title('Invitation sent to '.$user->email)->success()->send();
        } catch (\Throwable $e) {
            report($e);
            \Filament\Notifications\Notification::make()->title('The invitation email could not be sent')->body('Check Site settings > Email. You can resend it from the users list.')->danger()->send();
        }
    }

    /** The last sign-ins and failed attempts for this user. */
    public static function history(User $user): \Illuminate\Support\HtmlString
    {
        $rows = \Illuminate\Support\Facades\DB::table('login_events')->where('email', $user->email)->orderByDesc('at')->limit(8)->get();
        if ($rows->isEmpty()) return new \Illuminate\Support\HtmlString('<span class="text-gray-500">No sign-ins yet.</span>');
        $labels = ['signed_in' => 'Signed in', 'failed' => 'Wrong password', 'locked' => 'Locked out', 'two_factor_failed' => 'Wrong 2FA code'];
        return new \Illuminate\Support\HtmlString('<ul class="space-y-1 text-sm">'.$rows->map(fn ($r) => '<li><span class="'.($r->event === 'signed_in' ? '' : 'text-danger-600 ').'font-medium">'.e($labels[$r->event] ?? $r->event).'</span> · '
            .e(\Illuminate\Support\Carbon::parse($r->at)->format('j M Y, H:i')).' · '.e($r->ip).' · <span class="text-gray-500">'.e(\App\Support\Analytics\Classifier::browser($r->user_agent)).' on '.e(self::os($r->user_agent)).'</span></li>')->implode('').'</ul>');
    }

    private static function os(string $ua): string
    {
        foreach (['iPhone' => 'iPhone', 'iPad' => 'iPad', 'Android' => 'Android', 'Mac OS X' => 'Mac', 'Windows' => 'Windows', 'Linux' => 'Linux'] as $k => $v) if (str_contains($ua, $k)) return $v;
        return 'unknown device';
    }
}
