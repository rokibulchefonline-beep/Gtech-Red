<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Redirect extends Model
{
    use \App\Models\Concerns\BlankNotNull;

    protected $fillable = ['from_path', 'to_path', 'status_code', 'automatic', 'hits', 'last_hit_at'];

    protected function casts(): array
    {
        return ['automatic' => 'boolean', 'last_hit_at' => 'datetime', 'status_code' => 'integer', 'hits' => 'integer'];
    }

    /** "/Blogs/Old-Post/" and "blogs/old-post" are the same address. */
    public static function normalise(string $path): string
    {
        $path = '/'.trim(parse_url($path, PHP_URL_PATH) ?? '', '/');
        return $path === '/' ? '/' : strtolower($path);
    }

    /**
     * An address moved from $old to $new. Earlier redirects to $old now point straight at $new (no chains), and a
     * redirect away from $new is removed (it would loop, and the address is live again).
     */
    public static function moved(string $old, string $new): void
    {
        $old = self::normalise($old); $new = self::normalise($new);
        if ($old === $new) return;
        static::query()->where('from_path', $new)->delete();
        static::query()->where('to_path', $old)->update(['to_path' => $new]);
        static::query()->updateOrCreate(['from_path' => $old], ['to_path' => $new, 'status_code' => 301, 'automatic' => true]);
    }
}
