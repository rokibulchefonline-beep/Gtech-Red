{{-- One page section (Block in ServicePage.tsx). $s section array, $slug page slug, $name short name. --}}
@php($R = \App\Support\Site\Repo::class)
@switch($s['type'])
@case('logos')
@if ($R::clients())
@php($logos = $R::clients())
<section class="sp-logos" aria-label="Clients"><div class="wrap">
<p class="sp-logos-h"><span></span>Trusted by growing UK brands<span></span></p>
<div class="sp-marquee{{ count($logos) < 5 ? ' static' : '' }}"><div class="sp-marquee-track">
@foreach ($logos as $b)<div class="sp-logo-tile"><img src="{{ $b['logo'] }}" alt="{{ $b['name'] }}" loading="lazy" decoding="async"></div>@endforeach
@if (count($logos) >= 5)@foreach ($logos as $b)<div class="sp-logo-tile" aria-hidden="true"><img src="{{ $b['logo'] }}" alt="" loading="lazy" decoding="async"></div>@endforeach @endif
</div></div>
</div></section>
@endif
@break
@case('text')
<section id="{{ $s['id'] }}" class="sp-sec"><div class="wrap sp-split">
<div class="sp-split-head">
<h2>@hl($s['heading'])</h2>
<span class="sp-split-shape" aria-hidden="true"></span>
</div>
<div class="sp-split-body">@include('site.c.paras', ['p' => $s['paras'] ?? []])@include('site.c.list', ['b' => $s['bullets'] ?? null])</div>
</div></section>
@break
@case('media')
<section id="{{ $s['id'] }}" class="sp-sec {{ ($s['tone'] ?? '') === 'grey' ? 'sp-grey' : '' }} {{ !empty($s['flip']) ? 'flip' : '' }}"><div class="wrap sp-media">
<div class="sp-media-copy">@include('site.c.head', ['heading' => $s['heading'], 'center' => false])@include('site.c.paras', ['p' => $s['paras'] ?? []])@include('site.c.list', ['b' => $s['bullets'] ?? null])
<p class="sp-media-cta"><a class="btn-red" href="/contact?service={{ $R::uri($name) }}">{{ $R::ctaLabel($slug, $name, $s['id']) }} @icon('lucide:arrow-right', 15)</a></p>
</div>
<div class="sp-media-frame"><img src="{{ $s['image'] }}" alt="{{ $s['alt'] ?? '' }}" loading="lazy" width="960" height="720"></div>
</div></section>
@break
@case('cards')
<section id="{{ $s['id'] }}" class="sp-sec {{ count($s['cards']) > 4 ? 'sp-bg-dark' : '' }}"><div class="wrap">
@include('site.c.head', ['heading' => $s['heading'], 'intro' => $s['intro'] ?? null])
<div class="sp-cards n{{ count($s['cards']) }}">
@foreach ($s['cards'] as $c)<article class="sp-card"><span class="sp-card-ico">@icon($c['icon'] ?? '', 24)</span><h3>{{ $c['title'] }}</h3><p>{{ $c['text'] }}</p></article>@endforeach
</div>
</div></section>
@break
@case('features')
<section id="{{ $s['id'] }}" class="sp-sec"><div class="wrap sp-feat">
<div>@include('site.c.head', ['heading' => $s['heading'], 'center' => false, 'intro' => $s['intro'] ?? null])@include('site.c.paras', ['p' => $s['paras'] ?? []])
<a class="btn" href="/contact">Talk to our team</a></div>
<div class="sp-feat-grid">@foreach ($s['cards'] as $c)<article class="sp-feat-card"><span class="sp-card-ico solid">@icon($c['icon'] ?? '', 22)</span><h3>{{ $c['title'] }}</h3><p>{{ $c['text'] }}</p></article>@endforeach</div>
</div></section>
@break
@case('steps')
<section id="{{ $s['id'] }}" class="sp-sec sp-bg-mesh"><div class="wrap">
@include('site.c.head', ['heading' => $s['heading'], 'intro' => $s['intro'] ?? null])
<ol class="sp-steps">@foreach ($s['steps'] as $n => $st)<li><span class="sp-step-n">{{ $n + 1 }}</span><div><h3>{{ $st['title'] }}</h3><p>{{ $st['text'] }}</p></div></li>@endforeach</ol>
</div></section>
@break
@case('table')
<section id="{{ $s['id'] }}" class="sp-sec sp-grey"><div class="wrap" style="max-width:1040px">
@include('site.c.head', ['heading' => $s['heading'], 'intro' => $s['intro'] ?? null])
<div class="sp-table-wrap"><table class="sp-table"><thead><tr>@foreach ($s['columns'] as $c)<th scope="col">{{ $c }}</th>@endforeach</tr></thead><tbody>@foreach ($s['rows'] as $r)<tr>@foreach ($r as $k => $c)@if ($k)<td>{{ $c }}</td>@else<th scope="row">{{ $c }}</th>@endif @endforeach</tr>@endforeach</tbody></table></div>
@if (!empty($s['note']))<p class="sp-note">@icon('lucide:lightbulb', 20){{ $s['note'] }}</p>@endif
</div></section>
@break
@case('metrics')
<section id="{{ $s['id'] }}" class="sp-sec sp-bg-red"><div class="wrap">
@include('site.c.head', ['heading' => $s['heading'], 'intro' => $s['intro'] ?? null])
<div class="sp-metrics">@foreach ($s['metrics'] as $m)<div class="sp-metric"><span>{{ $m['label'] }}</span><b>{{ $m['value'] }}</b><p>{{ $m['text'] }}</p></div>@endforeach</div>
</div></section>
@break
@case('impact')
<section id="{{ $s['id'] }}" class="sp-impact"><div class="wrap sp-impact-in">
<div class="sp-impact-copy"><h2>@hl($s['heading'])</h2><p>@rt($s['text'] ?? '')</p></div>
<div class="sp-impact-stats">@foreach ($s['stats'] as $st)<div class="sp-impact-stat"><b>{{ $st['value'] }}</b><span>{{ $st['label'] }}</span></div>@endforeach</div>
</div></section>
@break
@case('cases')
@php($docs = ($s['service'] ?? '') === '*' ? $R::caseStudies(6) : $R::caseStudiesFor(($s['service'] ?? '') ?: $slug, 6))
@if ($docs->count())
<section id="{{ $s['id'] }}" class="sp-sec cases"><div class="wrap">
@include('site.c.head', ['heading' => $s['heading'], 'intro' => $s['intro'] ?? null])
@include('site.c.case-carousel', ['docs' => $docs])
<p class="cases-all"><a class="btn-dark" href="/case-studies">View All Case Studies</a></p>
</div></section>
@endif
@break
@case('reviews')
{{-- Every page shows the same client testimonials slider as the home page (Website content > Testimonials). --}}
@include('site.c.home.testimonials', ['id' => $s['id']])
@break
@case('industries')
<section id="{{ $s['id'] }}" class="sp-sec sp-grey"><div class="wrap">
@include('site.c.head', ['heading' => $s['heading'], 'intro' => $s['intro'] ?? null])
<div class="sp-inds">@foreach ($s['items'] as $it)@php($ind = $R::industry($it['slug']))@if ($ind)<a href="/industries/{{ $ind->slug }}" class="sp-ind"><span class="sp-ind-ico">@icon($ind->icon, 24)</span><h3>{{ $ind->name }}</h3><p>{{ $it['text'] }}</p><span class="sp-ind-more">Explore {{ $ind->name }} @icon('lucide:arrow-right', 16)</span></a>@endif @endforeach</div>
<p class="sp-inds-all"><a href="/industries">View all industries @icon('lucide:arrow-right', 15)</a></p>
</div></section>
@break
@case('cta')
<section id="{{ $s['id'] }}" class="sp-cta {{ ($s['tone'] ?? 'red') === 'dark' ? 'dark' : '' }}"><div class="wrap sp-cta-in">
<div><h2>@hl($s['heading'])</h2>@if (!empty($s['text']))<p>{{ $s['text'] }}</p>@endif</div>
<a class="btn-red" href="{{ $s['link'] }}">{{ $s['button'] }} @icon('lucide:arrow-right', 15)</a>
</div></section>
@break
@case('faq')
<section id="{{ $s['id'] }}" class="sp-sec"><div class="wrap" style="max-width:860px">
@include('site.c.head', ['heading' => $s['heading'], 'intro' => $s['intro'] ?? null])
<div class="sp-acc">@foreach ($s['items'] as $it)<details class="sp-acc-item"><summary>{{ $it['title'] }}</summary><p>{{ $it['text'] }}</p></details>@endforeach</div>
</div></section>
@break
@case('video')
@php([$host, $vid] = array_pad(explode(':', (string) ($s['video'] ?? ''), 2), 2, ''))
@if ($vid !== '')
<section id="{{ $s['id'] }}" class="sp-sec sp-grey"><div class="wrap" style="max-width:960px">
@include('site.c.head', ['heading' => $s['heading'], 'intro' => $s['intro'] ?? null])
<div class="sp-video"><iframe src="{{ $host === 'vimeo' ? 'https://player.vimeo.com/video/'.$vid : 'https://www.youtube-nocookie.com/embed/'.$vid }}" title="{{ strip_tags($s['heading']) }}" loading="lazy" allow="accelerometer; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>
@if (!empty($s['caption']))<p class="sp-note">{{ $s['caption'] }}</p>@endif
</div></section>
@endif
@break
@case('pricing')
<section id="{{ $s['id'] }}" class="sp-sec sp-grey"><div class="wrap">
@include('site.c.head', ['heading' => $s['heading'], 'intro' => $s['intro'] ?? null])
<div class="sp-plans">@foreach ($s['plans'] as $pl)
<article class="sp-plan {{ !empty($pl['highlight']) ? 'hot' : '' }}">
@if (!empty($pl['highlight']))<span class="sp-plan-tag">Most popular</span>@endif
<h3>{{ $pl['name'] }}</h3>
<p class="sp-plan-price"><b>{{ $pl['price'] }}</b>@if (!empty($pl['period']))<span>{{ $pl['period'] }}</span>@endif</p>
@if (!empty($pl['text']))<p class="sp-plan-text">{{ $pl['text'] }}</p>@endif
<ul class="sp-plan-list">@foreach ($pl['features'] as $f)<li>@include('site.c.tick'){{ $f }}</li>@endforeach</ul>
<a class="{{ !empty($pl['highlight']) ? 'btn-red' : 'btn-outline' }}" href="{{ $pl['link'] }}">{{ $pl['button'] }}</a>
</article>@endforeach</div>
</div></section>
@break
@case('html')
<section id="{{ $s['id'] }}" class="sp-html {{ empty($s['full']) ? 'sp-sec' : '' }}">@if (!empty($s['css']))<style>#{{ $s['id'] }} { {!! $s['css'] !!} }</style>@endif<div class="{{ empty($s['full']) ? 'wrap' : '' }}">{!! $s['html'] ?? '' !!}</div></section>
@break
@case('form')
@include('site.c.inquiry', ['service' => $name, 'id' => $s['id'], 'heading' => ($s['heading'] ?? '') ?: null, 'intro' => ($s['intro'] ?? '') ?: null])
@break
@endswitch
