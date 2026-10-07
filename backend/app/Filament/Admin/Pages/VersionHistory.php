<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Resources\CaseStudyResource;
use App\Filament\Admin\Resources\PageResource;
use App\Filament\Admin\Resources\PostResource;
use App\Models\Page as SitePage;
use App\Models\Revision;
use App\Support\RevisionDiff;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Url;

/**
 * Every saved version of a page, blog post or case study: compare any version with the current one and put it
 * back. Pages are restored into a draft (preview, then publish); posts and case studies are restored directly.
 */
class VersionHistory extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $view = 'filament.admin.pages.version-history';
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $slug = 'version-history';

    #[Url] public string $model = '';
    #[Url] public string $key = '';
    #[Url] public ?int $compare = null;

    private const RESOURCES = ['page' => PageResource::class, 'post' => PostResource::class, 'case_study' => CaseStudyResource::class];
    private const SECTIONS = ['page' => 'pages', 'post' => 'posts', 'case_study' => 'case_studies'];

    public static function urlFor(Model $record): string
    {
        return static::getUrl(['model' => $record::revisionType(), 'key' => (string) $record->getKey()]);
    }

    public function mount(): void
    {
        abort_unless(isset(self::RESOURCES[$this->model]) && auth()->user()?->hasPerm(self::SECTIONS[$this->model].'.view'), 403);
        abort_unless($this->subject(), 404);
        $this->compare ??= $this->revisions()->skip(1)->value('id') ?? $this->revisions()->value('id');
    }

    public function subject(): ?Model
    {
        return once(fn () => (Revision::MODELS[$this->model] ?? null)::query()->find($this->key));
    }

    private function revisions()
    {
        return Revision::query()->where('model', $this->model)->where('model_key', $this->key)->latest('id');
    }

    public function getTitle(): string
    {
        $s = $this->subject();
        return 'Version history: '.($s->name ?? $s->title ?? '');
    }

    public function getBreadcrumbs(): array
    {
        $r = self::RESOURCES[$this->model];
        return [$r::getUrl() => ucfirst($r::getPluralModelLabel()), $this->editUrl() => 'Edit', 'Version history'];
    }

    private function editUrl(): string
    {
        return self::RESOURCES[$this->model]::getUrl('edit', ['record' => $this->subject()]);
    }

    private function canRestore(): bool
    {
        $s = $this->subject();
        return self::RESOURCES[$this->model]::canEdit($s);
    }

    /** The version being compared and the current content (for a page: its draft if it has one). */
    public function comparison(): ?array
    {
        $rev = $this->compare ? $this->revisions()->whereKey($this->compare)->first() : null;
        if (! $rev) return null;
        $s = $this->subject();
        $current = $s instanceof SitePage ? $s->withDraft() : $s;
        $now = array_intersect_key(json_decode(json_encode($current->revisionData()), true), $rev->data);
        return ['rev' => $rev, 'rows' => RevisionDiff::rows($this->model, $rev->data, $now),
            'against' => $s instanceof SitePage && $s->hasDraft() ? 'the current draft' : 'the current version'];
    }

    public function table(Table $table): Table
    {
        return $table->query(fn () => $this->revisions()->with('user'))
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('Saved')->dateTime('D j M Y, H:i')->description(fn (Revision $r) => $r->created_at?->diffForHumans()),
                Tables\Columns\TextColumn::make('label')->label('What happened')->badge()->color(fn (string $state) => str_starts_with($state, 'Restored') ? 'warning' : 'gray'),
                Tables\Columns\TextColumn::make('user.name')->label('By')->placeholder('Automatic'),
                Tables\Columns\IconColumn::make('id')->label('')->icon(fn (Revision $r) => $r->id === $this->compare ? 'heroicon-s-eye' : null)->color('primary'),
            ])
            ->recordAction('compare')
            ->actions([
                Tables\Actions\Action::make('compare')->label('Compare')->icon('heroicon-o-arrows-right-left')->color('gray')
                    ->action(fn (Revision $r) => $this->compare = $r->id),
                Tables\Actions\Action::make('restore')->label('Restore')->icon('heroicon-o-arrow-uturn-left')->visible(fn () => $this->canRestore())
                    ->requiresConfirmation()->modalHeading('Restore this version?')
                    ->modalDescription(fn () => $this->model === 'page'
                        ? 'It is loaded into the page\'s draft, replacing any unpublished changes. The website changes only when you publish.'
                        : 'It replaces the current content straight away. The current content stays in the history, so you can undo this.')
                    ->action(fn (Revision $r) => $this->restore($r)),
            ])
            ->paginated([10, 25, 50]);
    }

    public function restore(Revision $r): void
    {
        abort_unless($this->canRestore() && $r->model === $this->model && $r->model_key === $this->key, 403);
        $s = $this->subject();
        if ($s instanceof SitePage) {
            $s->saveDraft(array_intersect_key($r->data, array_flip(SitePage::DRAFTABLE)) + $s->withDraft()->only(SitePage::DRAFTABLE));
            Notification::make()->title('Loaded into the draft')->body('Preview it, then publish.')->success()->send();
            $this->redirect(PageResource::getUrl('edit', ['record' => $s]));
            return;
        }
        $data = $r->data;
        // Someone who cannot publish keeps the current publishing status.
        if ($this->model === 'post' && ! PostResource::canPublish()) unset($data['status'], $data['date']);
        $s::$revisionLabel = 'Restored version from '.$r->created_at?->format('j M Y H:i');
        $s->fill(array_intersect_key($data, array_flip($s::REVISIONED)))->save();
        Notification::make()->title('Version restored')->success()->send();
        $this->redirect($this->editUrl());
    }
}
