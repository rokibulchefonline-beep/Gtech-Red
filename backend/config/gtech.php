<?php

return [
    // The website's address, used for "View on website" links in the panel.
    'site_url' => env('GTECH_SITE_URL', 'https://www.gtechdigital.co.uk'),
    // The website's public address, used in share links, canonical URLs and structured data.
    'public_url' => rtrim(env('GTECH_PUBLIC_URL', 'https://www.gtechdigital.co.uk'), '/'),
    // true on the live site. false on a local or test copy, whose pages are sent with noindex so Google ignores them.
    'blade_live' => (bool) env('GTECH_BLADE_LIVE', false),
    // Full-page cache for the public pages. Pages refresh by themselves when content is saved in the panel.
    'page_cache' => [
        'enabled' => (bool) env('GTECH_PAGE_CACHE', true),
        'store' => env('GTECH_PAGE_CACHE_STORE'),            // empty = the default CACHE_STORE
        'ttl' => (int) env('GTECH_PAGE_CACHE_TTL', 43200),    // seconds; 12 hours
    ],
];
