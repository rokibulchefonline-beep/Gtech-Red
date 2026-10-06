<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Support\ImageField;
use App\Models\Setting;
use App\Support\Publisher;
use App\Support\SiteMailer;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Crypt;

class Settings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?string $navigationLabel = 'Site settings';
    protected static ?int $navigationSort = 1;
    protected static string $view = 'filament.admin.pages.settings';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->hasPerm('settings');
    }

    public function mount(): void
    {
        $s = Setting::all_();
        $s['smtp']['pass'] = '';
        $s['smtp']['hasPass'] = (bool) (Setting::group('smtp')['pass'] ?? '');
        $this->form->fill($s);
    }

    public function form(Form $form): Form
    {
        return $form->statePath('data')->schema([
            Forms\Components\Tabs::make()->tabs([
                Forms\Components\Tabs\Tab::make('General')->schema([
                    Forms\Components\TextInput::make('general.siteName')->label('Site name')->required()->maxLength(80),
                    Forms\Components\TextInput::make('general.tagline')->maxLength(160),
                    Forms\Components\TextInput::make('general.siteUrl')->label('Website address')->url()->maxLength(200),
                ]),
                Forms\Components\Tabs\Tab::make('Contact')->schema([
                    Forms\Components\TextInput::make('contact.email')->email()->maxLength(160),
                    Forms\Components\TextInput::make('contact.phone')->maxLength(40),
                    Forms\Components\Textarea::make('contact.address')->rows(2)->maxLength(300),
                    Forms\Components\TextInput::make('contact.hours')->label('Opening hours')->maxLength(80),
                    Forms\Components\Repeater::make('socials')->label('Social profiles')->schema([
                        Forms\Components\Select::make('name')->options(array_combine($n = ['LinkedIn', 'Facebook', 'Instagram', 'X', 'YouTube', 'TikTok', 'Pinterest'], $n))->required(),
                        Forms\Components\TextInput::make('url')->url()->required()->maxLength(300),
                    ])->columns(2)->defaultItems(0),
                ]),
                Forms\Components\Tabs\Tab::make('Tracking')->schema([
                    Forms\Components\TextInput::make('tracking.gtmId')->label('Google Tag Manager ID')->placeholder('GTM-XXXXXXX')->maxLength(20),
                    Forms\Components\TextInput::make('tracking.ga4Id')->label('Google Analytics 4 ID')->placeholder('G-XXXXXXXXXX')->maxLength(20),
                    Forms\Components\TextInput::make('tracking.metaPixelId')->label('Meta Pixel ID')->maxLength(30),
                ]),
                Forms\Components\Tabs\Tab::make('SEO defaults')->schema([
                    Forms\Components\TextInput::make('seo.titleSuffix')->maxLength(60),
                    Forms\Components\Textarea::make('seo.defaultDescription')->rows(2)->maxLength(300),
                    ImageField::make('seo.ogImage', 'Default social sharing image'),
                ]),
                Forms\Components\Tabs\Tab::make('Email')->schema([
                    Forms\Components\Placeholder::make('smtphelp')->hiddenLabel()->content('Used for new-lead alerts and the automatic reply to people who fill in a form.'),
                    Forms\Components\TextInput::make('smtp.host')->label('SMTP host')->placeholder('smtp.gmail.com')->maxLength(120),
                    Forms\Components\TextInput::make('smtp.port')->numeric()->default(587),
                    Forms\Components\Toggle::make('smtp.secure')->label('Use SSL (port 465)'),
                    Forms\Components\TextInput::make('smtp.user')->label('Username')->maxLength(160),
                    Forms\Components\TextInput::make('smtp.pass')->label('Password')->password()->revealable()->maxLength(200)
                        ->helperText(fn ($get) => $get('smtp.hasPass') ? 'A password is saved. Leave empty to keep it.' : 'No password saved yet.'),
                    Forms\Components\Hidden::make('smtp.hasPass'),
                    Forms\Components\TextInput::make('smtp.fromName')->label('From name')->maxLength(80),
                    Forms\Components\TextInput::make('smtp.fromEmail')->label('From email')->email()->maxLength(160),
                    Forms\Components\TextInput::make('smtp.notifyTo')->label('Send lead alerts to')->email()->maxLength(160),
                    Forms\Components\Toggle::make('smtp.autoReply')->label('Send an automatic reply to the person who enquired'),
                ])->columns(2),
                Forms\Components\Tabs\Tab::make('Publishing')->schema([
                    Forms\Components\TextInput::make('publish.deployHook')->label('Cloudflare deploy hook URL')->url()->maxLength(500)
                        ->helperText('Cloudflare > Workers & Pages > your site > Settings > Builds > Deploy hooks. "Publish site" calls this URL.'),
                ]),
            ]),
        ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $oldPass = Setting::group('smtp')['pass'] ?? '';
        $data['smtp']['pass'] = filled($data['smtp']['pass'] ?? null) ? Crypt::encryptString($data['smtp']['pass']) : $oldPass;
        unset($data['smtp']['hasPass']);
        foreach (array_keys(Setting::DEFAULTS) as $group) {
            if (array_key_exists($group, $data)) Setting::put($group, array_is_list(Setting::DEFAULTS[$group]) ? array_values((array) $data[$group]) : (array) $data[$group]);
        }
        Notification::make()->title('Settings saved')->success()->send();
        $this->mount();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('test')->label('Send test email')->icon('heroicon-o-paper-airplane')->color('gray')
                ->form([Forms\Components\TextInput::make('to')->email()->required()->default(fn () => auth()->user()?->email)])
                ->action(function (array $data) {
                    try {
                        SiteMailer::send($data['to'], 'Test email from your website', '<p>Your email settings work.</p>');
                        Notification::make()->title('Test email sent')->success()->send();
                    } catch (\Throwable $e) {
                        Notification::make()->title('Email failed')->body($e->getMessage())->danger()->send();
                    }
                }),
            Action::make('publish')->label('Publish site')->icon('heroicon-o-rocket-launch')->requiresConfirmation()->action(fn () => Publisher::publish()),
        ];
    }
}
