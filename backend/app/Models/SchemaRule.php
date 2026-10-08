<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Additional schema (SEO > Additional schema): JSON-LD added to every page, or to every page of one type (all blog
 * posts, all service pages...), or to one address. {url}, {name}, {description} and {testimonials} are filled in.
 */
class SchemaRule extends Model
{
    protected $fillable = ['name', 'scope', 'path', 'json', 'active', 'sort'];

    public const SCOPES = [
        'all' => 'Every page of the website',
        'home' => 'Home page',
        'main' => 'Main pages (About, Contact, Services, Industries, Case studies, Blog)',
        'services' => 'All service pages (/services/…)',
        'industries' => 'All industry pages (/industries/…)',
        'posts' => 'All blog posts (/blogs/…)',
        'authors' => 'All blog author pages',
        'case_studies' => 'All case studies (/case-studies/…)',
        'legal' => 'Legal pages (terms, privacy, cookies)',
        'prefix' => 'Every address starting with…',
        'path' => 'One page (exact address)',
    ];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::saved(fn () => \App\Support\Site\PageCache::flush());
        static::deleted(fn () => \App\Support\Site\PageCache::flush());
    }

    public function matches(string $path): bool
    {
        $path = '/'.trim($path, '/');
        $own = '/'.trim((string) $this->path, '/');
        return match ($this->scope) {
            'all' => true,
            'home' => $path === '/',
            'main' => in_array($path, ['/about', '/contact', '/services', '/industries', '/case-studies', '/blogs'], true),
            'services' => str_starts_with($path, '/services/'),
            'industries' => str_starts_with($path, '/industries/'),
            'posts' => str_starts_with($path, '/blogs/') && ! str_starts_with($path, '/blogs/author/') && ! str_starts_with($path, '/blogs/page/'),
            'authors' => str_starts_with($path, '/blogs/author/'),
            'case_studies' => str_starts_with($path, '/case-studies/'),
            'legal' => in_array($path, ['/terms', '/privacy-policy', '/cookie-policy'], true),
            'prefix' => $own !== '/' && ($path === $own || str_starts_with($path, rtrim($own, '/').'/')),
            'path' => $path === $own,
            default => false,
        };
    }

    /** The rule's JSON-LD for a page, with the placeholders filled in; null when it is not valid. */
    public function render(string $url, string $name, string $description): mixed
    {
        $fill = fn (string $s) => strtr($s, ['{url}' => $url, '{name}' => $name, '{description}' => $description]);
        $data = json_decode((string) $this->json, true);
        if (! is_array($data)) return null;
        array_walk_recursive($data, function (&$v) use ($fill) { if (is_string($v)) $v = $fill($v); });
        $data = self::testimonials($data);
        return $data;
    }

    /** "{testimonials}" anywhere in the JSON becomes a list of Review items from Site structure > Testimonials. */
    private static function testimonials(mixed $data): mixed
    {
        if ($data === '{testimonials}') {
            return Testimonial::query()->where('visible', true)->orderBy('sort')->get()->map(fn (Testimonial $t) => array_filter([
                '@type' => 'Review', 'author' => ['@type' => 'Person', 'name' => $t->name], 'name' => $t->title ?: null, 'reviewBody' => strip_tags((string) $t->text),
                'reviewRating' => ['@type' => 'Rating', 'ratingValue' => '5', 'bestRating' => '5'],
            ]))->values()->all();
        }
        if (is_array($data)) foreach ($data as $k => $v) $data[$k] = self::testimonials($v);
        return $data;
    }

    /** JSON-LD of every active rule that applies to $path. */
    public static function forPage(string $path, string $url, string $name, string $description): array
    {
        try {
            $rules = self::query()->where("active", true)->orderBy("sort")->orderBy("id")->get();
        } catch (\Throwable) {
            return [];
        }
        return $rules->filter->matches($path)->map->render($url, $name, $description)->filter()->values()->all();
    }
}
