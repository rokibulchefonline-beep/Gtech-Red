<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    /** GET /api/media/{id}: old MongoDB ids and new numeric ids both work. */
    public function show(string $id)
    {
        $m = Media::query()->where('legacy_id', $id)->when(ctype_digit($id), fn ($q) => $q->orWhere('id', (int) $id))->first();
        if (! $m || ! Storage::disk('public')->exists($m->path)) abort(404);
        return response()->file(Storage::disk('public')->path($m->path), [
            'Content-Type' => $m->type, 'Cache-Control' => 'public, max-age=31536000, immutable',
            'X-Content-Type-Options' => 'nosniff', 'Content-Security-Policy' => "default-src 'none'; style-src 'unsafe-inline'; sandbox",
        ]);
    }
}
