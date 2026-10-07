<?php

namespace App\Http\Middleware;

use App\Support\Analytics\Classifier;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/** Records AI and search crawlers reading public pages (they never run the page-view script). */
class RecordBotHits
{
    public function handle(Request $request, Closure $next): Response
    {
        $res = $next($request);
        if ($request->isMethod('GET') && $res->getStatusCode() === 200 && ($bot = Classifier::bot((string) $request->userAgent()))) {
            try {
                DB::table('analytics_bot_hits')->insert(['bot' => $bot[0], 'kind' => $bot[1], 'path' => mb_substr($request->getPathInfo(), 0, 300), 'hit_at' => now()]);
            } catch (\Throwable) {
                // never break a page for analytics
            }
        }
        return $res;
    }
}
