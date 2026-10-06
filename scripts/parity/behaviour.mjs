// Drives the same interactions on the Next.js site (NEXT_URL) and the Blade site (BLADE_URL) and compares the
// resulting DOM state. Usage: node scripts/parity/behaviour.mjs [name filter]
import { chromium } from 'playwright';
const A = process.env.NEXT_URL || 'http://localhost:3130', B = process.env.BLADE_URL || 'http://127.0.0.1:8090';
const br = await chromium.launch();
const only = process.argv[2];
// Snapshot of the interactive state of the elements matching sel.
const snap = (p, sel) => p.evaluate((sel) => [...document.querySelectorAll(sel)].map((e) => {
  const o = { tag: e.tagName.toLowerCase(), cls: [...e.classList].filter((c) => !c.startsWith('rv')).sort().join(' ') };
  for (const a of ['aria-current', 'aria-hidden', 'aria-selected', 'aria-label', 'aria-expanded']) if (e.hasAttribute(a)) o[a] = e.getAttribute(a);
  if ('disabled' in e) o.disabled = e.disabled;
  if (e.style && e.style.width) o.width = e.style.width;
  const svg = e.querySelector(':scope > svg path'); if (svg) o.icon = svg.getAttribute('d').slice(0, 24);
  const t = e.childElementCount ? '' : e.textContent.trim(); if (t) o.text = t;
  return o;
}), sel);
async function open(base, path, w = 1352, h = 900) {
  const ctx = await br.newContext({ viewport: { width: w, height: h }, permissions: ['clipboard-read', 'clipboard-write'] });
  const p = await ctx.newPage(); const errors = [];
  p.on('pageerror', (e) => errors.push(e.message));
  await p.route(/youtube\.com|youtube-nocookie|googletagmanager|connect\.facebook\.net/, (r) => r.abort());
  await p.goto(base + path, { waitUntil: 'networkidle' });
  await p.evaluate(() => { const c = document.querySelector('.ck-ghost'); if (c && c.offsetParent) c.click(); });
  p.errors = errors; return p;
}
const scrollTo = (p, y) => p.evaluate((y) => window.scrollTo(0, y), y);
const scrollToEl = (p, sel, off = 0) => p.evaluate(([s, off]) => { const el = document.querySelector(s); window.scrollTo(0, el.getBoundingClientRect().top + scrollY + off); }, [sel, off]);
const wait = (p, ms) => p.waitForTimeout(ms);

const S = {
  'toc tabs: initial': async (p) => (await wait(p, 900), { tabs: await snap(p, '.sp-toc a'), left: await p.evaluate(() => Math.round(document.querySelector('.sp-toc .wrap').scrollLeft)) }),
  'toc tabs: scroll to cards section': async (p) => { await scrollToEl(p, '#services', -100); await wait(p, 1200); return { tabs: await snap(p, '.sp-toc a') }; },
  'toc tabs: page bottom': async (p) => { await scrollTo(p, 1e6); await wait(p, 1200); return { tabs: await snap(p, '.sp-toc a') }; },
  'toc tabs: click Process': async (p) => { await p.click('.sp-toc a[href="#process"]'); await wait(p, 1500); return { tabs: await snap(p, '.sp-toc a'), hash: await p.evaluate(() => location.hash) }; },
};
const pages = {
  '/services/local-seo': ['toc tabs: initial', 'toc tabs: scroll to cards section', 'toc tabs: page bottom', 'toc tabs: click Process'],
};
const mobile = {
  'toc tabs mobile: scroll far': async (p) => { await scrollToEl(p, '#reviews-clients', -100); await wait(p, 1500); return { tabs: await snap(p, '.sp-toc a'), left: await p.evaluate(() => Math.round(document.querySelector('.sp-toc .wrap').scrollLeft)) }; },
};
const cases = [];
for (const [path, names] of Object.entries(pages)) for (const n of names) cases.push({ path, name: n, fn: S[n] });
cases.push({ path: '/services/local-seo', name: 'toc tabs mobile: scroll far', fn: mobile['toc tabs mobile: scroll far'], w: 390 });

const extra = (await import('./scenarios.mjs')).default;
for (const c of [...cases, ...extra]) {
  if (only && !c.name.includes(only)) continue;
  const pa = await open(A, c.path, c.w, c.h), pb = await open(B, c.path, c.w, c.h);
  const [ra, rb] = [await c.fn(pa, { snap, scrollTo, scrollToEl, wait }), await c.fn(pb, { snap, scrollTo, scrollToEl, wait })];
  const ja = JSON.stringify(ra), jb = JSON.stringify(rb);
  console.log((ja === jb ? 'SAME ' : 'DIFF ') + `${c.path} [${c.w || 1352}] ${c.name}` + (pb.errors.length ? '  BLADE JS ERRORS: ' + pb.errors.join(' | ') : ''));
  if (ja !== jb) { console.log('  next :', ja.slice(0, 900)); console.log('  blade:', jb.slice(0, 900)); }
  await pa.context().close(); await pb.context().close();
}
await br.close();
