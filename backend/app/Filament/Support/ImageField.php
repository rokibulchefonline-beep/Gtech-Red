<?php

namespace App\Filament\Support;

use App\Models\Media;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\HtmlString;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * An image picker that stores a plain URL (what the website expects). Upload a file and it is added to the
 * media library and its URL filled in, or paste an existing image address.
 */
class ImageField
{
    public static function make(string $field, string $label = 'Image'): Group
    {
        return Group::make([
            TextInput::make($field)->label($label)->maxLength(500)->placeholder('Upload below or paste an image address')
                ->live(onBlur: true)
                ->helperText(fn (Get $get) => $get($field) ? new HtmlString('<img src="'.e(self::preview($get($field))).'" style="max-height:90px;margin-top:6px;border:1px solid #eee">') : null),
            FileUpload::make($field.'__upload')->label('Upload a new image')->image()->maxSize(4096)
                ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp', 'image/gif', 'image/svg+xml'])
                ->disk('public')->directory('media')->dehydrated(false)->live()
                ->afterStateUpdated(function (?TemporaryUploadedFile $state, Set $set) use ($field) {
                    if (! $state) return;
                    $path = $state->store('media', 'public');
                    $m = Media::create(['name' => $state->getClientOriginalName(), 'type' => $state->getMimeType(), 'size' => $state->getSize(), 'path' => $path, 'uploaded_by' => (string) auth()->user()?->email]);
                    $set($field, $m->url);
                    $set($field.'__upload', null);
                }),
        ]);
    }

    /** Relative website paths (/posts/x.webp) are shown from the public site. */
    public static function preview(string $v): string
    {
        return str_starts_with($v, '/') && ! str_starts_with($v, '/api/media/') ? rtrim(config('gtech.site_url'), '/').$v : $v;
    }
}
