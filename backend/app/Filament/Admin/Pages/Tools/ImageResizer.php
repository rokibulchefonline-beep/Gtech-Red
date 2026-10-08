<?php

namespace App\Filament\Admin\Pages\Tools;

use App\Support\ImageTools;
use Filament\Forms;

/** Image tools > Image resizer: new width and height, kept in proportion, cropped or stretched. */
class ImageResizer extends ImageToolPage
{
    protected static ?string $navigationIcon = 'heroicon-o-arrows-pointing-in';
    protected static ?string $navigationLabel = 'Image resizer';
    protected static ?string $title = 'Image resizer';
    protected static ?string $slug = 'image-resizer';
    protected static ?int $navigationSort = 1;

    public function intro(): string
    {
        return 'Make images smaller (or exactly the size you need) before you use them on the website. Common sizes: blog featured image 1200 x 675, author photo 300 x 300, social sharing image 1200 x 630.';
    }

    protected function options(): array
    {
        return [
            Forms\Components\TextInput::make('width')->label('Width (px)')->numeric()->minValue(1)->maxValue(6000)->default(1200),
            Forms\Components\TextInput::make('height')->label('Height (px)')->numeric()->minValue(1)->maxValue(6000)->helperText('Leave empty to keep the proportions.'),
            Forms\Components\Select::make('mode')->label('How to fit')->default('fit')->selectablePlaceholder(false)->options([
                'fit' => 'Keep proportions (fit inside)', 'crop' => 'Fill the exact size (crop the edges)', 'stretch' => 'Stretch to the exact size',
            ]),
            Forms\Components\Select::make('format')->label('Save as')->default('same')->selectablePlaceholder(false)
                ->options(['same' => 'Same format as the original'] + ImageTools::FORMATS),
            self::quality(),
            Forms\Components\Checkbox::make('enlarge')->label('Allow making small images bigger'),
            self::limit(),
        ];
    }

    protected function process(string $absPath, string $mime, array $data): array
    {
        $img = ImageTools::resize(ImageTools::load($absPath), (int) ($data['width'] ?? 0), (int) ($data['height'] ?? 0), (string) ($data['mode'] ?? 'fit'), (bool) ($data['enlarge'] ?? false));
        $format = ($data['format'] ?? 'same') === 'same' ? (['image/png' => 'png', 'image/webp' => 'webp'][$mime] ?? 'jpg') : $data['format'];
        return [$format, self::output($img, $format, $data)];
    }
}
