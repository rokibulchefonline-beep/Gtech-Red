<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** An icon uploaded in the panel. Used anywhere an icon can be chosen, as "custom:<slug>". */
class SiteIcon extends Model
{
    protected $fillable = ['slug', 'name', 'body', 'viewbox', 'mono', 'size', 'uploaded_by'];

    protected function casts(): array
    {
        return ['mono' => 'boolean', 'size' => 'integer'];
    }

    protected static function booted(): void
    {
        $flush = function () {
            \App\Support\Site\Icons::flush();
            \App\Support\Site\PageCache::flush();
        };
        static::saved($flush);
        static::deleted($flush);
    }

    public function key(): string
    {
        return 'custom:'.$this->slug;
    }
}
