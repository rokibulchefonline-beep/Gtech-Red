<?php

namespace App\Console\Commands;

use App\Support\Seo\Crawler;
use Illuminate\Console\Command;

/** Crawls the whole website and checks every link (SEO > SEO audit > Site health). Scheduled every night. */
class CrawlSite extends Command
{
    protected $signature = 'gtech:crawl {--alert : Email new broken links to the lead alert address}';
    protected $description = 'Crawl the website: error pages, broken links, inbound and outbound links';

    public function handle(): int
    {
        $run = (new Crawler())->run($this->option('alert') ? 'nightly' : 'manual');
        if ($run->status !== 'done') { $this->error('Crawl failed: '.$run->message); return self::FAILURE; }
        $this->info("{$run->pages} pages, {$run->links} links, {$run->broken} broken, {$run->errors} error pages, {$run->redirects} redirects ({$run->seconds}s).");
        if ($this->option('alert')) Crawler::alert($run);
        return self::SUCCESS;
    }
}
