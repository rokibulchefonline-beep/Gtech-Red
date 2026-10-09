<?php

use App\Models\Page;
use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// The site no longer uses Cloudflare: drop the Turnstile keys and the old deploy hook, and update the privacy policy
// wording about form spam protection (only where it still has the original sentence).
return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')->where('key', 'publish')->delete();
        $forms = Setting::group('forms');
        if (array_key_exists('turnstileSite', $forms) || array_key_exists('turnstileSecret', $forms)) {
            unset($forms['turnstileSite'], $forms['turnstileSecret']);
            Setting::put('forms', $forms);
        }
        $old = 'Our enquiry forms may use Cloudflare Turnstile to check that a real person is sending the form. It processes technical data such as your IP address for this purpose only.';
        $new = 'Our enquiry forms may use Google reCAPTCHA to check that a real person is sending the form. It processes technical data such as your IP address and browser details for this purpose only.';
        foreach (Page::query()->where('kind', 'legal')->get() as $p) {
            $json = json_encode($p->sections);
            $fixed = str_replace(trim(json_encode($old), '"'), trim(json_encode($new), '"'), $json);
            if ($fixed !== $json) { $p->sections = json_decode($fixed, true); $p->save(); }
        }
        \App\Support\Site\PageCache::flush();
    }

    public function down(): void {}
};
