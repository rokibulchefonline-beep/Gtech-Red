<?php

namespace App\View;

use App\Models\Industry;
use App\Models\ServiceGroup;
use App\Models\Setting;
use Illuminate\View\View;

/** Shared data for every public page: settings, the services menu, industries and form options. */
class SiteComposer
{
    private const SOCIAL_ICONS = ['LinkedIn' => 'simple-icons:linkedin', 'Facebook' => 'simple-icons:facebook', 'Instagram' => 'simple-icons:instagram', 'X' => 'simple-icons:x',
        'YouTube' => 'simple-icons:youtube', 'TikTok' => 'simple-icons:tiktok', 'Pinterest' => 'simple-icons:pinterest'];

    private static ?array $data = null;
    private static ?int $version = null;

    public function compose(View $view): void
    {
        // Loaded once per content version, so a saved menu, industry or setting shows straight away.
        $v = \App\Support\Site\PageCache::version();
        if (self::$data === null || self::$version !== $v) { self::$data = self::load(); self::$version = $v; }
        $view->with(self::$data);
    }

    private static function load(): array
    {
        $s = Setting::all_();
        $socials = array_values(array_map(fn ($x) => $x + ['icon' => self::SOCIAL_ICONS[$x['name']] ?? 'lucide:link'], array_filter($s['socials'] ?? [], fn ($x) => ! empty($x['url']))));
        $safe = fn (string $v, string $re) => preg_match($re, $v) ? $v : '';
        return [
            'site' => [
                'name' => $s['general']['siteName'] ?? 'GTech Digital', 'tagline' => $s['general']['tagline'] ?? '',
                'email' => $s['contact']['email'] ?? '', 'phone' => $s['contact']['phone'] ?? '', 'socials' => $socials,
                'gtm' => $safe((string) ($s['tracking']['gtmId'] ?? ''), '/^GTM-[A-Z0-9]+$/'), 'ga4' => $safe((string) ($s['tracking']['ga4Id'] ?? ''), '/^G-[A-Z0-9]+$/'),
                'pixel' => $safe((string) ($s['tracking']['metaPixelId'] ?? ''), '/^\d{5,20}$/'),
            ],
            'contact' => \App\Support\Site\Contact::get(),
            'menu' => ServiceGroup::query()->with('items')->orderBy('sort')->get(),
            'industryList' => Industry::query()->orderBy('sort')->get(),
            'budgets' => $s['forms']['budgets'] ?? [],
            // Under every form: the privacy notice ([text](/link) becomes a link), and Turnstile when set up.
            'formNotice' => self::notice((string) ($s['forms']['privacyNotice'] ?? '')),
            'turnstile' => $safe((string) ($s['forms']['turnstileSite'] ?? ''), '/^[0-9A-Za-z_-]{10,100}$/'),
        ];
    }

    private static function notice(string $text): string
    {
        $html = e(trim($text));
        return preg_replace_callback('/\[([^\]]+)\]\(((?:https?:\/\/|\/)[^)\s]*)\)/', fn ($m) => '<a href="'.$m[2].'">'.$m[1].'</a>', $html);
    }

    /** Forget cached data (after saving in the panel, or between tests). */
    public static function flush(): void
    {
        self::$data = null;
    }
}
