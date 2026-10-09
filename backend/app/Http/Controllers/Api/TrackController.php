<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsVisit;
use App\Support\Analytics\Classifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Page views from the website's own script (public/js/site.js). Two kinds of message:
 *  - a page view {sid, p, r, q} -> creates the visit on its first page and records the view; returns its id
 *  - time on page {sid, pv, sec}, sent when the visitor leaves the page
 * No cookies. sid is a random id kept only for the browser tab; the visitor id is a daily hash of IP and browser.
 */
class TrackController extends Controller
{
    public function __invoke(Request $request)
    {
        $d = json_decode($request->getContent(), true);
        $ua = (string) $request->userAgent();
        $sid = is_array($d) ? (string) ($d['sid'] ?? '') : '';
        if (! preg_match('/^[a-f0-9]{32}$/', $sid) || Classifier::isBot($ua)) return response()->noContent();

        if (isset($d['pv'], $d['sec'])) return $this->time($sid, (int) $d['pv'], (int) $d['sec']);

        $path = mb_substr((string) ($d['p'] ?? ''), 0, 300);
        if (! str_starts_with($path, '/') || str_starts_with($path, '/admin') || str_starts_with($path, '/api')) return response()->noContent();
        $now = now();

        $visit = AnalyticsVisit::query()->where('sid', $sid)->first();
        if (! $visit) {
            parse_str(ltrim((string) ($d['q'] ?? ''), '?'), $q);
            $q = array_map(fn ($v) => is_string($v) ? mb_substr($v, 0, 150) : '', $q);
            $referrer = mb_substr((string) ($d['r'] ?? ''), 0, 500);
            $own = preg_replace('/^www\./', '', strtolower((string) parse_url(config('gtech.public_url'), PHP_URL_HOST)));
            $src = Classifier::source($referrer, $q, $own);
            $visit = AnalyticsVisit::query()->create([
                'sid' => $sid, 'started_at' => $now, 'last_seen_at' => $now, 'landing_path' => $path,
                'visitor' => substr(hash('sha256', $request->ip().'|'.$ua.'|'.$now->format('Y-m-d').'|'.config('app.key')), 0, 16),
                'channel' => $src['channel'], 'source' => $src['source'], 'referrer_host' => mb_substr($src['referrer_host'], 0, 120),
                'referrer' => $src['referrer_host'] !== '' ? $referrer : '', 'click_id' => $src['click_id'],
                'utm_source' => mb_substr($q['utm_source'] ?? '', 0, 100), 'utm_medium' => mb_substr($q['utm_medium'] ?? '', 0, 100),
                'utm_campaign' => $q['utm_campaign'] ?? '', 'utm_term' => $q['utm_term'] ?? '', 'utm_content' => $q['utm_content'] ?? '',
                'device' => Classifier::device($ua), 'browser' => Classifier::browser($ua), 'country' => \App\Support\Analytics\Geo::country($request),
            ]);
        }
        $pv = DB::table('analytics_pageviews')->insertGetId(['visit_id' => $visit->id, 'path' => $path, 'viewed_at' => $now]);
        AnalyticsVisit::query()->whereKey($visit->id)->update(['pageviews' => DB::raw('pageviews + 1'), 'last_seen_at' => $now]);

        if (random_int(1, 500) === 1) self::prune();
        return response()->json(['pv' => $pv]);
    }

    private function time(string $sid, int $pv, int $sec)
    {
        $sec = max(0, min($sec, 1800));
        $row = DB::table('analytics_pageviews as p')->join('analytics_visits as v', 'v.id', '=', 'p.visit_id')
            ->where('p.id', $pv)->where('v.sid', $sid)->first(['p.id', 'p.seconds', 'p.visit_id']);
        if ($row && $sec > $row->seconds) {
            DB::table('analytics_pageviews')->where('id', $row->id)->update(['seconds' => $sec]);
            DB::table('analytics_visits')->where('id', $row->visit_id)->increment('seconds', $sec - $row->seconds);
        }
        return response()->noContent();
    }

    /** Keep 13 months. */
    public static function prune(): void
    {
        $cut = now()->subMonths(13);
        DB::table('analytics_pageviews')->where('viewed_at', '<', $cut)->delete();
        DB::table('analytics_visits')->where('started_at', '<', $cut)->delete();
        DB::table('analytics_bot_hits')->where('hit_at', '<', $cut)->delete();
    }
}
