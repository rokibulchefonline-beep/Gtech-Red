import { slugify } from '@/lib/util';

// Allow-list HTML sanitizer for post bodies (no DOM needed, so it runs in the browser, Node and
// Cloudflare Workers). Anything not explicitly allowed is dropped: scripts, event handlers,
// inline styles other than text-align and colour, javascript: URLs, iframes, forms.
const TAGS: Record<string, string[]> = {
  p: ['style'], h1: ['id'], h2: ['id'], h3: ['id'], h4: ['id'], h5: ['id'], h6: ['id'], strong: [], b: [], em: [], i: [], u: [], s: [], strike: [], del: [], mark: [], sub: [], sup: [],
  ul: [], ol: [], li: [], blockquote: [], pre: [], code: [], br: [], hr: [], a: ['href', 'target', 'rel', 'title'], img: ['src', 'alt', 'title', 'width', 'height'],
  figure: [], figcaption: [], span: ['style'], div: ['style'], table: [], thead: [], tbody: [], tr: [], th: [], td: [],
};
const VOID = new Set(['br', 'hr', 'img']);
const DROP_BLOCKS = /<(script|style|iframe|object|embed|form|textarea|noscript|svg|math)\b[\s\S]*?<\/\1\s*>/gi;
const esc = (s: string) => s.replace(/&(?!#?\w+;)/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
const decode = (s: string) => s.replace(/&#x([0-9a-f]+);?/gi, (_, h) => String.fromCharCode(parseInt(h, 16))).replace(/&#(\d+);?/g, (_, d) => String.fromCharCode(+d)).replace(/&colon;/gi, ':').replace(/&tab;|&newline;/gi, '');

function cleanStyle(v: string) {
  return v.split(';').map((d) => d.trim()).filter((d) => /^text-align\s*:\s*(left|right|center|justify)$/i.test(d) || /^color\s*:\s*(#[0-9a-f]{3,8}|rgb\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}\s*\))$/i.test(d)).join(';');
}
function cleanUrl(v: string, kind: 'href' | 'src') {
  const u = decode(v).replace(/[\u0000-\u0020\u007f]+/g, '');
  const ok = kind === 'href' ? /^(https?:|mailto:|tel:|\/(?!\/)|#)/i : /^(https?:\/\/|\/(?!\/))/i;
  return ok.test(u) ? v.trim() : '';
}

export function sanitizeHtml(input: string): string {
  const src = String(input ?? '').replace(/<!--[\s\S]*?-->/g, '').replace(DROP_BLOCKS, '');
  const re = /<(\/?)([a-zA-Z][a-zA-Z0-9]*)((?:"[^"]*"|'[^']*'|[^'">])*)>/g;
  let out = '', last = 0, m: RegExpExecArray | null;
  const text = (t: string) => t.replace(/</g, '&lt;').replace(/>/g, '&gt;');
  while ((m = re.exec(src))) {
    out += text(src.slice(last, m.index));
    last = re.lastIndex;
    const [, close, rawName, rawAttrs] = m;
    const name = rawName.toLowerCase();
    const allowed = TAGS[name];
    if (!allowed) continue;
    if (close) { if (!VOID.has(name)) out += `</${name}>`; continue; }
    let attrs = '', target = '';
    for (const a of rawAttrs.matchAll(/([a-zA-Z_:][-a-zA-Z0-9_:.]*)\s*(?:=\s*(?:"([^"]*)"|'([^']*)'|([^\s"'=<>`]+)))?/g)) {
      const k = a[1].toLowerCase(); const v = a[2] ?? a[3] ?? a[4] ?? '';
      if (!allowed.includes(k)) continue;
      if (k === 'href' || k === 'src') { const u = cleanUrl(v, k); if (u) attrs += ` ${k}="${esc(u)}"`; }
      else if (k === 'style') { const s = cleanStyle(decode(v)); if (s) attrs += ` style="${esc(s)}"`; }
      else if (k === 'target') { if (v === '_blank') { target = '_blank'; attrs += ' target="_blank"'; } }
      else if (k === 'id') { const s = slugify(v); if (s) attrs += ` id="${s}"`; }
      else if (k === 'width' || k === 'height') { if (/^\d{1,4}$/.test(v)) attrs += ` ${k}="${v}"`; }
      else if (k !== 'rel') attrs += ` ${k}="${esc(v)}"`;
    }
    if (name === 'a' && target) attrs += ' rel="noopener noreferrer"';
    if (name === 'img' && !/ alt=/.test(attrs)) attrs += ' alt=""';
    out += `<${name}${attrs}${VOID.has(name) ? '/' : ''}>`;
  }
  out += text(src.slice(last));
  // every H2/H3 gets an anchor id (table of contents, deep links)
  const used = new Set<string>();
  return out.replace(/<h([23])((?:\s[^>]*)?)>([\s\S]*?)<\/h\1>/g, (_, l, attrs, inner) => {
    const base = slugify(inner.replace(/<[^>]+>/g, '')) || 'section';
    let id = (/id="([^"]+)"/.exec(attrs)?.[1]) || base, n = 2;
    while (used.has(id)) id = `${base}-${n++}`;
    used.add(id);
    return `<h${l} id="${id}">${inner}</h${l}>`;
  });
}

/** Headings for the table of contents. Expects sanitized HTML. */
export const headingsOf = (html: string) => [...html.matchAll(/<h2 id="([^"]+)">([\s\S]*?)<\/h2>/g)].map((m) => ({ id: m[1], text: m[2].replace(/<[^>]+>/g, '').replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&quot;/g, '"').trim() }));
