<?php

namespace App\Filament\Support;

use FilamentTiptapEditor\TiptapEditor;

/**
 * The rich text editor for long articles. The stock editor copies the whole document into the form after every key
 * press, which makes typing lag once a post passes a couple of thousand words. This one copies it after a short pause
 * instead (the form still only talks to the server when you save).
 */
class ArticleEditor extends TiptapEditor
{
    public function getLiveDebounce(): int | string | null
    {
        return parent::getLiveDebounce() ?? 400;
    }
}
