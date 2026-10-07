<?php

namespace App\Filament\Admin\Resources\PageResource\Pages;

use App\Filament\Admin\Pages\VersionHistory;
use App\Filament\Admin\Resources\PageResource;
use App\Models\Page;
use App\Support\Site\PagePreview;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

/**
 * Editing a page never changes the website until it is published: "Save draft" keeps the changes aside,
 * "Preview" shows them as the page will look, and "Publish" (now or at a set time) puts them live.
 */
class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    /** What the next save does: publish, draft or schedule. */
    public string $saveMode = 'draft';
    public ?string $scheduleAt = null;

    public function getTitle(): string
    {
        return $this->record->name;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return PageResource::beforeFill(array_merge($data, $this->record->withDraft()->attributesToArray()));
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return PageResource::beforeSave($data, $this->record->withDraft());
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var Page $record */
        if ($this->saveMode === 'publish') {
            abort_unless(PageResource::canPublish(), 403);
            $record->publish($data);
        } elseif ($this->saveMode === 'schedule') {
            abort_unless(PageResource::canPublish(), 403);
            $record->saveDraft($data, \Illuminate\Support\Carbon::parse($this->scheduleAt));
        } else {
            $record->saveDraft($data);
        }
        return $record;
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return match ($this->saveMode) {
            'publish' => 'Published: the changes are live',
            'schedule' => 'Scheduled for '.$this->record->publish_at?->format('D j M Y, H:i'),
            default => 'Draft saved. The website is unchanged until you publish.',
        };
    }

    protected function afterSave(): void
    {
        $this->saveMode = 'draft';
        $this->scheduleAt = null;
    }

    protected function getFormActions(): array
    {
        $published = $this->record->published;
        return [
            Actions\Action::make('publishNow')->label($published ? 'Publish changes' : 'Publish page')->icon('heroicon-o-rocket-launch')
                ->visible(fn () => PageResource::canPublish())
                ->requiresConfirmation(! $published)->modalDescription($published ? null : 'The page goes live at '.$this->record->path.' and is added to the sitemap.')
                ->action(function () {
                    $this->saveMode = 'publish';
                    $this->save();
                }),
            Actions\Action::make('saveDraft')->label('Save draft')->icon('heroicon-o-document-check')->color('gray')
                ->action(function () {
                    $this->saveMode = 'draft';
                    $this->save();
                }),
            Actions\Action::make('schedule')->label('Schedule')->icon('heroicon-o-calendar-days')->color('gray')
                ->visible(fn () => PageResource::canPublish())
                ->modalWidth('md')->modalSubmitActionLabel('Schedule')
                ->form([Forms\Components\DateTimePicker::make('at')->label('Publish on')->required()->seconds(false)->native(false)
                    ->minDate(now())->default(now()->addDay()->setTime(9, 0))->helperText('UK time. The changes go live automatically.')])
                ->action(function (array $data) {
                    $this->saveMode = 'schedule';
                    $this->scheduleAt = $data['at'];
                    $this->save();
                }),
            $this->previewAction(),
            $this->getCancelFormAction(),
        ];
    }

    /** Opens the unsaved form in a preview tab (the tab is opened on click, so pop-up blockers allow it). */
    private function previewAction(): Actions\Action
    {
        return Actions\Action::make('preview')->label('Preview')->icon('heroicon-o-eye')->color('gray')
            ->extraAttributes(['x-on:click' => 'window.open(`about:blank`, `gtpreview`)'])
            ->action(function () {
                $row = PageResource::beforeSave($this->form->getRawState(), $this->record->withDraft());
                $url = PagePreview::url(PagePreview::store($this->record, $row));
                $this->js('window.open('.json_encode($url).", 'gtpreview')");
            });
    }

    protected function getHeaderActions(): array
    {
        $r = $this->record;
        return [
            Actions\Action::make('site')->label('View live page')->icon('heroicon-o-arrow-top-right-on-square')->color('gray')->visible($r->published)
                ->url(\App\Filament\Support\SiteLink::to($r->path), shouldOpenInNewTab: true),
            $this->previewAction()->label('Preview')->outlined(),
            Actions\ActionGroup::make([
                Actions\Action::make('discard')->label($r->publish_at && ! $r->hasDraft() ? 'Cancel the schedule' : 'Discard draft')->icon('heroicon-o-trash')->color('danger')
                    ->visible($r->hasDraft() || $r->publish_at !== null)->requiresConfirmation()
                    ->modalDescription('Throw away the unpublished changes'.($r->publish_at ? ' and the schedule' : '').'? The live page stays as it is.')
                    ->action(function () use ($r) {
                        $r->discardDraft();
                        $r->forceFill(['publish_at' => null])->save();
                        Notification::make()->title('Draft discarded')->success()->send();
                        $this->redirect(PageResource::getUrl('edit', ['record' => $r]));
                    }),
                Actions\Action::make('unpublish')->label('Unpublish')->icon('heroicon-o-eye-slash')->color('danger')
                    ->visible($r->kind === 'landing' && $r->published && PageResource::canPublish())->requiresConfirmation()
                    ->modalDescription('Take this page off the website? Its address shows "not found" until you publish it again.')
                    ->action(function () use ($r) {
                        Page::$revisionLabel = 'Unpublished';
                        $r->forceFill(['published' => false])->save();
                        Notification::make()->title('Page unpublished')->success()->send();
                        $this->redirect(PageResource::getUrl('edit', ['record' => $r]));
                    }),
                Actions\Action::make('history')->label('Version history')->icon('heroicon-o-clock')->url(VersionHistory::urlFor($r)),
                Actions\DeleteAction::make()->visible(fn () => PageResource::canDelete($r)),
            ])->label('More')->icon('heroicon-m-ellipsis-vertical')->button()->color('gray'),
        ];
    }
}
