{{-- Table of contents with scroll-spy (blog/Toc.tsx). $items [id, text] --}}
<nav class="bl-toc" aria-label="Table of contents" data-spy><p class="bl-side-title">Table of Contents</p><ol>@foreach ($items as $i)<li class="{{ $loop->first ? 'on' : '' }}"><a href="#{{ $i['id'] }}">{{ $i['text'] }}</a></li>@endforeach</ol></nav>
