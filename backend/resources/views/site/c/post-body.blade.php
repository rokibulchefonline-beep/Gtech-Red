{{-- Markdown post body (blog/PostBody.tsx). $blocks from Blog::parse() --}}
@php($B = \App\Support\Site\Blog::class)
@foreach ($blocks as $b)
@switch($b['type'])
@case('h2')<h2 id="{{ $b['id'] }}">{{ $b['text'] }}</h2>@break
@case('h3')<h3 id="{{ $b['id'] }}">{{ $b['text'] }}</h3>@break
@case('ul')<ul>@foreach ($b['items'] as $it)<li>{!! $B::rich($it) !!}</li>@endforeach</ul>@break
@case('ol')<ol>@foreach ($b['items'] as $it)<li>{!! $B::rich($it) !!}</li>@endforeach</ol>@break
@case('quote')<blockquote>{!! $B::rich($b['text']) !!}</blockquote>@break
@case('img')<figure><img src="{{ $b['src'] }}" alt="{{ $b['alt'] }}" loading="lazy">@if ($b['alt'])<figcaption>{{ $b['alt'] }}</figcaption>@endif</figure>@break
@case('hr')<hr>@break
@default<p>{!! $B::rich($b['text']) !!}</p>
@endswitch
@endforeach
