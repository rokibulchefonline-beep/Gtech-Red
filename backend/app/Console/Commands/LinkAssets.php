<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * While the Next.js site is still live, the Blade site shares its files instead of copying 40 MB into Git:
 * every image/video folder in ../public and the stylesheet (../app/globals.css) are linked into backend/public.
 */
class LinkAssets extends Command
{
    protected $signature = 'gtech:link-assets {--source= : The website folder (default: the folder above backend)}';

    protected $description = 'Link the website images, videos and stylesheet into public/';

    public function handle(): int
    {
        $root = rtrim($this->option('source') ?: dirname(base_path()), '/');
        if (! is_dir("$root/public")) { $this->error("No public folder in $root"); return self::FAILURE; }
        $made = 0;
        foreach (scandir("$root/public") as $f) {
            if ($f === '.' || $f === '..' || in_array($f, ['robots.txt', 'favicon.ico'], true)) continue;
            $made += $this->link("$root/public/$f", public_path($f));
        }
        @mkdir(public_path('css'), 0755, true);
        $made += $this->link("$root/app/globals.css", public_path('css/site.css'));
        $this->info("Linked $made item(s).");
        return self::SUCCESS;
    }

    /** Relative link targets keep working when the project folder is moved or deployed elsewhere. */
    private static function relative(string $from, string $to): string
    {
        $a = explode('/', trim(dirname($to), '/'));
        $b = explode('/', trim($from, '/'));
        while ($a && $b && $a[0] === $b[0]) { array_shift($a); array_shift($b); }
        return str_repeat('../', count($a)).implode('/', $b);
    }

    private function link(string $from, string $to): int
    {
        $from = self::relative(realpath($from) ?: $from, $to);
        if (is_link($to)) { if (readlink($to) === $from) return 0; unlink($to); }
        elseif (file_exists($to)) { $this->warn("Skipped (already exists): $to"); return 0; }
        return symlink($from, $to) ? 1 : 0;
    }
}
