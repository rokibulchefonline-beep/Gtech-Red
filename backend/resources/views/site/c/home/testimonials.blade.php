{{-- Rotating testimonials (Testimonials.tsx; rotation in site.js). --}}
@php($list = \App\Support\Site\Repo::testimonials())
@if ($list->count())
<section class="testi" data-testi @if (!empty($id)) id="{{ $id }}" @endif>
<div class="wrap">
<h2>@hl('What Our [[Clients Say]]')</h2>
{!! \App\Support\Site\Icons::svg('lucide:messages-square', 44, 'testi-ico') !!}
<div class="testi-stage">@foreach ($list as $n => $t)<blockquote class="{{ $n ? '' : 'on' }}" aria-hidden="{{ $n ? 'true' : 'false' }}"><h3>{{ $t->title }}</h3><p>{{ $t->text }}</p><cite>{{ $t->name }}</cite></blockquote>@endforeach</div>
<div class="testi-dots" role="tablist" aria-label="Choose testimonial">@foreach ($list as $n => $t)<button role="tab" aria-selected="{{ $n ? 'false' : 'true' }}" aria-label="Testimonial {{ $n + 1 }}" class="{{ $n ? '' : 'on' }}"></button>@endforeach</div>
</div>
</section>
@endif
