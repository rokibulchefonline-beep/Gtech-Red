<?php

namespace App\Filament\Admin\Pages\Tools;

use App\Models\Media;
use App\Support\ImageTools;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Shared base of the Image resizer and Image converter: upload one or more images, choose the options, get the
 * results with their new size to download, or save them to the Media library. Results are kept for a day.
 */
abstract class ImageToolPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationGroup = 'Image tools';
    protected static string $view = 'filament.admin.pages.image-tool';

    public ?array $data = [];

    /** @var array<int, array{name:string, path:string, url:string, before:int, after:int, w:int, h:int, saved?:bool}> */
    public array $results = [];

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->hasPerm('media.view');
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    /** The tool's own options. */
    abstract protected function options(): array;

    /** Output format and bytes for one image, from the form data. */
    abstract protected function process(string $absPath, string $mime, array $data): array;

    abstract public function intro(): string;

    public function form(Form $form): Form
    {
        return $form->statePath('data')->schema([
            Forms\Components\FileUpload::make('files')->label('Images')->multiple()->maxFiles(20)->image()->maxSize(20480)
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/bmp', 'image/avif'])
                ->storeFiles(false)->required()->helperText('Up to 20 images, 20 MB each. JPG, PNG, WebP, GIF (first frame), BMP or AVIF.'),
            Forms\Components\Section::make('Options')->schema($this->options())->columns(3),
        ]);
    }

    public function run(): void
    {
        $data = $this->form->getState();
        $disk = Storage::disk('public');
        $this->cleanOld();
        $this->results = [];
        foreach ((array) ($data['files'] ?? []) as $file) {
            if (! $file instanceof TemporaryUploadedFile) continue;
            $name = $file->getClientOriginalName();
            try {
                [$ext, $bytes] = $this->process($file->getRealPath(), (string) $file->getMimeType(), $data);
            } catch (\Throwable $e) {
                Notification::make()->danger()->title($name.' could not be processed')->body($e->getMessage())->send();
                continue;
            }
            $path = 'tools/'.Str::random(8).'-'.Str::slug(pathinfo($name, PATHINFO_FILENAME) ?: 'image').'.'.$ext;
            $disk->put($path, $bytes);
            $size = @getimagesizefromstring($bytes) ?: [0, 0];
            $this->results[] = ['name' => pathinfo($name, PATHINFO_FILENAME).'.'.$ext, 'path' => $path, 'url' => $disk->url($path),
                'before' => (int) $file->getSize(), 'after' => strlen($bytes), 'w' => (int) $size[0], 'h' => (int) $size[1]];
        }
        if ($this->results) Notification::make()->success()->title(count($this->results).' image(s) ready')->send();
        $this->form->fill(collect($data)->except('files')->all());
    }

    /** Adds a result to the Media library. */
    public function save(int $i): void
    {
        $r = $this->results[$i] ?? null;
        if (! $r || ! empty($r['saved']) || ! auth()->user()?->hasPerm('media.create')) return;
        $disk = Storage::disk('public');
        $to = 'media/'.basename($r['path']);
        $disk->copy($r['path'], $to);
        $m = Media::create(['name' => $r['name'], 'alt' => ImageTools::altFromName($r['name']), 'type' => $disk->mimeType($to) ?: 'image/webp',
            'size' => $disk->size($to), 'path' => $to, 'uploaded_by' => (string) auth()->user()?->email]);
        $this->results[$i]['saved'] = true;
        $this->results[$i]['media'] = $m->url;
        Notification::make()->success()->title('Saved to the Media library')->body($m->url)->send();
    }

    /** Results older than a day are removed. */
    private function cleanOld(): void
    {
        $disk = Storage::disk('public');
        foreach ($disk->files('tools') as $f) if ($disk->lastModified($f) < time() - 86400) $disk->delete($f);
    }

    public static function kb(int $b): string
    {
        return ImageTools::kb($b);
    }

    protected static function quality(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('quality')->label('Quality (1-100)')->numeric()->minValue(1)->maxValue(100)->default(82)
            ->helperText('82 looks the same as the original for most photos. Ignored for PNG.');
    }

    protected static function limit(): Forms\Components\Checkbox
    {
        return Forms\Components\Checkbox::make('limit')->label('Keep each file under 250 KB')->default(true)
            ->helperText('Lowers the quality, then the size, until the file fits (blog images must be 250 KB or less).');
    }

    /** Bytes in $format, honouring the quality and the 250 KB option. */
    protected static function output(\GdImage $img, string $format, array $data): string
    {
        $q = max(1, min(100, (int) ($data['quality'] ?? 82)));
        $bytes = ImageTools::encode($img, $format, $q);
        if (! empty($data['limit']) && strlen($bytes) > ImageTools::MAX_BYTES) $bytes = ImageTools::compress($img, $format === 'png' ? 'webp' : $format, ImageTools::MAX_BYTES, imagesx($img));
        return $bytes;
    }
}
