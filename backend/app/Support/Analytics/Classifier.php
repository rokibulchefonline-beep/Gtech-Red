<?php

namespace App\Support\Analytics;

/** Works out where a visit came from (channel and source), what device it is, and which bots are reading the site. */
class Classifier
{
    public const CHANNELS = ['Search', 'AI', 'Direct', 'Social', 'Referral', 'Paid', 'Email', 'Campaign'];

    /** Referrer host patterns, checked in order: [regex, channel, source]. */
    private const HOSTS = [
        // AI assistants (people clicking a link in an answer)
        ['/(^|\.)(chatgpt\.com|chat\.openai\.com|openai\.com)$/', 'AI', 'ChatGPT'],
        ['/(^|\.)perplexity\.ai$/', 'AI', 'Perplexity'],
        ['/(^|\.)claude\.ai$/', 'AI', 'Claude'],
        ['/(^|\.)(gemini\.google\.com|bard\.google\.com)$/', 'AI', 'Gemini'],
        ['/(^|\.)copilot\.microsoft\.com$|(^|\.)copilot\.cloud\.microsoft$/', 'AI', 'Copilot'],
        ['/(^|\.)(grok\.com|x\.ai)$/', 'AI', 'Grok'],
        ['/(^|\.)meta\.ai$/', 'AI', 'Meta AI'],
        ['/(^|\.)deepseek\.com$/', 'AI', 'DeepSeek'],
        ['/(^|\.)(chat\.mistral\.ai|mistral\.ai)$/', 'AI', 'Mistral'],
        ['/(^|\.)you\.com$/', 'AI', 'You.com'],
        ['/(^|\.)phind\.com$/', 'AI', 'Phind'],
        ['/(^|\.)poe\.com$/', 'AI', 'Poe'],
        // Search engines
        ['/(^|\.)google\.[a-z.]+$/', 'Search', 'Google'],
        ['/(^|\.)bing\.com$/', 'Search', 'Bing'],
        ['/(^|\.)yahoo\.[a-z.]+$/', 'Search', 'Yahoo'],
        ['/(^|\.)duckduckgo\.com$/', 'Search', 'DuckDuckGo'],
        ['/(^|\.)ecosia\.org$/', 'Search', 'Ecosia'],
        ['/(^|\.)search\.brave\.com$/', 'Search', 'Brave Search'],
        ['/(^|\.)yandex\.[a-z.]+$/', 'Search', 'Yandex'],
        ['/(^|\.)baidu\.com$/', 'Search', 'Baidu'],
        ['/(^|\.)startpage\.com$/', 'Search', 'Startpage'],
        ['/(^|\.)qwant\.com$/', 'Search', 'Qwant'],
        // Social networks
        ['/(^|\.)(facebook\.com|fb\.com|fb\.me|m\.facebook\.com|l\.facebook\.com)$/', 'Social', 'Facebook'],
        ['/(^|\.)(instagram\.com|l\.instagram\.com)$/', 'Social', 'Instagram'],
        ['/(^|\.)(linkedin\.com|lnkd\.in)$/', 'Social', 'LinkedIn'],
        ['/(^|\.)(t\.co|twitter\.com|x\.com)$/', 'Social', 'X (Twitter)'],
        ['/(^|\.)tiktok\.com$/', 'Social', 'TikTok'],
        ['/(^|\.)(youtube\.com|youtu\.be)$/', 'Social', 'YouTube'],
        ['/(^|\.)pinterest\.[a-z.]+$|(^|\.)pin\.it$/', 'Social', 'Pinterest'],
        ['/(^|\.)reddit\.com$/', 'Social', 'Reddit'],
        ['/(^|\.)(threads\.net|threads\.com)$/', 'Social', 'Threads'],
        ['/(^|\.)quora\.com$/', 'Social', 'Quora'],
        ['/(^|\.)(whatsapp\.com|wa\.me)$/', 'Social', 'WhatsApp'],
        // Webmail
        ['/(^|\.)(mail\.google\.com|outlook\.live\.com|outlook\.office\.com|mail\.yahoo\.com)$/', 'Email', 'Email'],
    ];

    /** utm_source values (lowercased) that name a known source. */
    private const UTM = [
        'chatgpt.com' => ['AI', 'ChatGPT'], 'chatgpt' => ['AI', 'ChatGPT'], 'openai' => ['AI', 'ChatGPT'], 'perplexity' => ['AI', 'Perplexity'],
        'perplexity.ai' => ['AI', 'Perplexity'], 'claude' => ['AI', 'Claude'], 'claude.ai' => ['AI', 'Claude'], 'gemini' => ['AI', 'Gemini'],
        'copilot' => ['AI', 'Copilot'], 'copilot.com' => ['AI', 'Copilot'], 'grok' => ['AI', 'Grok'], 'grok.com' => ['AI', 'Grok'], 'meta.ai' => ['AI', 'Meta AI'],
        'google' => ['Search', 'Google'], 'bing' => ['Search', 'Bing'], 'yahoo' => ['Search', 'Yahoo'], 'duckduckgo' => ['Search', 'DuckDuckGo'],
        'facebook' => ['Social', 'Facebook'], 'fb' => ['Social', 'Facebook'], 'instagram' => ['Social', 'Instagram'], 'ig' => ['Social', 'Instagram'],
        'linkedin' => ['Social', 'LinkedIn'], 'twitter' => ['Social', 'X (Twitter)'], 'x' => ['Social', 'X (Twitter)'], 'tiktok' => ['Social', 'TikTok'],
        'youtube' => ['Social', 'YouTube'], 'pinterest' => ['Social', 'Pinterest'], 'reddit' => ['Social', 'Reddit'],
        'newsletter' => ['Email', 'Newsletter'], 'mailchimp' => ['Email', 'Mailchimp'], 'brevo' => ['Email', 'Brevo'], 'klaviyo' => ['Email', 'Klaviyo'],
    ];

    /** Bots worth knowing about, by user agent: [regex, name, kind]. "ai" = AI crawlers and assistants fetching pages. */
    private const BOTS = [
        ['/ChatGPT-User/i', 'ChatGPT (live fetch for a user)', 'ai'], ['/OAI-SearchBot/i', 'ChatGPT search', 'ai'], ['/GPTBot/i', 'GPTBot (OpenAI)', 'ai'],
        ['/Claude-User/i', 'Claude (live fetch for a user)', 'ai'], ['/Claude-SearchBot/i', 'Claude search', 'ai'], ['/ClaudeBot|anthropic-ai/i', 'ClaudeBot (Anthropic)', 'ai'],
        ['/Perplexity-User/i', 'Perplexity (live fetch for a user)', 'ai'], ['/PerplexityBot/i', 'PerplexityBot', 'ai'],
        ['/Google-Extended|Google-CloudVertexBot/i', 'Google AI (Gemini)', 'ai'], ['/GoogleOther/i', 'GoogleOther', 'ai'],
        ['/meta-externalagent|meta-externalfetcher|FacebookBot/i', 'Meta AI', 'ai'], ['/Applebot-Extended/i', 'Apple Intelligence', 'ai'],
        ['/xAI|Grok/i', 'Grok (xAI)', 'ai'], ['/DeepSeekBot/i', 'DeepSeek', 'ai'], ['/MistralAI-User/i', 'Mistral', 'ai'],
        ['/Amazonbot/i', 'Amazonbot', 'ai'], ['/Bytespider/i', 'Bytespider (ByteDance)', 'ai'], ['/CCBot/i', 'Common Crawl', 'ai'], ['/cohere-ai/i', 'Cohere', 'ai'],
        ['/Googlebot/i', 'Googlebot', 'search'], ['/bingbot/i', 'Bingbot', 'search'], ['/Applebot/i', 'Applebot', 'search'], ['/DuckDuckBot/i', 'DuckDuckBot', 'search'],
        ['/YandexBot/i', 'YandexBot', 'search'], ['/Baiduspider/i', 'Baiduspider', 'search'],
    ];

    /**
     * @param string $referrer full referrer URL ('' for none)
     * @param array<string,string> $query landing page query parameters
     * @return array{channel:string, source:string, referrer_host:string, click_id:string}
     */
    public static function source(string $referrer, array $query, string $ownHost = ''): array
    {
        $host = strtolower((string) parse_url($referrer, PHP_URL_HOST));
        $host = preg_replace('/^www\./', '', $host);
        if ($ownHost !== '' && ($host === $ownHost || str_ends_with($host, '.'.$ownHost))) $host = ''; // our own site
        $utmSource = strtolower(trim($query['utm_source'] ?? ''));
        $utmMedium = strtolower(trim($query['utm_medium'] ?? ''));
        $click = collect(['gclid', 'gbraid', 'wbraid', 'msclkid', 'fbclid', 'ttclid', 'li_fat_id'])->first(fn ($k) => ! empty($query[$k])) ?? '';
        $out = fn (string $c, string $s) => ['channel' => $c, 'source' => mb_substr($s, 0, 60), 'referrer_host' => $host, 'click_id' => $click];

        // Paid clicks first: ad click ids or a paid medium.
        if (in_array($click, ['gclid', 'gbraid', 'wbraid'], true)) return $out('Paid', 'Google Ads');
        if ($click === 'msclkid') return $out('Paid', 'Microsoft Ads');
        if (preg_match('/^(cpc|ppc|paid|paidsearch|paid_search|paid-social|paidsocial|paid_social|display|cpm|banner)$/', $utmMedium)) {
            return $out('Paid', self::UTM[$utmSource][1] ?? ($utmSource !== '' ? ucfirst($utmSource) : 'Paid'));
        }
        if (in_array($click, ['fbclid', 'ttclid', 'li_fat_id'], true) && $utmSource === '' && $host === '') {
            return $out('Social', ['fbclid' => 'Facebook', 'ttclid' => 'TikTok', 'li_fat_id' => 'LinkedIn'][$click]);
        }
        if ($utmMedium === 'email' || $utmMedium === 'newsletter') return $out('Email', self::UTM[$utmSource][1] ?? ($utmSource !== '' ? ucfirst($utmSource) : 'Email'));
        if ($utmSource !== '') {
            if (isset(self::UTM[$utmSource])) return $out(...self::UTM[$utmSource]);
            if ($utmMedium === 'social') return $out('Social', ucfirst($utmSource));
            return $out('Campaign', $utmSource);
        }
        if ($host !== '') {
            foreach (self::HOSTS as [$re, $channel, $source]) if (preg_match($re, $host)) return $out($channel, $source);
            return $out('Referral', $host);
        }
        return $out('Direct', 'Direct');
    }

    public static function device(string $ua): string
    {
        if (preg_match('/iPad|Tablet|PlayBook|Silk|(Android(?!.*Mobile))/i', $ua)) return 'tablet';
        if (preg_match('/Mobi|iPhone|iPod|Android|Windows Phone|Opera Mini/i', $ua)) return 'mobile';
        return 'desktop';
    }

    public static function browser(string $ua): string
    {
        foreach (['Edg/' => 'Edge', 'OPR/' => 'Opera', 'SamsungBrowser' => 'Samsung', 'Firefox/' => 'Firefox', 'CriOS' => 'Chrome', 'Chrome/' => 'Chrome', 'Safari/' => 'Safari'] as $k => $v) {
            if (str_contains($ua, $k)) return $v;
        }
        return 'Other';
    }

    /** @return array{0:string,1:string}|null [bot name, kind] for crawlers we report on */
    public static function bot(string $ua): ?array
    {
        foreach (self::BOTS as [$re, $name, $kind]) if (preg_match($re, $ua)) return [$name, $kind];
        return null;
    }

    /** Anything automated: never counted as a visit. */
    public static function isBot(string $ua): bool
    {
        return $ua === '' || self::bot($ua) !== null || (bool) preg_match('/bot|crawl|spider|slurp|headless|lighthouse|pingdom|uptime|monitor|curl|wget|python|httpclient|java\/|go-http|axios|node-fetch|preview|facebookexternalhit|embedly|whatsapp|telegram|discord|skype/i', $ua);
    }
}
