<?php

use App\Models\Post;
use Illuminate\Database\Migrations\Migration;

/** SEO titles that fit in Google's ~60 characters for the two posts whose headline is longer (only if none is set). */
return new class extends Migration
{
    public function up(): void
    {
        foreach ([
            'aeo-geo-guide' => 'AEO and GEO: How to Get Your Business Into AI Answers',
            'social-media-strategy' => 'How to Build a Social Media Strategy That Drives Sales',
        ] as $slug => $title) {
            $p = Post::query()->where('slug', $slug)->first();
            if ($p && ! $p->meta_title) $p->forceFill(['meta_title' => $title])->saveQuietly();
        }
        \App\Support\Site\PageCache::flush();
    }

    public function down(): void
    {
    }
};
