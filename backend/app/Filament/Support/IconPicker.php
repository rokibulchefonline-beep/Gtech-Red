<?php

namespace App\Filament\Support;

use App\Support\Site\Icons;
use Filament\Forms\Components\Select;

/**
 * Choose an icon: search ~2,100 line icons, ~3,700 brand logos and your uploaded icons (Website content > Icons),
 * each shown with its picture.
 */
class IconPicker
{
    public static function make(string $name = 'icon', string $label = 'Icon'): Select
    {
        return Select::make($name)->label($label)->searchable()->allowHtml()->placeholder('No icon')
            ->options(fn () => collect(Icons::popular())->mapWithKeys(fn ($k) => [$k => Icons::label($k)])->all())
            ->getSearchResultsUsing(fn (string $search) => collect(Icons::search($search))->mapWithKeys(fn ($k) => [$k => Icons::label($k)])->all())
            ->getOptionLabelUsing(fn ($value) => $value ? Icons::label((string) $value) : null)
            ->searchPrompt('Type to search 5,900+ icons, e.g. "chart", "shield", "google"')
            ->rule(fn () => fn ($attr, $v, $fail) => filled($v) && ! Icons::exists((string) $v) ? $fail('Choose an icon from the list.') : null)
            ->helperText('Search line icons, brand logos and icons you uploaded (Website content > Icons).');
    }
}
