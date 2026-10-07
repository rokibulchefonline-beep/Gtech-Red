<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Resources\LeadResource;
use App\Models\Lead;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;

/**
 * The leads as a board: one column per stage (Site settings > Leads), drag a card to move the lead on. Won and
 * Lost show the last 30 days. Moving a card is recorded on the lead's timeline like any status change.
 */
class Pipeline extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-view-columns';
    protected static ?string $navigationGroup = 'Leads';
    protected static ?string $navigationLabel = 'Pipeline';
    protected static ?int $navigationSort = 0;
    protected static string $view = 'filament.admin.pages.pipeline';

    public const PER_COLUMN = 60;

    #[Url] public string $owner = '';
    #[Url] public string $q = '';

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->hasPerm('leads.view');
    }

    public function canMove(): bool
    {
        return (bool) auth()->user()?->hasPerm('leads.edit');
    }

    private function query(): Builder
    {
        $u = auth()->user();
        return Lead::query()->visibleTo($u)
            ->when($this->owner === 'me', fn ($q) => $q->where('assigned_to', $u->id))
            ->when($this->owner === 'none', fn ($q) => $q->whereNull('assigned_to'))
            ->when(ctype_digit($this->owner), fn ($q) => $q->where('assigned_to', (int) $this->owner))
            ->when(trim($this->q) !== '', fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', '%'.trim($this->q).'%')
                ->orWhere('business', 'like', '%'.trim($this->q).'%')->orWhere('email', 'like', '%'.trim($this->q).'%')));
    }

    /** @return array<array{key:string,label:string,count:int,value:float,leads:\Illuminate\Support\Collection,closed:bool}> */
    public function columns(): array
    {
        $out = [];
        foreach (Lead::statuses() as $key => $label) {
            $closed = in_array($key, Lead::CLOSED, true);
            $q = $this->query()->where('status', $key)->when($closed, fn ($q) => $q->where('updated_at', '>=', now()->subDays(30)));
            $out[] = ['key' => $key, 'label' => $label, 'closed' => $closed, 'count' => (clone $q)->count(), 'value' => (float) (clone $q)->sum('value'),
                'leads' => $q->with('owner')->orderByRaw('next_action_at is null')->orderBy('next_action_at')->latest()->limit(self::PER_COLUMN)->get()];
        }
        return $out;
    }

    public function ownerOptions(): array
    {
        $all = (bool) auth()->user()?->hasPerm('leads.all');
        return ['' => $all ? 'Everyone' : 'My leads', 'me' => 'Assigned to me'] + ($all ? ['none' => 'Nobody'] + LeadResource::ownerOptions() : []);
    }

    public function move(int $id, string $stage): void
    {
        abort_unless($this->canMove() && isset(Lead::statuses()[$stage]), 403);
        $lead = Lead::query()->visibleTo(auth()->user())->find($id);
        if (! $lead || $lead->status === $stage) return;
        $lead->update(['status' => $stage]);
        Notification::make()->title($lead->name.' moved to '.Lead::statuses()[$stage])->success()->duration(2500)->send();
    }

    public function url(Lead $l): string
    {
        return LeadResource::getUrl(LeadResource::canEdit($l) ? 'edit' : 'view', ['record' => $l]);
    }
}
