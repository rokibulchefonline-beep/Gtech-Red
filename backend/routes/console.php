<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Needs the server's cron to run "php artisan schedule:run" every minute (see README).
\Illuminate\Support\Facades\Schedule::command('leads:remind')->weekdays()->at('08:00')->timezone('Europe/London');
\Illuminate\Support\Facades\Schedule::call(fn () => \App\Models\Page::publishDue())->name('pages:publish-due')->everyMinute();
// Lost leads older than the retention period in Site settings > Leads (off unless set).
\Illuminate\Support\Facades\Schedule::call(fn () => \App\Support\Crm\Gdpr::purgeOld())->name('leads:purge-old')->dailyAt('03:15');
