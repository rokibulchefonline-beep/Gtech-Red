<?php

namespace App\Filament\Support;

use App\Support\ImageTools;
use Filament\Forms\ComponentContainer;
use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use FilamentTiptapEditor\Actions\EditMediaAction;
use FilamentTiptapEditor\TiptapEditor;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * "Edit image" in the article editor (click an image, then the edit button): shows the current image, and edits
 * its alt text, caption, size and loading. Replacing the picture is optional and follows the blog image rules
 * (JPG or WebP, at most 250 KB, or optimised automatically).
 */
class SiteEditMediaAction extends EditMediaAction
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->modalHeading('Edit image')->modalSubmitActionLabel('Save')
            ->mountUsing(fn (ComponentContainer $form, array $arguments) => $form->fill([
                'current' => (string) ($arguments['src'] ?? ''), 'alt' => $arguments['alt'] ?? '', 'title' => $arguments['title'] ?? '',
                'width' => $arguments['width'] ?? '', 'height' => $arguments['height'] ?? '', 'lazy' => (bool) ($arguments['lazy'] ?? true), 'optimise' => true,
            ]))
            ->form(fn (TiptapEditor $component) => [
                Placeholder::make('preview')->hiddenLabel()->content(fn (Get $get) => $get('current')
                    ? new HtmlString('<img src="'.e($get('current')).'" alt="" style="max-height:180px;border-radius:8px;border:1px solid #e5e7eb">') : null),
                \Filament\Forms\Components\Hidden::make('current'),
                TextInput::make('alt')->label('Alt text')->maxLength(200)->helperText('Describe the image for screen readers and Google.'),
                TextInput::make('title')->label('Caption (optional)')->maxLength(250)->helperText('Shown under the image in smaller text.'),
                Checkbox::make('lazy')->label('Load when scrolled into view (lazy)'),
                Group::make([TextInput::make('width'), TextInput::make('height')])->columns(),
                Checkbox::make('optimise')->label('Optimise a replacement automatically (WebP, under 250 KB)'),
                FileUpload::make('src')->label('Replace the image (optional)')
                    ->disk($component->getDisk())->directory($component->getDirectory())->visibility('public')
                    ->acceptedFileTypes(['image/jpeg', 'image/webp'])->image()->maxFiles(1)->maxSize(10240)->live()
                    ->afterStateUpdated(function ($state, Set $set) {
                        if (! $state instanceof TemporaryUploadedFile) return;
                        $set('alt', ImageTools::altFromName($state->getClientOriginalName()) ?: null);
                        if ($d = $state->dimensions()) { $set('width', $d[0]); $set('height', $d[1]); }
                    })
                    ->saveUploadedFileUsing(function (BaseFileUpload $upload, TemporaryUploadedFile $file, Get $get) {
                        if (! $get('optimise') && $file->getSize() > ImageTools::MAX_BYTES) {
                            throw ValidationException::withMessages([$upload->getStatePath() => 'This image is '.ImageTools::kb($file->getSize()).'. Blog images must be 250 KB or less: tick "Optimise automatically" or resize it first.']);
                        }
                        $path = $file->storePubliclyAs($upload->getDirectory(), Str::uuid().'.'.strtolower($file->getClientOriginalExtension()), $upload->getDiskName());
                        if ($get('optimise')) $path = trim($upload->getDirectory().'/'.basename(ImageTools::optimiseFile(Storage::disk($upload->getDiskName())->path($path))), '/');
                        return $path;
                    }),
            ])
            ->action(function (TiptapEditor $component, array $data) {
                $src = trim((string) (is_array($data['src'] ?? null) ? reset($data['src']) : ($data['src'] ?? ''))) ?: (string) ($data['current'] ?? '');
                if (! preg_match('#^(https?:)?//|^/#', $src)) $src = '/storage/'.ltrim($src, '/');
                $src = (string) Str::of($src)->replace(rtrim((string) config('app.url'), '/'), '');
                $component->getLivewire()->dispatch(event: 'insertFromAction', type: 'media', statePath: $component->getStatePath(), media: [
                    'src' => $src, 'alt' => trim((string) ($data['alt'] ?? '')), 'title' => trim((string) ($data['title'] ?? '')) ?: null,
                    'width' => $data['width'] ?? null, 'height' => $data['height'] ?? null, 'lazy' => (bool) ($data['lazy'] ?? false), 'link_text' => null,
                ]);
            });
    }
}
