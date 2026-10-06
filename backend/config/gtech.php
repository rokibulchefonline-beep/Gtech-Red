<?php

return [
    // Shared secret the Next.js website sends (X-Api-Key) when it reads content at build time.
    'api_token' => env('GTECH_API_TOKEN', ''),
    // Public address of the Next.js website, used for CORS and "View site" links.
    'site_url' => env('GTECH_SITE_URL', 'https://www.gtechdigital.co.uk'),
    // The website's public address, used in share links, canonical URLs and structured data.
    'public_url' => rtrim(env('GTECH_PUBLIC_URL', 'https://www.gtechdigital.co.uk'), '/'),
    // Set to true when the Blade pages replace the Next.js website. Until then they are sent with noindex.
    'blade_live' => (bool) env('GTECH_BLADE_LIVE', false),
];
