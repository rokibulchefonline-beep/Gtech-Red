<?php

namespace App\Http\Middleware;

use App\Support\Site\PageCache;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Serves public pages from the full-page cache (App\Support\Site\PageCache) and answers repeat visits with
 * 304 Not Modified when the page has not changed (ETag).
 *
 * Only plain GET requests are cached: addresses with a query string are rendered fresh, except the blog topic
 * filter (?category=), so random search terms cannot fill the cache.
 */
class CacheSitePage
{
    public function handle(Request $request, Closure $next): Response
    {
        // Pages scheduled in the panel go live on the first visit after their time (the scheduler does it too).
        if (\Illuminate\Support\Facades\Cache::add('pages:due-check', 1, 20)) {
            try { \App\Models\Page::publishDue(); } catch (\Throwable $e) { report($e); }
        }
        $query = $request->query();
        $cacheable = PageCache::enabled() && $request->isMethod('GET') && (! $query || array_keys($query) === ['category']);
        if (! $cacheable) return $this->finish($request, $next($request), false);

        $key = PageCache::key($request->getPathInfo().($query ? '?category='.$request->query('category') : ''));
        $hit = PageCache::store()->get($key);
        if ($hit) {
            $res = response($hit['body'], 200, $hit['headers']);
            $res->headers->set('X-Page-Cache', 'HIT');
            return $this->finish($request, $res, true);
        }

        $res = $next($request);
        if ($res->getStatusCode() === 200 && str_contains((string) $res->headers->get('Content-Type'), 'text/html')) {
            $headers = array_filter(['Content-Type' => $res->headers->get('Content-Type'), 'X-Robots-Tag' => $res->headers->get('X-Robots-Tag')]);
            PageCache::store()->put($key, ['body' => $res->getContent(), 'headers' => $headers], PageCache::ttl());
            $res->headers->set('X-Page-Cache', 'MISS');
        }
        return $this->finish($request, $res, true);
    }

    /** Browsers keep the page but check back each time; an unchanged page costs a 304 with no body. */
    private function finish(Request $request, Response $res, bool $etag): Response
    {
        if ($res->getStatusCode() !== 200 || ! str_contains((string) $res->headers->get('Content-Type'), 'text/html')) return $res;
        $res->headers->set('Cache-Control', 'public, max-age=0, must-revalidate');
        if ($etag) {
            $res->setEtag(md5((string) $res->getContent()));
            $res->isNotModified($request);
        }
        return $res;
    }
}
