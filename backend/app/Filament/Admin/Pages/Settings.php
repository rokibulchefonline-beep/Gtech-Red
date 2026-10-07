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
        return (bool) auth()->user()?->hasPerm('settings.view');
    }

    public function mount(): void
    {
        $s = Setting::all_();
        $s['smtp']['pass'] = '';
        $s['smtp']['hasPass'] = (bool) (Setting::group('smtp')['pass'] ?? '');
        $s['forms']['turnstileSecret'] = '';
        $s['forms']['hasSecret'] = (bool) (Setting::group('forms')['turnstileSecret'] ?? '');
        $this->form->fill($s);
    }

    public function form(Form $form): Form
    {
        return $form->statePath('data')->disabled(fn () => ! self::canEdit())->schema([
            Forms\Components\Tabs::make()->tabs([
                Forms\Components\Tabs\Tab::make('General')->schema([
                    Forms\Components\TextInput::make('general.siteName')->label('Site name')->required()->maxLength(80),
                    Forms\Components\TextInput::make('general.tagline')->maxLength(160),
                    Forms\Components\TextInput::make('general.siteUrl')->label('Website address')->url()->maxLength(200),
                ]),
                Forms\Components\Tabs\Tab::make('Contact')->schema([
                    Forms\Components\TextInput::make('contact.email')->label('Email')->email()->maxLength(160),
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('contact.phone')->label('Main phone')->tel()->maxLength(40)->helperText('Shown first, and used in Google\'s business details.'),
                        Forms\Components\TextInput::make('contact.phone2')->label('Second phone (optional)')->tel()->maxLength(40),
                    ]),
                    Forms\Components\TextInput::make('contact.hours')->label('Opening hours')->maxLength(80),
                    Forms\Components\Fieldset::make('Google Business Profile address')->columns(2)->schema([
                        Forms\Components\Placeholder::make('gbp_help')->hiddenLabel()->columnSpanFull()
                            ->content('Copy the address exactly as it is on your Google Business Profile. The same name, address and phone on the website and the profile helps you show in Google Maps and local results.'),
                        Forms\Components\TextInput::make('contact.street')->label('Street address')->maxLength(160)->columnSpanFull()->placeholder('e.g. Unit 5, 10 High Street'),
                        Forms\Components\TextInput::make('contact.city')->label('Town / city')->maxLength(80),
                        Forms\Components\TextInput::make('contact.region')->label('County (optional)')->maxLength(80),
                        Forms\Components\TextInput::make('contact.postcode')->label('Postcode')->maxLength(12),
                        Forms\Components\Select::make('contact.country')->label('Country')->options(['GB' => 'United Kingdom'])->default('GB')->selectablePlaceholder(false),
                        Forms\Components\TextInput::make('contact.mapsUrl')->label('Google Maps link to your profile')->url()->maxLength(500)->columnSpanFull()
                            ->placeholder('https://maps.app.goo.gl/... or https://www.google.com/maps?cid=...')
                            ->helperText('In Google Maps open your business, click Share and copy the link. Adds a "Get directions" link and connects the website to the profile.'),
                        Forms\Components\Toggle::make('contact.showAddress')->label('Show the address on the contact page')->default(true),
                        Forms\Components\Toggle::make('contact.showAddressFooter')->label('Show it in the footer too'),
                    ]),
                    Forms\Components\Repeater::make('socials')->label('Social profiles')->schema([
                        Forms\Components\Select::make('name')->options(array_combine($n = ['LinkedIn', 'Facebook', 'Instagram', 'X', 'YouTube', 'TikTok', 'Pinterest'], $n))->required(),
                        Forms\Components\TextInput::make('url')->url()->required()->maxLength(300),
                    ])->columns(2)->defaultItems(0),
                ]),
                Forms\Components\Tabs\Tab::make('Tracking')->schema([
                    Forms\Components\TextInput::make('tracking.gtmId')->label('Google Tag Manager ID')->placeholder('GTM-XXXXXXX')->maxLength(20)
                        ->regex('/^GTM-[A-Z0-9]+$/')->validationMessages(['regex' => 'Use the container ID only, like GTM-NRPJVVSH (not the whole code).'])
                        ->helperText('Only the ID, not the code. The website adds both Google Tag Manager snippets to every page itself: the script high in the <head> (just after Google Consent Mode) and the <noscript> part straight after <body>. Leave empty to remove it.'),
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
                Forms\Components\Tabs\Tab::make('Leads')->schema([
                    Forms\Components\Radio::make('leads.autoAssign')->label('Assign new enquiries')
                        ->options(['off' => 'No, someone assigns them by hand', 'round_robin' => 'Automatically, taking turns between the people below'])->default('off')->live(),
                    Forms\Components\CheckboxList::make('leads.assignees')->label('Take turns between')->columns(2)
                        ->options(fn () => \App\Filament\Admin\Resources\LeadResource::ownerOptions())
                        ->visible(fn ($get) => $get('leads.autoAssign') === 'round_robin')
                        ->helperText('Each person gets an email when a lead is assigned to them. Deactivated users are skipped.'),
                    Forms\Components\TextInput::make('leads.webhook')->label('Chat alert webhook (optional)')->url()->maxLength(500)
                        ->rule(fn () => fn ($attr, $v, $fail) => $v && ! \App\Support\Crm\LeadRouting::validWebhook((string) $v) ? $fail('Use the https:// webhook address from Slack, Google Chat or Microsoft Teams.') : null)
                        ->helperText('Posts every new lead to a Slack, Google Chat or Microsoft Teams channel. Create an "incoming webhook" in that app and paste its address here.'),
                    Forms\Components\Toggle::make('leads.reminders')->label('Email each person their due follow-ups every weekday morning'),
                    Forms\Components\Repeater::make('pipeline.stages')->label('Pipeline stages')->reorderable()->grid(3)->addActionLabel('Add a stage')
                        ->schema([
                            Forms\Components\Hidden::make('key'),
                            Forms\Components\TextInput::make('label')->hiddenLabel()->required()->maxLength(30),
                        ])
                        ->deleteAction(fn ($action) => $action->hidden(fn (array $arguments, Forms\Components\Repeater $component) => in_array($component->getState()[$arguments['item']]['key'] ?? '', array_keys(\App\Models\Lead::FIXED), true)))
                        ->helperText('Rename, add, remove or reorder the stages of the Pipeline board. New, Won and Lost always exist (New first, Won and Lost last). A stage that still has leads cannot be removed.'),
                    Forms\Components\Select::make('leads.retainMonths')->label('Delete lost leads automatically')
                        ->options([0 => 'Never', 6 => 'After 6 months', 12 => 'After 12 months', 24 => 'After 2 years', 36 => 'After 3 years'])->selectablePlaceholder(false)
                        ->helperText('Data protection: lost leads untouched for this long are deleted every night, with their timeline. Leads in any other stage are kept.'),
                ]),
                Forms\Components\Tabs\Tab::make('Forms')->schema([
                    Forms\Components\Textarea::make('forms.privacyNotice')->label('Privacy notice under every form')->rows(2)->maxLength(500)
                        ->helperText('Tells people how their details are used (UK GDPR). Write [link text](/privacy-policy) for a link. It is saved with every lead. Leave empty to hide it.'),
                    Forms\Components\Fieldset::make('Spam protection: Cloudflare Turnstile (optional)')->schema([
                        Forms\Components\Placeholder::make('tshelp')->hiddenLabel()->columnSpanFull()
                            ->content('A free "I am human" check from Cloudflare, usually invisible to people. Create a widget for your domain in Cloudflare > Turnstile and paste its two keys here. Both keys are needed; remove the site key to turn it off.'),
                        Forms\Components\TextInput::make('forms.turnstileSite')->label('Site key')->maxLength(100),
                        Forms\Components\TextInput::make('forms.turnstileSecret')->label('Secret key')->password()->revealable()->maxLength(200)
                            ->helperText(fn ($get) => $get('forms.hasSecret') ? 'A secret key is saved. Leave empty to keep it.' : 'No secret key saved yet.'),
                        Forms\Components\Hidden::make('forms.hasSecret'),
                    ])->columns(2),
                ]),
                Forms\Components\Tabs\Tab::make('Security')->schema([
                    Forms\Components\Radio::make('security.require2fa')->label('Require two-factor sign-in')
                        ->options(['off' => 'No, people choose for themselves (in My account)', 'managers' => 'For people who can manage users (recommended at least)', 'everyone' => 'For everyone'])
                        ->helperText('With two-factor sign-in, people also enter a 6-digit code from an authenticator app (Google Authenticator, Microsoft Authenticator, 1Password...). Anyone required to use it is asked to set it up at their next sign-in.')
                        ->default('off'),
                ]),
                Forms\Components\Tabs\Tab::make('Publishing')->visible(fn () => ! config('gtech.blade_live'))->schema([
                    Forms\Components\TextInput::make('publish.deployHook')->label('Cloudflare deploy hook URL')->url()->maxLength(500)
                        ->helperText('Cloudflare > Workers & Pages > your site > Settings > Builds > Deploy hooks. "Publish site" calls this URL.'),
                ]),
            ]),
        ]);
    }

    /** Pipeline stages from the form: keys for new ones, New/Won/Lost kept in place. Null (and a message) when a removed stage still has leads. */
    private static function stages(array $rows): ?array
    {
        $out = [];
        foreach (array_values($rows) as $r) {
            $label = trim((string) ($r['label'] ?? ''));
            if ($label === '') continue;
            $key = ($r['key'] ?? '') ?: \Illuminate\Support\Str::slug($label, '_');
            while (isset($out[$key]) || ($key !== ($r['key'] ?? '') && isset(\App\Models\Lead::FIXED[$key]))) $key .= '_2';
            $out[$key] = ['key' => $key, 'label' => $label];
        }
        foreach (\App\Models\Lead::FIXED as $k => $l) $out[$k] ??= ['key' => $k, 'label' => $l];
        $ordered = [$out['new'], ...array_values(array_diff_key($out, \App\Models\Lead::FIXED)), $out['won'], $out['lost']];
        $removed = \App\Models\Lead::query()->whereNotIn('status', array_column($ordered, 'key'))->pluck('status')->unique();
        if ($removed->isNotEmpty()) {
            Notification::make()->title('Not saved')->body('Leads are still in the stage '.$removed->map(fn ($k) => '"'.(\App\Models\Lead::statuses()[$k] ?? $k).'"')->implode(', ').'. Move them first.')->danger()->send();
            return null;
        }
        return $ordered;
    }

    public static function canEdit(): bool
    {
        return (bool) auth()->user()?->hasPerm('settings.edit');
    }

    public function save(): void
    {
        abort_unless(self::canEdit(), 403);
        $data = $this->form->getState();
        $oldPass = Setting::group('smtp')['pass'] ?? '';
        $data['smtp']['pass'] = filled($data['smtp']['pass'] ?? null) ? Crypt::encryptString($data['smtp']['pass']) : $oldPass;
        unset($data['smtp']['hasPass']);
        $oldSecret = Setting::group('forms')['turnstileSecret'] ?? '';
        $data['forms']['turnstileSecret'] = filled($data['forms']['turnstileSecret'] ?? null) ? Crypt::encryptString(trim($data['forms']['turnstileSecret'])) : $oldSecret;
        $data['forms']['budgets'] = Setting::group('forms')['budgets'] ?? [];
        unset($data['forms']['hasSecret']);
        if (($stages = self::stages((array) ($data['pipeline']['stages'] ?? []))) === null) return;
        $data['pipeline']['stages'] = $stages;
        foreach (array_keys(Setting::DEFAULTS) as $group) {
            if (array_key_exists($group, $data)) Setting::put($group, array_is_list(Setting::DEFAULTS[$group]) ? array_values((array) $data[$group]) : (array) $data[$group]);
        }
        Notification::make()->title('Settings saved')->success()->send();
        $this->mount();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('test')->label('Send test email')->icon('heroicon-o-paper-airplane')->color('gray')->visible(fn () => self::canEdit())
                ->form([Forms\Components\TextInput::make('to')->email()->required()->default(fn () => auth()->user()?->email)])
                ->action(function (array $data) {
                    try {
                        SiteMailer::send($data['to'], 'Test email from your website', '<p>Your email settings work.</p>');
                        Notification::make()->title('Test email sent')->success()->send();
                    } catch (\Throwable $e) {
                        Notification::make()->title('Email failed')->body($e->getMessage())->danger()->send();
                    }
                }),
            Action::make('publish')->label('Publish site')->icon('heroicon-o-rocket-launch')->requiresConfirmation()
                ->visible(fn () => ! config('gtech.blade_live') && (bool) auth()->user()?->hasPerm('pages.edit'))->action(fn () => Publisher::publish()),
        ];
    }
}
