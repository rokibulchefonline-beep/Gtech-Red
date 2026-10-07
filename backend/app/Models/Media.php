<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use \App\Models\Concerns\BlankNotNull;

    protected $table = 'media';

    protected $fillable = ['legacy_id','name','type','size','path','uploaded_by'];

    protected function casts(): array
    {
        return ['size'=>'integer'];
    }

    /** SVG files can carry scripts: every uploaded SVG is cleaned (scripts, event handlers, outside links removed). */
    protected static function booted(): void
    {
        static::saving(function (Media $m) {
            if (! $m->isDirty('path') || ! self::isSvg($m)) return;
            $disk = \Illuminate\Support\Facades\Storage::disk('public');
            if (! $disk->exists($m->path)) return;
            $clean = self::cleanSvg((string) $disk->get($m->path));
            if ($clean === null) { $disk->delete($m->path); throw new \RuntimeException('This SVG file could not be read safely. Please upload a PNG, JPG or WebP instead.'); }
            $disk->put($m->path, $clean);
            $m->size = strlen($clean);
        });
    }

    public static function isSvg(Media $m): bool
    {
        return str_contains(strtolower((string) $m->type), 'svg') || str_ends_with(strtolower((string) $m->path), '.svg');
    }

    /** The SVG without anything that can run code; null when it is not a valid SVG. */
    public static function cleanSvg(string $svg): ?string
    {
        $s = new \enshrined\svgSanitize\Sanitizer();
        $s->removeRemoteReferences(true);
        $out = $s->sanitize($svg);
        return $out === false || trim((string) $out) === '' ? null : $out;
    }

    /** Public URL. Old MongoDB ids keep their /api/media/<id> address. */
    public function getUrlAttribute(): string
    {
        return url('/api/media/'.($this->legacy_id ?: $this->id));
    }
}
