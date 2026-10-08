<?php

namespace App\Filament\Support;

use App\Models\Media;
use App\Support\ImageTools;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * An image picker that stores a plain URL (what the website expects). Upload a file and it is added to the
 * media library and its URL filled in, or paste an existing image address.
 *
 * Presets: 'any' (PNG, JPG, WebP, GIF, SVG), 'blog' (JPG or WebP only, at most 250 KB) and 'avatar' (cropped to
 * 300 x 300). "Optimise automatically" converts uploads to WebP under 250 KB. $alt names the alt text field to
 * fill from the file name when it is still empty.
 */
class ImageField
{
    public static function make(string $field, string $label = 'Image', ?string $alt = null, string $preset = 'any'): Group
    {
        $blog = $preset === 'blog';
        $avatar = $preset === 'avatar';
        $types = $blog ? ['image/jpeg', 'image/webp'] : ['image/png', 'image/jpeg', 'image/webp', 'image/gif', 'image/svg+xml'];
        $help = match ($preset) {
            'blog' => 'JPG, JPEG or WebP, at most 250 KB. With "Optimise automatically" larger photos are compressed to fit.',
            'avatar' => 'Cropped to a 300 x 300 square from the centre automatically.',
            default => 'PNG, JPG, WebP, GIF or SVG.',
        };
        return Group::make([
            TextInput::make($field)->label($label)->maxLength(500)->placeholder('Upload below or paste an image address')
                ->live(onBlur: true)
                ->helperText(fn (Get $get) => $get($field) ? new HtmlString('<img src="'.e(self::preview($get($field))).'" style="max-height:90px;margin-top:6px;border:1px solid #eee">') : null),
            Checkbox::make($field.'__optimise')->label('Optimise automatically (WebP, under 250 KB)')->dehydrated(false)->hidden($avatar)
                ->afterStateHydrated(fn (Checkbox $component, $state) => $state === null ? $component->state(true) : null),
            FileUpload::make($field.'__upload')->label('Upload a new image')->image()->maxSize(10240)->helperText($help)
                ->acceptedFileTypes($types)
                ->disk('public')->directory('media')->dehydrated(false)->live()
                ->afterStateUpdated(function (?TemporaryUploadedFile $state, Set $set, Get $get) use ($field, $alt, $blog, $avatar) {
                    if (! $state) return;
                    $set($field.'__upload', null);
                    $m = self::store($state, $avatar ? 'avatar' : ($get($field.'__optimise') !== false ? 'optimise' : ($blog ? 'blog' : 'keep')));
                    if (! $m) return;
                    $set($field, $m->url);
                    if ($alt && blank($get($alt))) $set($alt, ImageTools::altFromName($state->getClientOriginalName()));
                }),
        ]);
    }

    /**
     * Saves an upload to the media library. $how: 'optimise' (WebP under 250 KB), 'avatar' (300 x 300 WebP),
     * 'blog' (as it is, but at most 250 KB) or 'keep'. Returns null (with a message) when the file is refused.
     */
    public static function store(TemporaryUploadedFile $file, string $how = 'keep'): ?Media
    {
        $name = $file->getClientOriginalName();
        // SVG, GIF and animated WebP stay as they are (GD would lose the vector or the animation).
        $raster = ImageTools::canProcess($file->getRealPath(), (string) $file->getMimeType());
        if ($how === 'blog' && $file->getSize() > ImageTools::MAX_BYTES) {
            Notification::make()->danger()->title('Image too large')
                ->body($name.' is '.ImageTools::kb($file->getSize()).'. Blog images must be 250 KB or less: tick "Optimise automatically" or use Image tools > Image resizer first.')->send();
            return null;
        }
        $path = $file->store('media', 'public');
        $disk = Storage::disk('public');
        try {
            if ($raster && in_array($how, ['optimise', 'avatar'], true)) {
                $abs = $how === 'avatar' ? ImageTools::squareFile($disk->path($path)) : ImageTools::optimiseFile($disk->path($path));
                $path = 'media/'.basename($abs);
            }
        } catch (\Throwable $e) {
            $disk->delete($path);
            Notification::make()->danger()->title('Image could not be processed')->body($e->getMessage())->send();
            return null;
        }
        $type = $raster && in_array($how, ['optimise', 'avatar'], true) ? 'image/webp' : $file->getMimeType();
        $shownName = $type === 'image/webp' ? preg_replace('/\.[a-z0-9]+$/i', '', $name).'.webp' : $name;
        return Media::create(['name' => $shownName, 'alt' => ImageTools::altFromName($name), 'type' => $type, 'size' => $disk->size($path), 'path' => $path, 'uploaded_by' => (string) auth()->user()?->email]);
    }

    /** Relative website paths (/posts/x.webp) are shown from the public site. */
    public static function preview(string $v): string
    {
        return str_starts_with($v, '/') && ! str_starts_with($v, '/api/media/') ? SiteLink::to($v) : $v;
    }
}
