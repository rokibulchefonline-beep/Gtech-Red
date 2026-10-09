<?php

namespace App\Filament\Support;

use App\Support\ImageTools;
use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use FilamentTiptapEditor\Actions\MediaAction;
use FilamentTiptapEditor\TiptapEditor;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * The article editor's image box. The package turned a fresh upload's stored path (media/x.webp) into
 * /media/x.webp, which does not exist; uploads live under /storage. Addresses are kept relative to the site.
 *
 * Blog image rules: JPG, JPEG or WebP only, at most 250 KB. "Optimise automatically" (on by default) turns a larger
 * photo into a WebP under 250 KB. The alt text starts from the file name, and the caption shows under the image.
 */
class SiteMediaAction extends MediaAction
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->form(fn (TiptapEditor $component) => [
            Checkbox::make('optimise')->label('Optimise automatically (WebP, under 250 KB)')->default(true)->live()
                ->afterStateHydrated(fn (Checkbox $c, $state) => $state === null ? $c->state(true) : null),
            FileUpload::make('src')->label('Image')
                ->helperText('JPG, JPEG or WebP, at most 250 KB (larger photos are compressed when "Optimise automatically" is ticked).')
                ->disk($component->getDisk())->directory($component->getDirectory())->visibility('public')
                ->acceptedFileTypes(['image/jpeg', 'image/webp'])->image()->maxFiles(1)->maxSize(10240)->required()->live()
                ->imageResizeTargetWidth('1920')->imageResizeTargetHeight('1920')->imageResizeMode('contain')->imageResizeUpscale(false)
                ->validationMessages(['uploaded' => ImageField::UPLOAD_FAILED])
                ->afterStateUpdated(function ($state, Set $set, Get $get) {
                    if (! $state instanceof TemporaryUploadedFile) return;
                    $set('type', 'image');
                    if (blank($get('alt'))) $set('alt', ImageTools::altFromName($state->getClientOriginalName()));
                    if ($d = $state->dimensions()) { $set('width', $d[0]); $set('height', $d[1]); }
                })
                ->saveUploadedFileUsing(function (BaseFileUpload $upload, TemporaryUploadedFile $file, Get $get) {
                    if ($get('optimise') === false && $file->getSize() > ImageTools::MAX_BYTES) {
                        throw ValidationException::withMessages([$upload->getStatePath() => 'This image is '.ImageTools::kb($file->getSize()).'. Blog images must be 250 KB or less: tick "Optimise automatically" or resize it first.']);
                    }
                    $path = $file->storePubliclyAs($upload->getDirectory(), Str::uuid().'.'.strtolower($file->getClientOriginalExtension()), $upload->getDiskName());
                    if ($get('optimise') !== false) $path = trim($upload->getDirectory().'/'.basename(ImageTools::optimiseFile(Storage::disk($upload->getDiskName())->path($path))), '/');
                    return $path;
                }),
            TextInput::make('alt')->label('Alt text')->maxLength(200)->helperText('Filled from the file name; change it to describe the image.'),
            TextInput::make('title')->label('Caption (optional)')->maxLength(250)->helperText('Shown under the image in smaller text.'),
            Checkbox::make('lazy')->label('Load when scrolled into view (lazy)')->default(true),
            Group::make([TextInput::make('width'), TextInput::make('height')])->columns(),
            Hidden::make('type')->default('image'),
        ]);
        $this->action(function (TiptapEditor $component, array $data) {
            $src = (string) $data['src'];
            if (! preg_match('#^(https?:)?//|^/#', $src)) $src = '/storage/'.ltrim($src, '/'); // a stored upload path
            $src = (string) Str::of($src)->replace(rtrim((string) config('app.url'), '/'), '');

            $component->getLivewire()->dispatch(
                event: 'insertFromAction',
                type: 'media',
                statePath: $component->getStatePath(),
                media: [
                    'src' => $src,
                    'alt' => trim((string) ($data['alt'] ?? '')),
                    'title' => $data['title'] ?? null,
                    'width' => $data['width'] ?? null,
                    'height' => $data['height'] ?? null,
                    'lazy' => $data['lazy'] ?? false,
                    'link_text' => $data['link_text'] ?? null,
                ],
            );
        });
    }
}
