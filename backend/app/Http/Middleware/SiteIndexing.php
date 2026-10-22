<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps search engines out of every copy of the site that is not the live domain (test, staging, local): each
 * response, images and files included, says "noindex". Google removes pages it already has the next time it
 * crawls them. Set in SEO > Search engines (see Crawl); SITE_INDEXING=on|off in .env overrides the admin.
 */
class SiteIndexing
{
    public static function allowed(?Request $request = null): bool
    {
        return \App\Support\Site\Crawl::indexable(($request ?? request())->getHost());
    }

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        if (! self::allowed($request)) $response->headers->set('X-Robots-Tag', 'noindex, nofollow');

        return $response;
    }
}
