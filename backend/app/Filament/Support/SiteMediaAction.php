<?php

namespace App\Filament\Support;

use FilamentTiptapEditor\Actions\MediaAction;
use FilamentTiptapEditor\TiptapEditor;
use Illuminate\Support\Str;

/**
 * The article editor's image box. The package turned a fresh upload's stored path (media/x.webp) into
 * /media/x.webp, which does not exist; uploads live under /storage. Addresses are kept relative to the site.
 */
class SiteMediaAction extends MediaAction
{
    protected function setUp(): void
    {
        parent::setUp();
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
