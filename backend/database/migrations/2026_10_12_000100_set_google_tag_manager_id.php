<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

/** The site's Google Tag Manager container, unless one is already set (Site settings > Tracking). */
return new class extends Migration
{
    public function up(): void
    {
        $row = Setting::query()->find('tracking');
        $value = (array) ($row?->value ?? []);
        if (! empty($value['gtmId'])) return;
        Setting::query()->updateOrCreate(['key' => 'tracking'], ['value' => ['gtmId' => 'GTM-NRPJVVSH'] + $value + ['ga4Id' => '', 'metaPixelId' => '']]);
        \App\Support\Site\PageCache::flush();
    }

    public function down(): void
    {
    }
};
