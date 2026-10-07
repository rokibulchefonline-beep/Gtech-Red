<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

/** GTech's email and phone numbers (Site settings > Contact). */
return new class extends Migration
{
    public function up(): void
    {
        $value = (array) (Setting::query()->find('contact')?->value ?? []);
        Setting::query()->updateOrCreate(['key' => 'contact'], ['value' => array_merge($value, [
            'email' => 'info@gtechdigital.co.uk', 'phone' => '0330 380 1000', 'phone2' => '0203 598 5956',
        ])]);
        \App\Support\Site\PageCache::flush();
    }

    public function down(): void
    {
    }
};
