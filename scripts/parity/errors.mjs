// Loads every page of the Blade site and lists JS errors and failed requests to the site itself.
// Usage: node scripts/parity/errors.mjs urls.txt (one path per line)
import { chromium } from 'playwright';
import fs from 'fs';
const BASE = process.env.BLADE_URL || 'http://127.0.0.1:8090';
const urls = fs.readFileSync(process.argv[2], 'utf8').trim().split('\n');
const br = await chromium.launch();
const ctx = await br.newContext({ viewport: { width: 1352, height: 900 } });
let bad = 0;
for (const u of urls) {
  const p = await ctx.newPage(); const errs = [];
  p.on('pageerror', (e) => errs.push('JS ' + e.message));
  p.on('console', (m) => { if (m.type() === 'error' && !/youtube|ERR_|net::/i.test(m.text())) errs.push('console ' + m.text()); });
  p.on('response', (r) => { const x = r.url(); if (r.status() >= 400 && x.startsWith(BASE)) errs.push(r.status() + ' ' + x.replace(BASE, '')); });
  await p.route(/youtube\.com|youtube-nocookie|googletagmanager|connect\.facebook\.net/, (r) => r.abort());
  await p.goto(BASE + u, { waitUntil: 'networkidle' });
  await p.evaluate(async () => { for (let y = 0; y < document.documentElement.scrollHeight; y += 700) { scrollTo(0, y); await new Promise((r) => setTimeout(r, 40)); } });
  await p.waitForTimeout(400);
  if (errs.length) { bad++; console.log(u, '\n   ' + [...new Set(errs)].join('\n   ')); }
  await p.close();
}
console.log('pages', urls.length, 'with problems', bad);
await br.close();
