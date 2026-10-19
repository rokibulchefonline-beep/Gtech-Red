<?php

namespace App\Console\Commands;

use App\Models\CaseStudy;
use App\Support\Site\PageCache;
use App\Support\Site\Repo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Takes real screenshots of each client's website (desktop and mobile home page) and adds them to the gallery of
 * that client's case studies. Uses the free WordPress.com mShots service, so the server needs internet access; a
 * fresh screenshot can take a minute to be ready, so the command waits and retries. Clients without a website
 * address (Case studies > Client > Website) are skipped. Run it again to refresh: images are replaced in place.
 */
class CaseScreenshots extends Command
{
    protected $signature = 'gtech:case-screenshots {--client= : Only this client name} {--tries=8 : Attempts per screenshot (10 seconds apart)}';

    protected $description = 'Add real website screenshots to the case study galleries';

    private const SHOTS = ['desktop' => ['w' => 1280, 'h' => 800, 'vpw' => 1280, 'vph' => 800, 'label' => 'website home page'],
        'mobile' => ['w' => 390, 'h' => 844, 'vpw' => 390, 'vph' => 844, 'label' => 'mobile home page']];

    public function handle(): int
    {
        $byClient = CaseStudy::query()->where('website', '!=', '')->whereNotNull('website')->get()->groupBy('client');
        if ($c = $this->option('client')) $byClient = $byClient->only([$c]);
        if ($byClient->isEmpty()) { $this->warn('No case studies with a website address. Add one under Case studies > Client > Website.'); return self::SUCCESS; }

        $dir = public_path('case/shots');
        if (! is_dir($dir)) mkdir($dir, 0755, true);

        foreach ($byClient as $client => $rows) {
            $url = $rows->first()->website;
            $new = [];
            foreach (self::SHOTS as $kind => $o) {
                $this->line("$client: $kind screenshot of $url ...");
                $img = $this->fetch($url, $o);
                if (! $img) { $this->error("  not ready or failed, try again later"); continue; }
                $file = 'case/shots/'.Str::slug($client)."-$kind.".$this->save($img, public_path('case/shots/'.Str::slug($client)."-$kind"));
                $new[] = ['src' => '/'.$file.'?v='.time(), 'alt' => "$client {$o['label']} screenshot", 'caption' => "$client {$o['label']}"];
                $this->info('  saved '.$file);
            }
            if (! $new) continue;
            foreach ($rows as $row) {
                $keep = collect((array) $row->gallery)->reject(fn ($g) => str_starts_with($g['src'] ?? '', '/case/shots/'.Str::slug($client).'-'))->values()->all();
                $row->update(['gallery' => array_merge($new, $keep)]);
            }
        }

        PageCache::flush();
        Repo::flush();

        return self::SUCCESS;
    }

    /** mShots answers with a "generating" placeholder (or a redirect to one) until the screenshot is ready. */
    private function fetch(string $url, array $o): ?string
    {
        $api = 'https://s.wordpress.com/mshots/v1/'.rawurlencode($url).'?'.http_build_query(['w' => $o['w'], 'h' => $o['h'], 'vpw' => $o['vpw'], 'vph' => $o['vph']]);
        for ($i = 0; $i < (int) $this->option('tries'); $i++) {
            try {
                $r = Http::timeout(60)->withoutRedirecting()->get($api);
                if ($r->successful() && str_starts_with((string) $r->header('Content-Type'), 'image/jpeg') && strlen($r->body()) > 5000) return $r->body();
            } catch (\Throwable $e) {
                $this->warn('  '.$e->getMessage());
            }
            sleep(10);
        }

        return null;
    }

    /** Saves as WebP when GD can, otherwise as the JPEG received. Returns the extension. */
    private function save(string $jpeg, string $base): string
    {
        if (function_exists('imagewebp') && ($im = @imagecreatefromstring($jpeg))) {
            imagewebp($im, "$base.webp", 82);
            imagedestroy($im);
            return 'webp';
        }
        file_put_contents("$base.jpg", $jpeg);

        return 'jpg';
    }
}
