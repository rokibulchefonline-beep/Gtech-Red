<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Per-page SEO overrides. Key: the encoded path ("home", "services~seo"). */
class SeoEntry extends Model
{
    use \App\Models\Concerns\BlankNotNull;

    protected $table = 'seo_entries';
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['key', 'path', 'title', 'description', 'canonical', 'og_image', 'noindex', 'focus_keyword', 'schema_off', 'schema_custom'];

    protected function casts(): array
    {
        return ['noindex' => 'boolean', 'schema_off' => 'boolean'];
    }

    public static function keyFor(string $path): string
    {
        return $path === '/' ? 'home' : str_replace('/', '~', ltrim($path, '/'));
    }
}
