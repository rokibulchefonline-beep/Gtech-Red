{{-- Page head built by App\Support\Site\Seo::make(). --}}
<title>{{ $seo['title'] }}</title>
@if ($seo['description'])<meta name="description" content="{{ $seo['description'] }}">@endif
@if ($seo['robots'])<meta name="robots" content="{{ $seo['robots'] }}">@endif
@if ($seo['keywords'])<meta name="keywords" content="{{ $seo['keywords'] }}">@endif
<link rel="canonical" href="{{ $seo['canonical'] }}">
@foreach ($seo['og'] as $k => $v)<meta property="{{ $k }}" content="{{ $v }}">
@endforeach
@foreach ($seo['twitter'] as $k => $v)<meta name="{{ $k }}" content="{{ $v }}">
@endforeach
@foreach ($seo['schema'] as $json)<script type="application/ld+json">{!! $json !!}</script>
@endforeach
