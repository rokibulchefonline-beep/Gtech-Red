<?php

use App\Models\Client;
use Illuminate\Database\Migrations\Migration;

/**
 * Real client logos: the logos loaded from another agency's website are removed, and five real clients are added
 * (skipped when a client with the same name already exists, so admin edits are kept).
 */
return new class extends Migration
{
    private const CLIENTS = [
        ['Pride of Asia', '/clients/pride-of-asia.png', ''],
        ['Asian Restaurant & Takeaway Awards', '/clients/arta.png', ''],
        ['TableBookings.co.uk', '/clients/tablebookings.png', 'https://www.tablebookings.co.uk'],
        ['Salik & Co', '/clients/salik-and-co.png', ''],
        ['ChefOnline', '/clients/chefonline.png', 'https://www.chefonline.co.uk'],
    ];

    public function up(): void
    {
        Client::query()->where('logo', 'like', '%growmemarketing.ca%')->delete();
        foreach (self::CLIENTS as $i => [$name, $logo, $url]) {
            Client::query()->firstOrCreate(['name' => $name], ['logo' => $logo, 'url' => $url, 'order' => ($i + 1) * 10, 'visible' => true]);
        }
        \App\Support\Site\PageCache::flush();
        \App\Support\Site\Repo::flush();
    }

    public function down(): void
    {
    }
};
