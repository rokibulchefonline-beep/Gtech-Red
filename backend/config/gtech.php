<?php

return [
    // Shared secret the Next.js website sends (X-Api-Key) when it reads content at build time.
    'api_token' => env('GTECH_API_TOKEN', ''),
    // Public address of the Next.js website, used for CORS and "View site" links.
    'site_url' => env('GTECH_SITE_URL', 'https://www.gtechdigital.co.uk'),
];
