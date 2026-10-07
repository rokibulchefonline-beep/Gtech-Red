<!doctype html>
<html lang="en-GB">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex, nofollow">
<title>Preview: {{ $page->name }}</title>
<style>
:root{color-scheme:light;--bar:#111;--ink:#fff;--muted:#a3a3a3;--red:#e8202f}
*{box-sizing:border-box}body{margin:0;background:#e9e9ec;font:14px/1.4 system-ui,-apple-system,"Segoe UI",sans-serif;height:100vh;display:flex;flex-direction:column}
.bar{background:var(--bar);color:var(--ink);display:flex;align-items:center;gap:12px;padding:8px 16px;flex-wrap:wrap}
.bar b{font-weight:600}.tag{background:#b45309;color:#fff;border-radius:999px;padding:2px 10px;font-size:12px;font-weight:600}
.path{color:var(--muted)}.sp{flex:1}
.dev{display:flex;border:1px solid #444;border-radius:8px;overflow:hidden}
.dev button{background:transparent;color:var(--ink);border:0;padding:6px 12px;font:inherit;cursor:pointer}
.dev button[aria-pressed=true]{background:var(--red)}
.stage{flex:1;overflow:auto;display:flex;justify-content:center;padding:16px}
iframe{border:0;background:#fff;height:100%;width:100%;box-shadow:0 2px 16px rgba(0,0,0,.15);transition:width .2s}
.note{color:var(--muted);font-size:12px}
</style>
</head>
<body>
<div class="bar">
<span class="tag">Preview</span><b>{{ $page->name }}</b><span class="path">{{ $page->path }}</span>
<span class="note">Unsaved changes, not on the website. Refresh the editor's Preview to see later edits.</span>
<span class="sp"></span>
<div class="dev" role="group" aria-label="Screen size">
<button type="button" data-w="100%" aria-pressed="true">Desktop</button><button type="button" data-w="820px" aria-pressed="false">Tablet</button><button type="button" data-w="390px" aria-pressed="false">Phone</button>
</div>
</div>
<div class="stage"><iframe src="{{ $frame }}" title="Page preview"></iframe></div>
<script>
document.querySelectorAll('.dev button').forEach(function (b) {
  b.addEventListener('click', function () {
    document.querySelectorAll('.dev button').forEach(function (x) { x.setAttribute('aria-pressed', x === b ? 'true' : 'false'); });
    document.querySelector('iframe').style.width = b.dataset.w;
  });
});
</script>
</body>
</html>
