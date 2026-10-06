// Full-page screenshots of every page at three widths on the Next.js site (NEXT_URL) and the Blade site
// (BLADE_URL), compared pixel by pixel. Prints one JSON line per page and width; pages that differ are saved to
// out-dir. Animated images, videos and the logos (resampled by the Next.js image optimiser) are hidden on both
// sides, keeping their space, so only real differences show.
// Usage: node scripts/parity/visual.mjs urls.txt out-dir   (WIDTHS=1352,900,390 CONC=3)
import { chromium } from 'playwright';
import fs from 'fs';
import path from 'path';
const [urlsFile, outDir] = process.argv.slice(2);
const urls = fs.readFileSync(urlsFile, 'utf8').trim().split('\n');
const widths = (process.env.WIDTHS || '1352,900,390').split(',').map(Number);
// Animated images in public/ (WebP with an ANIM chunk, APNG, multi-frame GIF).
const pub = new URL('../../public/', import.meta.url).pathname, anim = [];
(function walk(dir) {
  for (const f of fs.readdirSync(dir, { withFileTypes: true })) {
    const p = path.join(dir, f.name);
    if (f.isDirectory()) walk(p);
    else if (/\.(webp|png|gif)$/.test(f.name)) { const b = fs.readFileSync(p); if ((f.name.endsWith('.webp') && b.includes('ANIM')) || (f.name.endsWith('.png') && b.includes('acTL')) || (f.name.endsWith('.gif') && b.indexOf(Buffer.from([0x21, 0xf9, 0x04]), b.indexOf(Buffer.from([0x21, 0xf9, 0x04])) + 1) > 0)) anim.push('/' + path.relative(pub, p)); }
  }
})(pub);
const A = process.env.NEXT_URL || 'http://localhost:3130', B = process.env.BLADE_URL || 'http://127.0.0.1:8090';
const br = await chromium.launch();
async function shot(base, path, w) {
  const ctx = await br.newContext({ viewport: { width: w, height: 900 }, reducedMotion: 'reduce' });
  const p = await ctx.newPage();
  await p.route((u) => { const s = u.toString(); return !(s.startsWith(base) || /fonts\.(googleapis|gstatic)\.com/.test(s)); }, (r) => r.abort());
  await p.goto(base + path, { waitUntil: 'networkidle' });
  await p.waitForSelector('.ck .ck-ghost', { state: 'visible', timeout: 4000 }).then((b) => b.click()).catch(() => {});
  const h = await p.evaluate(() => document.documentElement.scrollHeight);
  await p.addStyleTag({ content: 'html{scroll-behavior:auto!important}' });
  for (let y = 0; y < h; y += 600) { await p.evaluate((y) => scrollTo({ top: y, behavior: 'instant' }), y); await p.waitForTimeout(40); }
  await p.waitForTimeout(3200);
  await p.evaluate((anim) => {
    scrollTo({ top: 0, behavior: 'instant' });
    document.querySelectorAll('[data-rv]').forEach((e) => e.classList.add('rv-in'));
    const hide = (e) => { e.style.visibility = 'hidden'; };
    document.querySelectorAll('img').forEach((i) => { const s = decodeURIComponent(i.getAttribute('src') || ''); if (anim.some((a) => s.includes(a))) hide(i); });
    document.querySelectorAll('video, .hdr .logo img, .ft-logo img').forEach(hide); // video frames; logo is resampled by the Next image optimiser
  }, anim);
  await p.waitForTimeout(600);
  const png = await p.screenshot({ fullPage: true, animations: 'disabled', caret: 'hide' });
  await ctx.close(); return png;
}
const cmp = await br.newPage();
async function diff(x, y) {
  return cmp.evaluate(async ([a, c]) => {
    const load = (s) => new Promise((r) => { const i = new Image(); i.onload = () => r(i); i.src = 'data:image/png;base64,' + s; });
    const [I, J] = await Promise.all([load(a), load(c)]);
    const W = Math.min(I.width, J.width), H = Math.min(I.height, J.height);
    const cv = (i) => { const k = document.createElement('canvas'); k.width = W; k.height = H; const g = k.getContext('2d'); g.drawImage(i, 0, 0); return g.getImageData(0, 0, W, H).data; };
    const da = cv(I), db = cv(J); let n = 0; const rows = new Set(); let x0 = 1e9, y0 = 1e9, x1 = -1, y1 = -1;
    for (let i = 0; i < da.length; i += 4) if (Math.abs(da[i] - db[i]) + Math.abs(da[i + 1] - db[i + 1]) + Math.abs(da[i + 2] - db[i + 2]) > 24) { n++; const p = i / 4, x = p % W, y = (p / W) | 0; rows.add((y / 50) | 0); x0 = Math.min(x0, x); y0 = Math.min(y0, y); x1 = Math.max(x1, x); y1 = Math.max(y1, y); }
    return { size: [I.width, I.height, J.width, J.height], n, pct: +(100 * n / (W * H)).toFixed(4), box: n ? [x0, y0, x1, y1] : null, bands: [...rows].sort((a, b) => a - b).map((r) => r * 50) };
  }, [x.toString('base64'), y.toString('base64')]);
}
const jobs = []; for (const u of urls) for (const w of widths) jobs.push([u, w]);
const conc = +(process.env.CONC || 3); let k = 0;
async function worker() {
  while (k < jobs.length) {
    const [u, w] = jobs[k++];
    try {
      const [a, b] = await Promise.all([shot(A, u, w), shot(B, u, w)]);
      const r = await diff(a, b);
      const sameSize = r.size[0] === r.size[2] && r.size[1] === r.size[3];
      if (r.n || !sameSize) { const n = (u === '/' ? 'home' : u.slice(1).replace(/\W+/g, '_')) + '_' + w; fs.writeFileSync(`${outDir}/${n}_next.png`, a); fs.writeFileSync(`${outDir}/${n}_blade.png`, b); }
      console.log(JSON.stringify({ u, w, sameSize, ...r }));
    } catch (e) { console.log(JSON.stringify({ u, w, error: String(e).slice(0, 200) })); }
  }
}
await Promise.all(Array.from({ length: conc }, worker));
await br.close();
