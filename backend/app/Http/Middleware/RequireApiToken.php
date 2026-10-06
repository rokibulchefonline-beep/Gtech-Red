<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/** Read access for the website build: the request must carry X-Api-Key = GTECH_API_TOKEN. */
class RequireApiToken
{
    public function handle(Request $request, Closure $next)
    {
        $token = (string) config('gtech.api_token');
        if ($token === '' || ! hash_equals($token, (string) $request->header('X-Api-Key'))) {
            return response()->json(['ok' => false, 'error' => 'Unauthorised'], 401);
        }
        return $next($request);
    }
}
