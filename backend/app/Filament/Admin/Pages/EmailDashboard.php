<?php

namespace App\Filament\Admin\Pages;

use App\Models\Email;
use App\Models\EmailTemplate;
use App\Models\Lead;
use App\Support\Mail\Inbox;
use App\Support\SiteMailer;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

/**
 * Email > Email dashboard: an inbox-style view of the site's email. Sent holds every email the website sends
 * (lead replies, alerts, password resets); Inbox shows received mail when IMAP is set up; Drafts and Trash work
 * as in any mail app. Write, reply and forward from here, with the email templates.
 */
class EmailDashboard extends Page implements HasForms
{
    use InteractsWithForms, WithPagination;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static ?string $navigationGroup = 'Email';
    protected static ?string $navigationLabel = 'Email dashboard';
    protected static ?string $title = 'Email';
    protected static ?string $slug = 'email';
    protected static ?int $navigationSort = 1;
    protected static string $view = 'filament.admin.pages.email-dashboard';

    #[Url]
    public string $folder = 'sent';

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'open')]
    public ?int $openId = null;

    public bool $starredOnly = false;
    public bool $composing = false;
    public ?array $compose = [];

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->hasPerm('email.view');
    }

    public static function getNavigationBadge(): ?string
    {
        if (! auth()->user()?->hasPerm('email.view')) return null;
        $n = Email::query()->visibleTo(auth()->user())->where('folder', 'inbox')->whereNull('read_at')->count();
        return $n ? (string) $n : null;
    }

    public function mount(): void
    {
        if (! isset(Email::FOLDERS[$this->folder])) $this->folder = 'sent';
        if (Inbox::enabled() && ! request()->has('folder')) $this->folder = 'inbox';
        $this->composeForm->fill();
        // ?to=someone@example.com&lead=12 opens a new email (used by the leads list).
        if ($to = request()->query('to')) $this->write(['to' => (string) $to, 'lead_id' => request()->integer('lead') ?: Email::leadFor(Email::addresses((string) $to))]);
    }

    protected function getForms(): array
    {
        return ['composeForm'];
    }

    private function canSend(): bool
    {
        return (bool) auth()->user()?->hasPerm('email.send');
    }

    public function composeForm(Form $form): Form
    {
        return $form->statePath('compose')->schema([
            Forms\Components\Hidden::make('draft_id'),
            Forms\Components\Grid::make(3)->schema([
                Forms\Components\TextInput::make('to')->label('To')->required()->columnSpan(2)->prefixIcon('heroicon-o-user')
                    ->placeholder('name@example.com, another@example.com')
                    ->rule(fn () => fn ($a, $v, $fail) => ! Email::addresses((string) $v) ? $fail('Add at least one valid email address.') : null),
                Forms\Components\Select::make('lead_id')->label('Lead (optional)')->searchable()->placeholder('Not linked')
                    ->getSearchResultsUsing(fn (string $q) => Lead::query()->visibleTo(auth()->user())->where(fn ($w) => $w->where('name', 'like', "%$q%")->orWhere('email', 'like', "%$q%"))->limit(20)->get()->mapWithKeys(fn (Lead $l) => [$l->id => "$l->name <$l->email>"])->all())
                    ->getOptionLabelUsing(fn ($v) => ($l = Lead::query()->find($v)) ? "$l->name <$l->email>" : null)
                    ->live()->afterStateUpdated(function ($state, Get $get, Set $set) {
                        if ($state && blank($get('to')) && ($l = Lead::query()->find($state))) $set('to', $l->email);
                    })->helperText('Logs the email on the lead\'s timeline.'),
            ]),
            Forms\Components\TextInput::make('cc')->label('Cc')->placeholder('Optional')
                ->rule(fn () => fn ($a, $v, $fail) => filled($v) && ! Email::addresses((string) $v) ? $fail('Use valid email addresses.') : null),
            Forms\Components\Grid::make(3)->schema([
                Forms\Components\TextInput::make('subject')->required()->maxLength(250)->columnSpan(2),
                Forms\Components\Select::make('template')->label('Template')->placeholder('None')->dehydrated(false)->live()
                    ->options(fn () => EmailTemplate::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->afterStateUpdated(function (?string $state, Get $get, Set $set) {
                        $t = $state ? EmailTemplate::query()->find($state) : null;
                        if (! $t) return;
                        $lead = ($get('lead_id') ? Lead::query()->find($get('lead_id')) : null) ?? new Lead(['name' => '', 'business' => '', 'service' => '']);
                        $set('subject', EmailTemplate::render($t->subject, $lead, auth()->user(), html: false));
                        $set('body', EmailTemplate::render($t->body, $lead, auth()->user()));
                        $set('template', null);
                    }),
            ]),
            Forms\Components\RichEditor::make('body')->hiddenLabel()->required()
                ->toolbarButtons(['bold', 'italic', 'underline', 'strike', 'link', 'bulletList', 'orderedList', 'blockquote', 'h2', 'h3', 'undo', 'redo'])
                ->extraInputAttributes(['style' => 'min-height:16rem']),
            Forms\Components\Toggle::make('copy')->label('Send me a copy'),
        ]);
    }

    // ---------- list ----------

    private function base(): Builder
    {
        return Email::query()->visibleTo(auth()->user());
    }

    public function getEmailsProperty()
    {
        $q = $this->base()->with('lead');
        if ($this->starredOnly) $q->where('starred', true)->where('folder', '!=', 'trash');
        else $q->where('folder', $this->folder);
        if (($s = trim($this->search)) !== '') {
            $q->where(fn ($w) => $w->where('subject', 'like', "%$s%")->orWhere('to', 'like', "%$s%")->orWhere('from_email', 'like', "%$s%")
                ->orWhere('from_name', 'like', "%$s%")->orWhere('body', 'like', "%$s%"));
        }
        return $q->orderByDesc('sent_at')->orderByDesc('id')->paginate(25);
    }

    public function getCountsProperty(): array
    {
        $rows = $this->base()->selectRaw('folder, count(*) as n, sum(case when read_at is null then 1 else 0 end) as unread')->groupBy('folder')->get()->keyBy('folder');
        $out = [];
        foreach (array_keys(Email::FOLDERS) as $f) $out[$f] = ['n' => (int) ($rows[$f]->n ?? 0), 'unread' => (int) ($rows[$f]->unread ?? 0)];
        $out['starred'] = ['n' => $this->base()->where('starred', true)->where('folder', '!=', 'trash')->count(), 'unread' => 0];
        $out['failed'] = $this->base()->where('folder', 'sent')->where('status', 'failed')->count();
        return $out;
    }

    public function getOpenProperty(): ?Email
    {
        return $this->openId ? $this->base()->with(['lead', 'user'])->find($this->openId) : null;
    }

    public function setFolder(string $f): void
    {
        $this->starredOnly = $f === 'starred';
        if (isset(Email::FOLDERS[$f])) $this->folder = $f;
        $this->openId = null;
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function open(int $id): void
    {
        $e = $this->base()->find($id);
        if (! $e) return;
        if ($e->folder === 'draft') { $this->editDraft($e); return; }
        if (! $e->read_at) $e->forceFill(['read_at' => now()])->save();
        $this->openId = $id;
    }

    public function closeOpen(): void
    {
        $this->openId = null;
    }

    // ---------- actions on one email ----------

    private function mine(int $id): ?Email
    {
        $e = $this->base()->find($id);
        return $e && auth()->user()?->hasPerm('email.view') ? $e : null;
    }

    public function toggleStar(int $id): void
    {
        if ($e = $this->mine($id)) $e->forceFill(['starred' => ! $e->starred])->save();
    }

    public function markUnread(int $id): void
    {
        if ($e = $this->mine($id)) { $e->forceFill(['read_at' => null])->save(); $this->openId = null; }
    }

    public function trash(int $id): void
    {
        if (! auth()->user()?->hasPerm('email.delete') && $this->mine($id)?->user_id !== auth()->id()) return;
        if ($e = $this->mine($id)) { $e->moveToTrash(); $this->openId = null; Notification::make()->title('Moved to Trash')->success()->send(); }
    }

    public function restore(int $id): void
    {
        if ($e = $this->mine($id)) { $e->restore(); $this->openId = null; Notification::make()->title('Restored to '.Email::FOLDERS[$e->folder])->success()->send(); }
    }

    public function deleteForever(int $id): void
    {
        if (! auth()->user()?->hasPerm('email.delete')) return;
        if (($e = $this->mine($id)) && $e->folder === 'trash') { $e->delete(); $this->openId = null; Notification::make()->title('Deleted for good')->success()->send(); }
    }

    public function emptyTrash(): void
    {
        if (! auth()->user()?->hasPerm('email.delete')) return;
        $n = $this->base()->where('folder', 'trash')->delete();
        $this->openId = null;
        Notification::make()->title($n.' '.Str::plural('email', $n).' deleted for good')->success()->send();
    }

    public function retry(int $id): void
    {
        $e = $this->mine($id);
        if (! $e || $e->status !== 'failed' || ! $this->canSend()) return;
        $this->write(['to' => $e->to, 'cc' => $e->cc, 'subject' => $e->subject, 'body' => $e->body, 'lead_id' => $e->lead_id]);
    }

    // ---------- writing ----------

    public function write(array $fill = []): void
    {
        if (! $this->canSend()) return;
        $this->composeForm->fill($fill + ['to' => '', 'cc' => '', 'subject' => '', 'body' => '', 'lead_id' => null, 'copy' => false, 'draft_id' => null]);
        $this->composing = true;
    }

    public function reply(int $id, bool $all = false): void
    {
        $e = $this->mine($id);
        if (! $e) return;
        $incoming = $e->folder === 'inbox' || $e->trashed_from === 'inbox';
        $to = $incoming ? $e->from_email : $e->to;
        $cc = $all ? implode(', ', array_diff(Email::addresses($e->to.','.$e->cc), [strtolower((string) $to), strtolower((string) SiteMailer::fromAddress())])) : '';
        $this->write(['to' => $to, 'cc' => $cc, 'lead_id' => $e->lead_id,
            'subject' => preg_match('/^re:/i', $e->subject) ? $e->subject : 'Re: '.$e->subject,
            'body' => '<p></p><p></p><blockquote><p>On '.e($e->sent_at?->format('D j M Y, H:i')).', '.e($incoming ? ($e->from_name ?: $e->from_email) : 'we').' wrote:</p>'.self::quote($e->body).'</blockquote>']);
    }

    public function forward(int $id): void
    {
        $e = $this->mine($id);
        if (! $e) return;
        $this->write(['subject' => preg_match('/^fwd?:/i', $e->subject) ? $e->subject : 'Fwd: '.$e->subject, 'lead_id' => $e->lead_id,
            'body' => '<p></p><p>---------- Forwarded message ----------<br>From: '.e($e->from_name ?: $e->from_email ?: 'GTech Digital').'<br>Date: '.e($e->sent_at?->format('D j M Y, H:i')).'<br>Subject: '.e($e->subject).'<br>To: '.e($e->to).'</p>'.self::quote($e->body)]);
    }

    /** A message body made safe to quote in the editor. */
    private static function quote(?string $html): string
    {
        return \App\Support\Site\Sanitizer::clean(preg_replace('~<(style|script|head)\b[\s\S]*?</\1>~i', '', (string) $html));
    }

    private function editDraft(Email $e): void
    {
        $this->write(['to' => $e->to, 'cc' => $e->cc, 'subject' => $e->subject, 'body' => $e->body, 'lead_id' => $e->lead_id, 'draft_id' => $e->id]);
    }

    public function closeCompose(): void
    {
        $this->composing = false;
    }

    public function saveDraft(): void
    {
        if (! $this->canSend()) return;
        $d = $this->composeForm->getRawState();
        $row = ['folder' => 'draft', 'status' => 'draft', 'to' => (string) ($d['to'] ?? ''), 'cc' => (string) ($d['cc'] ?? ''), 'subject' => (string) ($d['subject'] ?? ''),
            'body' => (string) ($d['body'] ?? ''), 'lead_id' => $d['lead_id'] ?: null, 'user_id' => auth()->id(), 'read_at' => now(), 'sent_at' => now()];
        $draft = ! empty($d['draft_id']) ? $this->base()->where('folder', 'draft')->find($d['draft_id']) : null;
        $draft ? $draft->update($row) : Email::query()->create($row);
        $this->composing = false;
        Notification::make()->title('Draft saved')->success()->send();
    }

    public function send(): void
    {
        abort_unless($this->canSend(), 403);
        $d = $this->composeForm->getState();
        $me = auth()->user();
        $lead = ! empty($d['lead_id']) ? Lead::query()->visibleTo($me)->find($d['lead_id']) : null;
        try {
            SiteMailer::send((string) $d['to'], (string) $d['subject'], (string) $d['body'], $me?->email, plain: true, cc: $d['cc'] ?? null,
                context: ['lead_id' => $lead?->id, 'user_id' => $me?->id, 'draft_id' => $d['draft_id'] ?? null]);
            if (! empty($d['copy']) && $me?->email) SiteMailer::send($me->email, 'Copy: '.$d['subject'], (string) $d['body'], null, plain: true, context: ['user_id' => $me->id]);
        } catch (\Throwable $e) {
            Notification::make()->title('The email was not sent')->body($e->getMessage())->danger()->persistent()->send();
            return;
        }
        if ($lead) {
            $lead->log('email', 'Sent "'.$d['subject']."\"\n\n".\App\Support\RevisionDiff::text((string) $d['body']));
            if ($lead->status === 'new') $lead->status = 'contacted';
            if (! $lead->first_contacted_at) $lead->first_contacted_at = now();
            $lead->save();
        }
        $this->composing = false;
        $this->setFolder('sent');
        Notification::make()->title('Email sent')->success()->send();
    }

    public function checkMail(): void
    {
        try {
            $n = Inbox::fetch();
            $this->setFolder('inbox');
            Notification::make()->title($n ? $n.' new '.Str::plural('email', $n) : 'No new email')->success()->send();
        } catch (\Throwable $e) {
            Notification::make()->title('Could not check the inbox')->body($e->getMessage())->danger()->send();
        }
    }

    public function inboxReady(): bool
    {
        return Inbox::enabled();
    }
}
