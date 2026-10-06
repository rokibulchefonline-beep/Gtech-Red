<?php

namespace App\Http\Middleware;

use App\Models\Redirect;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * When an address is not found, send the visitor to its new address if one is recorded (Website content >
 * Redirects). Live pages always win: a redirect is only used for addresses that would otherwise be a 404.
 */
class FollowRedirects
{
    public function handle(Request $request, Closure $next): Response
    {
        $res = $next($request);
        if ($res->getStatusCode() !== 404 || ! in_array($request->getMethod(), ['GET', 'HEAD'], true) || str_starts_with($request->path(), 'admin')) return $res;
        try {
            $r = Redirect::query()->where('from_path', Redirect::normalise($request->getPathInfo()))->first();
        } catch (\Throwable) {
            return $res; // e.g. table not migrated yet
        }
        if (! $r) return $res;
        $r->timestamps = false;
        $r->forceFill(['hits' => $r->hits + 1, 'last_hit_at' => now()])->saveQuietly();
        $to = $r->to_path.($request->getQueryString() && ! str_contains($r->to_path, '?') ? '?'.$request->getQueryString() : '');
        return redirect($to, in_array($r->status_code, [301, 302, 307, 308], true) ? $r->status_code : 301);
    }
}
