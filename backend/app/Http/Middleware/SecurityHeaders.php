<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Standard protective headers on every response: no framing by other sites (clickjacking), no MIME sniffing,
 * a privacy-friendly referrer, no camera/microphone/location access, and HSTS once the site runs on HTTPS.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        $h = $response->headers;
        $h->set('X-Content-Type-Options', 'nosniff', false);
        $h->set('X-Frame-Options', 'SAMEORIGIN', false);
        $h->set('Referrer-Policy', 'strict-origin-when-cross-origin', false);
        $h->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()', false);
        if ($request->isSecure()) $h->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains', false);
        if (function_exists('header_remove')) @header_remove('X-Powered-By');
        return $response;
    }
}
