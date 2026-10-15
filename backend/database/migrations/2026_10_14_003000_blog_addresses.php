<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// The blog moves from /blogs to /blog (the address the current website uses). Saved paths follow: SEO overrides,
// schema rules, redirects and links written inside pages, posts and case studies. Analytics history is converted too,
// so reports stay continuous.
return new class extends Migration
{
    public function up(): void
    {
        $fix = fn (string $v) => preg_replace('#(?<![A-Za-z0-9-])/blogs(?=/|\b|$)#', '/blog', $v);
        foreach (['seo_entries' => ['path'], 'schema_rules' => ['path'], 'redirects' => ['from_path', 'to_path'],
            'analytics_pageviews' => ['path'], 'analytics_visits' => ['landing_path'], 'leads' => ['form_path', 'landing_path']] as $table => $cols) {
            if (! \Illuminate\Support\Facades\Schema::hasTable($table)) continue;
            foreach ($cols as $c) {
                if (! \Illuminate\Support\Facades\Schema::hasColumn($table, $c)) continue;
                DB::table($table)->where($c, 'like', '%/blogs%')->orderBy('id')->each(function ($r) use ($table, $c, $fix) {
                    DB::table($table)->where('id', $r->id)->update([$c => $fix((string) $r->{$c})]);
                });
            }
        }
        // A redirect from /blog/x to /blog/x would now loop: remove those.
        if (\Illuminate\Support\Facades\Schema::hasTable('redirects')) DB::table('redirects')->whereColumn('from_path', 'to_path')->delete();

        // Links inside content (JSON or HTML columns).
        foreach (['pages' => ['hero', 'sections', 'faqs', 'data'], 'posts' => ['body', 'excerpt'], 'case_studies' => ['body', 'sections']] as $table => $cols) {
            if (! \Illuminate\Support\Facades\Schema::hasTable($table)) continue;
            $cols = array_values(array_filter($cols, fn ($c) => \Illuminate\Support\Facades\Schema::hasColumn($table, $c)));
            $key = $table === 'pages' ? 'key' : 'id';
            foreach (DB::table($table)->get(array_merge([$key], $cols)) as $r) {
                $up = [];
                foreach ($cols as $c) {
                    $v = (string) $r->{$c};
                    if (! str_contains($v, '/blogs')) continue;
                    // Plain links (/blogs/x) and links inside JSON, where / is written \/ (\/blogs\/x).
                    $n = preg_replace('#(?<=["\'(\s>])/blogs(?=["\'/?\#)\s]|$)#', '/blog', $v);
                    $n = preg_replace('#(?<=["\'(\s>])\\\\/blogs(?=\\\\/|["\'?\#)\s])#', '\\\\/blog', $n);
                    if ($n !== $v) $up[$c] = $n;
                }
                if ($up) DB::table($table)->where($key, $r->{$key})->update($up);
            }
        }
        \App\Support\Site\PageCache::flush();
    }

    public function down(): void {}
};
