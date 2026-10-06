<section id="faq" class="sp-sec"><div class="wrap sp-faq-wrap">
<aside class="sp-faq-aside">
<h2>@hl($title)</h2>
<p>Can not find what you are looking for? Our specialists are happy to help.</p>
<a class="btn light-btn" href="/contact">Ask an expert</a>
</aside>
<div class="sp-faq">
@foreach ($faqs as $f)<details @if ($loop->first) open @endif><summary>{{ $f['q'] }}@icon('lucide:chevron-down', 20)</summary><p>@rt($f['a'])</p></details>@endforeach
</div>
</div></section>
