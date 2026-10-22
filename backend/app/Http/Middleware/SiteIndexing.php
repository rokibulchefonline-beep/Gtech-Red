<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps search engines out of every copy of the site that is not the live domain (test, staging, local): each
 * response, images and files included, says "noindex". Google removes pages it already has the next time it
 * crawls them. The live domains are in config/gtech.php (indexing); SITE_INDEXING=on|off in .env overrides.
 */
class SiteIndexing
{
    public static function allowed(?Request $request = null): bool
    {
        $mode = strtolower((string) config('gtech.indexing.mode'));
        if (in_array($mode, ['on', 'true', '1'], true)) return true;
        if (in_array($mode, ['off', 'false', '0'], true)) return false;
        $host = strtolower(($request ?? request())->getHost());

        return in_array($host, (array) config('gtech.indexing.hosts'), true);
    }

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        if (! self::allowed($request)) $response->headers->set('X-Robots-Tag', 'noindex, nofollow');

        return $response;
    }
}
