<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blade templates are written on several lines for readability; React output has no whitespace between tags.
 * Line-break whitespace between tags is removed so inline elements sit exactly as on the old site. Spaces written
 * on the same line are kept (they are deliberate).
 * Before go-live (GTECH_BLADE_LIVE=false) the pages are also marked noindex.
 */
class MinifySiteHtml
{
    public function handle(Request $request, Closure $next): Response
    {
        $res = $next($request);
        $type = (string) $res->headers->get('Content-Type');
        if ($res->isSuccessful() && str_contains($type, 'text/html') && method_exists($res, 'getContent')) {
            $html = (string) $res->getContent();
            $html = preg_replace('/>\s*\n\s*</', '><', $html);
            $html = \App\Support\Site\ImageDims::add($html);
            $res->setContent(trim($html));
            // Keep search engines off the Blade pages until they go live, so they never compete with the website.
            if (! config('gtech.blade_live')) $res->headers->set('X-Robots-Tag', 'noindex, nofollow');
        }
        return $res;
    }
}
