{{-- Home page (app/page.tsx). $p Page --}}
@extends('site.layout')
@php($R = \App\Support\Site\Repo::class)
@php($lines = explode('|', $p->hero['h1'] ?? ''))
@php($sec = fn ($id) => $R::homeSection($id))
@php($who = $sec('who'))
@php($svc = $sec('services'))
@php($how = $sec('how'))
@php($howSteps = $sec('how-steps')['steps'] ?? [])
@php($howBase = [['Discover & Plan', 'We audit your website, ads and competitors, then agree clear goals and a plan built around your numbers.'], ['Build & Launch', 'Our team designs, develops and launches your campaigns, website or software, with fast feedback at every stage.'], ['Measure & Grow', 'We track every lead and sale, report in plain English and keep improving so results compound month after month.']])
@php(\App\Support\Site\Schema::$modified = $p->updated_at?->format('Y-m-d'))
@php($seo = \App\Support\Site\Seo::make('/', ['title' => $p->meta_title, 'absolute' => true, 'description' => $p->meta_description], [
    \App\Support\Site\Schema::page(['path' => '/', 'name' => $p->meta_title, 'description' => $p->meta_description, 'mainEntity' => \App\Support\Site\Schema::org()]),
    \App\Support\Site\Schema::itemList('/', 'GTech Digital services', $R::groups()->map(fn ($g) => [$g->title, "/services/{$g->slug}"])->all()),
    ...(!empty($p->faqs) ? [\App\Support\Site\Schema::faq('/', $p->faqs)] : []),
]))
@section('d-partners')
@include('site.c.partner-strip')
@endsection
@section('d-who')
<section class="who">
<div class="wrap who-grid">
@include('site.c.intro-video')
<div class="who-body">
<h2>@if (str_contains($who['heading'], '[['))@hl($who['heading'])@else{{ $who['heading'] }}@endif</h2>
<p class="who-text">@rt($who['paras'][0] ?? '')</p>
<ul class="who-points">@foreach ($who['bullets'] ?? [] as $pt)<li>@include('site.c.tick')<span>@rt($pt)</span></li>@endforeach</ul>
<div class="who-btns">
<a class="btn" href="/about">More about us</a>
<a class="btn-line" href="/contact">Contact us</a>
</div>
</div>
</div>
</section>
@endsection
@section('d-services')
<section class="ourservices">
<div class="wrap">
<h2>@hl($svc['heading'])</h2>
<p class="os-sub">@rt($svc['text'] ?? '')</p>
<div class="svc-stack " data-stack>@foreach ($R::coreServices() as $i => $s)<article class="svc-card " style="--i:{{ $i }}"><div class="svc-text"><h3>{{ $s->title }}</h3><p>{{ $s->line }}</p><ul>@foreach ((array) $s->points as $pt)<li>@include('site.c.tick'){{ $pt }}</li>@endforeach</ul><a class="svc-link" href="/services/{{ $s->slug }}">Learn more &rarr;</a></div><div class="svc-media">@if ($s->image)<img src="{{ $s->image }}" alt="{{ $s->title }}" loading="lazy">@else @icon($R::item($s->slug)['item']->icon ?? $R::group($s->slug)?->icon ?? '', 110)@endif</div></article>@endforeach</div>
</div>
</section>
@endsection
@section('d-how')
<section class="how " data-inview="0.25">
<div class="wrap">
<h2>@hl($how['heading'])</h2>
<p class="how-sub">@rt($how['text'] ?? '')</p>
<div class="how-grid">
@include('site.c.home.how-line')
@foreach ($howBase as $n => [$t, $x])<div class="how-step"><div class="how-art">@include('site.c.home.how-art-'.($n + 1))</div><h3>{{ $howSteps[$n]['title'] ?? $t }}</h3><p>{{ $howSteps[$n]['text'] ?? $x }}</p></div>@endforeach
</div>
</div>
</section>
@endsection
@section('d-brands')
@if (\App\Support\Site\Repo::clients())@include('site.c.home.brands')@endif
@endsection
@section('d-cases')
<section class="cases">
<div class="wrap">
<h2>@hl('Digital Marketing [[Case Studies]]')</h2>
<p class="os-sub">See how we help brands grow with results you can measure.</p>
@include('site.c.case-carousel', ['docs' => $R::caseStudies(6)])
<p class="cases-all"><a class="btn-dark" href="/case-studies">View All Case Studies</a></p>
</div>
</section>
@endsection
@section('d-results')
<section class="results " data-inview="0.2"><div class="wrap"><h2>Tired of Excuses Instead of <span class="red">Results?</span></h2><p class="results-sub"><strong>See what better growth looks like</strong> with GTech Digital</p>
@include('site.c.home.results-grid')
</div></section>
@endsection
@section('d-testimonials')
@include('site.c.home.testimonials')
@endsection
@section('d-faq')
@if (!empty($p->faqs))@include('site.c.faq', ['title' => 'Frequently Asked Questions About GTech Digital', 'faqs' => $p->faqs])@endif
@endsection
@section('d-inquiry')
@include('site.c.inquiry')
@endsection
@section('content')
<div class="no-hl">
<section class="hero-video">
@php($hv = \App\Support\Site\HeroVideo::for($p))
@if ($hv['type'] === 'youtube')
<div class="hero-yt" aria-hidden="true" data-hero-video data-yt="{{ $hv['id'] }}" data-mobile="{{ $hv['mobile'] ? 1 : 0 }}"@if ($hv['poster']) style="background:#0a0a0a url('{{ $hv['poster'] }}') center/cover no-repeat"@endif></div>
@elseif ($hv['type'] === 'file')
<div class="hero-bg" aria-hidden="true"@if ($hv['poster']) style="background-image:url('{{ $hv['poster'] }}')"@endif><video class="hero-bg-v" muted loop playsinline preload="none"@if ($hv['poster']) poster="{{ $hv['poster'] }}"@endif data-hero-video data-src="{{ $hv['src'] }}" data-mobile="{{ $hv['mobile'] ? 1 : 0 }}"></video></div>
@else
<div class="hero-bg" aria-hidden="true"@if ($hv['poster']) style="background-image:url('{{ $hv['poster'] }}')"@endif></div>
@endif
<div class="wrap">
<div class="hero-head">
<h1 class="hero-title">@foreach ($lines as $i => $l)<span{!! $i === count($lines) - 1 && $i > 0 ? ' class="hero-last"' : '' !!}>@if (str_contains($l, '[['))@hl($l)@else{{ trim($l) ?: ' ' }}@endif</span>@endforeach</h1>
<p class="hero-sub">@rt($p->hero['lead'] ?? '')</p>
</div>
<div class="hero-ctas">
<a class="btn-red" href="/contact">Let&#x27;s Talk</a>
<a class="btn-outline" href="/services">Our Services</a>
</div>
@include('site.c.stats', ['hero' => true])
</div>
</section>
@include('site.c.layout', ['p' => $p, 'slug' => 'home', 'name' => 'GTech Digital'])
</div>
@endsection
