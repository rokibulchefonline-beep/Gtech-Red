<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * One address per page: /services/local-seo/ (trailing slash), /Services/Local-SEO (capitals) and /index.php all
 * redirect permanently to the clean lowercase address, so Google never sees duplicates. Files (anything with an
 * extension), the panel and the API are left alone.
 */
class CanonicalUrl
{
    private const SKIP = '#^/(admin|api|livewire|filament|storage|build|preview|up|_)#i';

    public function handle(Request $request, Closure $next): Response
    {
        if (! in_array($request->getMethod(), ['GET', 'HEAD'], true)) return $next($request);
        $uri = $request->getRequestUri();
        [$rawPath, $query] = array_pad(explode('?', $uri, 2), 2, '');
        $path = rawurldecode($rawPath);

        $clean = preg_replace('#^/index\.php(?=/|$)#i', '', $path) ?: '/';
        if (! preg_match(self::SKIP, $clean) && ! preg_match('#\.[a-z0-9]{1,5}$#i', $clean)) {
            if ($clean !== '/') $clean = rtrim($clean, '/') ?: '/';
            $clean = preg_replace('#/{2,}#', '/', $clean);
            $clean = mb_strtolower($clean);
        }
        // A relative address: right behind any proxy or port, and never prefixed with /index.php again.
        if ($clean !== $path) return new \Symfony\Component\HttpFoundation\RedirectResponse($clean.($query !== '' ? "?$query" : ''), 301);
        return $next($request);
    }
}
