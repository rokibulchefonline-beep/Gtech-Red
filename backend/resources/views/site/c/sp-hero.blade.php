{{-- Hero of service and industry pages. $p Page, $name, $crumbs [[label, href]], $second [href, label], $alt, $icon --}}
<section class="sp-hero">
<div class="wrap sp-hero-in">
<nav class="sp-crumbs" aria-label="Breadcrumb"><a href="/">Home</a><span>/</span>@foreach ($crumbs as [$l, $h])<a href="{{ $h }}">{{ $l }}</a><span>/</span>@endforeach<b>{{ $name }}</b></nav>
<h1>@hl($p->hero['h1'] ?? (($p->hero['keyword'] ?? $name).' Services of [[GTech Digital]]'))</h1>
<p class="sp-lead">@rt($p->hero['lead'] ?? '')</p>
<div class="sp-hero-btns">
<a class="sp-btn-red" href="/contact?service={{ \App\Support\Site\Repo::uri($name) }}">Book a Free Audit</a>
<a class="sp-btn-line" href="{{ $second[0] }}">{{ $second[1] }}</a>
</div>
<ul class="sp-hero-points">@foreach ($p->hero['points'] ?? [] as $pt)<li>@include('site.c.tick'){{ $pt }}</li>@endforeach</ul>
<p class="sp-updated">Reviewed by GTech Digital specialists · Updated {{ now()->format('F Y') }}</p>
<div class="sp-hero-show">
<img src="{{ $p->hero['motion'] ?? '' }}" alt="{{ $alt }}" width="800" height="600">
<span class="sp-float a">@icon($icon, 20){{ $name }}</span>
<span class="sp-float b">@icon('lucide:trending-up', 20)Revenue-focused</span>
</div>
</div>
</section>
@include('site.c.toc-bar', ['label' => 'On this page', 'items' => [...collect($p->sections)->filter(fn ($s) => !empty($s['nav']))->map(fn ($s) => ['id' => $s['id'], 'label' => $s['nav']])->values()->all(), ['id' => 'faq', 'label' => 'FAQs']]])
