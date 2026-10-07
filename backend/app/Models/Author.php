<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/** A blog author (Blog > Authors). Posts name their author; the author page is /blogs/author/{slug}. */
class Author extends Model
{
    use \App\Models\Concerns\BlankNotNull;

    protected $fillable = ['name', 'slug', 'job_title', 'bio', 'photo', 'linkedin', 'x', 'website', 'expertise', 'sort'];

    protected function casts(): array
    {
        return ['expertise' => 'array', 'sort' => 'integer'];
    }

    protected static function booted(): void
    {
        static::saving(function (Author $a) {
            $a->slug = Str::slug($a->slug ?: $a->name);
        });
        // A renamed author keeps their posts.
        static::updated(function (Author $a) {
            if ($a->wasChanged('name')) Post::query()->where('author', $a->getOriginal('name'))->update(['author' => $a->name]);
        });
    }

    public function path(): string
    {
        return '/blogs/author/'.$this->slug;
    }

    /** Profile links for the page and the schema (sameAs). */
    public function profiles(): array
    {
        return array_values(array_filter([$this->linkedin, $this->x, $this->website], fn ($u) => preg_match('#^https://#', (string) $u)));
    }

    public static function forPost(Post $p): ?self
    {
        return $p->author ? \App\Support\Site\Repo::authors()->firstWhere('name', $p->author) : null;
    }
}
