<?php

namespace App\Support\Analytics;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/** All numbers on the Analytics screen for one period, optionally narrowed to a channel, a source or a page. */
class Report
{
    public Carbon $from;
    public Carbon $to;

    public function __construct(public int $days = 30, public string $channel = '', public string $source = '', public string $page = '')
    {
        $this->to = now();
        $this->from = $days <= 1 ? now()->startOfDay() : now()->subDays($days - 1)->startOfDay();
    }

    /** Visits in the period, after the filters. A page filter keeps visits that viewed that page. */
    public function visits(): Builder
    {
        return DB::table('analytics_visits as v')->whereBetween('v.started_at', [$this->from, $this->to])
            ->when($this->channel !== '', fn ($q) => $q->where('v.channel', $this->channel))
            ->when($this->source !== '', fn ($q) => $q->where('v.source', $this->source))
            ->when($this->page !== '', fn ($q) => $q->whereExists(fn ($e) => $e->from('analytics_pageviews as x')->whereColumn('x.visit_id', 'v.id')->where('x.path', $this->page)));
    }

    /** Page views of the filtered visits (only the chosen page when a page filter is set). */
    public function views(): Builder
    {
        return DB::table('analytics_pageviews as p')->join('analytics_visits as v', 'v.id', '=', 'p.visit_id')
            ->whereBetween('v.started_at', [$this->from, $this->to])
            ->when($this->channel !== '', fn ($q) => $q->where('v.channel', $this->channel))
            ->when($this->source !== '', fn ($q) => $q->where('v.source', $this->source))
            ->when($this->page !== '', fn ($q) => $q->where('p.path', $this->page));
    }

    public function totals(): array
    {
        $v = $this->visits()->selectRaw('count(*) n, count(distinct v.visitor) people, sum(case when v.pageviews <= 1 then 1 else 0 end) single, sum(v.seconds) secs, sum(case when v.lead_id is not null then 1 else 0 end) leads, sum(case when v.channel = ? then 1 else 0 end) ai', ['AI'])->first();
        $views = $this->views()->count();
        $n = (int) $v->n;
        return [
            'visitors' => (int) $v->people, 'visits' => $n, 'views' => $views,
            'bounce' => $n ? round(100 * $v->single / $n) : 0,
            'avg_time' => $n ? (int) round($v->secs / $n) : 0,
            'leads' => (int) $v->leads, 'conversion' => $n ? round(100 * $v->leads / $n, 1) : 0,
            'ai' => (int) $v->ai,
        ];
    }

    /** Visits per day (per month for a year) and channel, for the chart. */
    public function series(): array
    {
        $monthly = $this->days > 92;
        $fmt = $monthly ? '%Y-%m' : '%Y-%m-%d';
        $expr = DB::getDriverName() === 'sqlite' ? "strftime('$fmt', v.started_at)" : "DATE_FORMAT(v.started_at, '$fmt')";
        $rows = $this->visits()->selectRaw("$expr as b, v.channel, count(*) n")->groupBy('b', 'v.channel')->get();
        $buckets = [];
        for ($d = $this->from->copy(); $d <= $this->to; $monthly ? $d->addMonth() : $d->addDay()) {
            $buckets[$d->format($monthly ? 'Y-m' : 'Y-m-d')] = array_fill_keys(Classifier::CHANNELS, 0);
        }
        foreach ($rows as $r) if (isset($buckets[$r->b])) $buckets[$r->b][$r->channel] = (int) $r->n;
        return ['monthly' => $monthly, 'buckets' => $buckets];
    }

    private function group(Builder $q, string $col, int $limit = 12): array
    {
        return $q->selectRaw("$col as k, count(*) visits, count(distinct v.visitor) people, sum(case when v.lead_id is not null then 1 else 0 end) leads, avg(v.pageviews) ppv, avg(v.seconds) secs")
            ->groupBy('k')->orderByDesc('visits')->limit($limit)->get()->map(fn ($r) => (array) $r)->all();
    }

    public function channels(): array { return $this->group($this->visits(), 'v.channel', 10); }
    public function sources(): array
    {
        return $this->visits()->selectRaw('v.source k, max(v.channel) channel, count(*) visits, count(distinct v.visitor) people, sum(case when v.lead_id is not null then 1 else 0 end) leads, avg(v.pageviews) ppv, avg(v.seconds) secs')
            ->groupBy('k')->orderByDesc('visits')->limit(15)->get()->map(fn ($r) => (array) $r)->all();
    }
    public function ai(): array { return $this->group($this->visits()->where('v.channel', 'AI'), 'v.source', 12); }
    public function search(): array { return $this->group($this->visits()->where('v.channel', 'Search'), 'v.source', 12); }
    public function social(): array { return $this->group($this->visits()->where('v.channel', 'Social'), 'v.source', 12); }
    public function referrers(): array { return $this->group($this->visits()->where('v.channel', 'Referral'), 'v.referrer_host', 15); }
    public function landing(): array { return $this->group($this->visits(), 'v.landing_path', 15); }
    public function devices(): array { return $this->group($this->visits(), 'v.device', 5); }
    public function browsers(): array { return $this->group($this->visits(), 'v.browser', 8); }
    public function countries(): array { return $this->group($this->visits()->where('v.country', '!=', ''), 'v.country', 10); }

    public function campaigns(): array
    {
        return $this->visits()->where(fn ($q) => $q->where('v.utm_campaign', '!=', '')->orWhere('v.utm_source', '!=', ''))
            ->selectRaw("v.utm_source s, v.utm_medium m, v.utm_campaign c, count(*) visits, sum(case when v.lead_id is not null then 1 else 0 end) leads")
            ->groupBy('s', 'm', 'c')->orderByDesc('visits')->limit(15)->get()->map(fn ($r) => (array) $r)->all();
    }

    /** Pages by views, with visitors, average time, entrances and leads from visits that started there. */
    public function pages(): array
    {
        $rows = $this->views()->selectRaw('p.path k, count(*) views, count(distinct v.visitor) people, avg(nullif(p.seconds, 0)) secs')
            ->groupBy('k')->orderByDesc('views')->limit(25)->get();
        $entry = $this->visits()->whereIn('v.landing_path', $rows->pluck('k'))->selectRaw('v.landing_path k, count(*) n, sum(case when v.lead_id is not null then 1 else 0 end) leads')->groupBy('k')->get()->keyBy('k');
        return $rows->map(fn ($r) => ['k' => $r->k, 'views' => (int) $r->views, 'people' => (int) $r->people, 'secs' => (int) round((float) $r->secs),
            'entries' => (int) ($entry[$r->k]->n ?? 0), 'leads' => (int) ($entry[$r->k]->leads ?? 0)])->all();
    }

    /** Recent referring links (full URLs) for the Referral channel. */
    public function referrerLinks(): array
    {
        return $this->visits()->where('v.referrer', '!=', '')->whereNotIn('v.channel', ['Search'])
            ->selectRaw('v.referrer k, max(v.channel) channel, count(*) visits')->groupBy('k')->orderByDesc('visits')->limit(15)->get()->map(fn ($r) => (array) $r)->all();
    }

    /** AI crawlers and assistants reading pages (server side, never in visits). */
    public function bots(string $kind = 'ai'): array
    {
        return DB::table('analytics_bot_hits')->where('kind', $kind)->whereBetween('hit_at', [$this->from, $this->to])
            ->when($this->page !== '', fn ($q) => $q->where('path', $this->page))
            ->selectRaw('bot k, count(*) hits, count(distinct path) pages, max(hit_at) last')->groupBy('k')->orderByDesc('hits')->get()->map(fn ($r) => (array) $r)->all();
    }

    public function botPages(): array
    {
        return DB::table('analytics_bot_hits')->where('kind', 'ai')->whereBetween('hit_at', [$this->from, $this->to])
            ->selectRaw('path k, count(*) hits, count(distinct bot) bots')->groupBy('k')->orderByDesc('hits')->limit(15)->get()->map(fn ($r) => (array) $r)->all();
    }
}
