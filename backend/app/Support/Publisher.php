<?php

namespace App\Support;

use App\Models\Setting;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;

/** "Publish site": calls the Cloudflare deploy hook so the website rebuilds with the latest content. */
class Publisher
{
    public static function publish(): void
    {
        $hook = (string) (Setting::group('publish')['deployHook'] ?? '');
        if (! $hook) {
            Notification::make()->title('No deploy hook yet')->body('Add the Cloudflare deploy hook URL in Settings > Publishing first.')->warning()->send();
            return;
        }
        try {
            $res = Http::timeout(20)->post($hook);
            $res->successful()
                ? Notification::make()->title('Publishing started')->body('The website is rebuilding. Changes are live in about 2-4 minutes.')->success()->send()
                : Notification::make()->title('Publish failed')->body('The deploy hook answered '.$res->status().'. Check the URL in Settings.')->danger()->send();
        } catch (\Throwable $e) {
            Notification::make()->title('Publish failed')->body($e->getMessage())->danger()->send();
        }
    }
}
