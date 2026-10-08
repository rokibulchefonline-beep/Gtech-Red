<?php

namespace App\Filament\Admin\Pages\Tools;

use App\Support\ImageTools;
use Filament\Forms;

/** Image tools > Image converter: PNG, JPG, GIF, BMP or AVIF to WebP, JPG or PNG. */
class ImageConverter extends ImageToolPage
{
    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static ?string $navigationLabel = 'Image converter';
    protected static ?string $title = 'Image converter';
    protected static ?string $slug = 'image-converter';
    protected static ?int $navigationSort = 2;

    public function intro(): string
    {
        return 'Change an image\'s file type. WebP is the best choice for the website: usually 25-35% smaller than JPG at the same quality. Transparent areas turn white in JPG.';
    }

    protected function options(): array
    {
        return [
            Forms\Components\Select::make('format')->label('Convert to')->default('webp')->selectablePlaceholder(false)->options(ImageTools::FORMATS),
            self::quality(),
            Forms\Components\TextInput::make('max_width')->label('Largest width (px, optional)')->numeric()->minValue(1)->maxValue(6000)
                ->helperText('Wider images are made smaller. Leave empty to keep the size.'),
            self::limit(),
        ];
    }

    protected function process(string $absPath, string $mime, array $data): array
    {
        $img = ImageTools::load($absPath);
        if (! empty($data['max_width'])) $img = ImageTools::resize($img, (int) $data['max_width'], 0);
        $format = (string) ($data['format'] ?? 'webp');
        return [$format, self::output($img, $format, $data)];
    }
}
