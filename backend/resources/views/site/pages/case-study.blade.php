{{-- /case-studies/{slug} (app/case-studies/[slug]/page.tsx). $d CaseStudy --}}
@extends('site.layout')
@php($R = \App\Support\Site\Repo::class)
@php($others = $R::caseStudies(12)->where('slug', '!=', $d->slug)->take(3)->values())
@php($used = collect((array) $d->services)->map(fn ($s) => $R::item($s)['item'] ?? null)->filter()->take(6)->values())
@php($name = $d->client ?: $d->title)
@php($metrics = (array) $d->metrics)
@php($svc = $R::uri($used[0]->name ?? ''))
@php($path = "/case-studies/{$d->slug}")
@php($headline = "$name Case Study".($metrics ? ': '.implode(' and ', array_map(fn ($m) => "{$m['value']} {$m['label']}", array_slice($metrics, 0, 2))) : ''))
@php($short = "$name Case Study".($metrics ? ": {$metrics[0]['value']} {$metrics[0]['label']}" : ''))
@php($metaDesc = $d->meta_description ?: \App\Support\Site\Seo::fillDescription((string) $d->excerpt, "How GTech Digital helped $name".($used->count() ? ' with '.$used->take(2)->pluck('name')->implode(' and ') : '').($metrics ? ': '.implode(', ', array_map(fn ($m) => "{$m['value']} {$m['label']}", array_slice($metrics, 0, 2))) : '').'.'))
@php($seo = \App\Support\Site\Seo::make($path, ['title' => $d->meta_title ?: (mb_strlen($headline) <= \App\Support\Site\Seo::TITLE_MAX ? $headline : $short), 'description' => $metaDesc, 'og' => ['type' => 'article', 'title' => $headline, 'description' => $d->excerpt, 'image' => $d->image]], [
    \App\Support\Site\Schema::page(['path' => $path, 'name' => $headline, 'description' => (string) $d->excerpt, 'mainEntity' => \App\Support\Site\Schema::abs($path).'#article', 'image' => $d->image, 'about' => $used->pluck('name')->all(), 'modified' => $d->updated_at?->format('Y-m-d')]),
    \App\Support\Site\Schema::breadcrumb($path, [['Case Studies', '/case-studies'], [$name, $path]]),
    \App\Support\Site\Schema::article(['path' => $path, 'headline' => $headline, 'description' => $d->meta_description ?: ($d->excerpt ?: ''), 'image' => $d->image, 'keywords' => $used->pluck('name')->all(), 'modified' => $d->updated_at?->format('Y-m-d')]),
]))
@section('content')
<section class="cs-hero">
<div class="wrap cs-hero-in{{ $d->image ? ' has-img' : '' }}">
<div class="cs-hero-copy">
<nav class="sp-crumbs left" aria-label="Breadcrumb"><a href="/">Home</a><span>/</span><a href="/case-studies">Case Studies</a><span>/</span><b>{{ $name }}</b></nav>
@if ($d->industry)<span class="cs-tag">{{ $d->industry }}</span>@endif
<h1>{{ $name }} Case Study{!! $metrics ? ': <span class="hl">'.e(implode(' and ', array_map(fn ($m) => "{$m['value']} {$m['label']}", array_slice($metrics, 0, 2)))).'</span>' : '' !!}</h1>
@if ($d->excerpt)<p class="cs-lead">{{ $d->excerpt }}</p>@endif
<div class="sp-hero-btns left">
<a class="sp-btn-red" href="/contact?service={{ $svc }}">Get Similar Results</a>
<a class="sp-btn-line light" href="#results">See the Results</a>
</div>
</div>
@if ($d->image)<div class="cs-hero-art"><img src="{{ $d->image }}" alt="{{ $d->image_alt ?: "$name case study" }}" fetchpriority="high" decoding="async"></div>@endif
</div>
</section>
@if ($metrics)
<div id="results" class="wrap cs-metrics-wrap"><div class="cs-metrics n{{ count($metrics) }}">@foreach ($metrics as $m)<div class="cs-metric"><strong>{{ $m['value'] }}</strong><span>{{ $m['label'] }}</span></div>@endforeach</div></div>
@endif
<section class="sp-sec cs-body"><div class="wrap cs-grid">
<aside class="cs-snap"><div class="cs-snap-in">
@if ($d->logo)<img class="cs-snap-logo" src="{{ $d->logo }}" alt="{{ $name }}">@endif
<h2>Project Snapshot</h2>
<dl>
<div><dt>Client</dt><dd>{{ $name }}</dd></div>
@if ($d->industry)<div><dt>Industry</dt><dd>{{ $d->industry }}</dd></div>@endif
@if ($d->duration)<div><dt>Duration</dt><dd>{{ $d->duration }}</dd></div>@endif
@if ($d->website)<div><dt>Website</dt><dd><a href="{{ $d->website }}" target="_blank" rel="noopener noreferrer">{{ preg_replace('#^https?://#', '', $d->website) }}</a></dd></div>@endif
</dl>
@if ($used->count())
<h3>Services</h3>
<ul class="cs-snap-svc">@foreach ($used as $it)<li><a href="/services/{{ $it->slug }}">@icon($it->icon, 16){{ $it->name }}</a></li>@endforeach</ul>
@endif
<a class="sp-btn-red wide" href="/contact?service={{ $svc }}">Talk to Our Team</a>
</div></aside>
<div class="cs-main">
@if ($d->challenge)<section id="challenge"><h2>@hl("The Challenge Facing $name")</h2><p>{{ $d->challenge }}</p></section>@endif
@if ($d->solution)<section id="solution"><h2>@hl("Our Solution for $name")</h2><p>{{ $d->solution }}</p></section>@endif
@if (!$d->challenge && !$d->solution && $d->body)<section><p style="white-space:pre-line">{{ $d->body }}</p></section>@endif
</div>
</div></section>
@if (!empty($d->quote['text']))
<section class="sp-sec"><div class="wrap"><figure class="cs-quote">@icon('lucide:quote', 34)<blockquote>{{ $d->quote['text'] }}</blockquote><figcaption><b>{{ $d->quote['name'] ?? '' }}</b>@if (!empty($d->quote['role']))<span>{{ $d->quote['role'] }}</span>@endif</figcaption></figure></div></section>
@endif
@if ($others->count())
<section class="sp-sec"><div class="wrap">
<div class="sp-head center"><h2>@hl('More Digital Marketing Case Studies')</h2></div>
<div class="case-grid">@foreach ($others as $i => $o)@include('site.c.case-card', ['doc' => $o, 'index' => $i])@endforeach</div>
</div></section>
@endif
@include('site.c.inquiry')
@endsection
