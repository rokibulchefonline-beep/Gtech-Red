{{-- Simple page heading (PageHead.tsx). $title, $sub, $back [href,label], $icon --}}
<section class="page-hd">
<div class="wrap">
@if (!empty($back))<a href="{{ $back[0] }}">&larr; {{ $back[1] }}</a>@endif
<h1>{!! !empty($icon) ? \App\Support\Site\Icons::svg($icon, 36, 'hd-ico') : '' !!}@hl($title)</h1>
@if (!empty($sub))<p>{{ $sub }}</p>@endif
</div>
</section>
