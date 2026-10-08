<?php

namespace App\Console\Commands;

use App\Support\Mail\Inbox;
use Illuminate\Console\Command;

/** Copies new email from the IMAP mailbox into the Email dashboard's Inbox (scheduled every five minutes). */
class FetchMail extends Command
{
    protected $signature = 'gtech:fetch-mail';
    protected $description = 'Fetch new email into the Email dashboard inbox';

    public function handle(): int
    {
        if (! Inbox::enabled()) { $this->line('Inbox not set up.'); return self::SUCCESS; }
        try {
            $this->info(Inbox::fetch().' new message(s).');
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }
        return self::SUCCESS;
    }
}
