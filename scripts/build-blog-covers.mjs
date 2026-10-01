// Blog cover images drawn in code -> public/posts/<slug>.webp (1200x675).
// Run: node scripts/build-blog-covers.mjs   (re-run after adding demo posts)
import { mkdirSync } from 'node:fs';
import sharp from 'sharp';
import { icon } from './visuals-lib.mjs';
import { demoPosts } from '../content/posts.ts';

const W = 1200, H = 675;
const icons = { SEO: 'lucide:search', 'Local SEO': 'lucide:map-pin', 'Web Design': 'lucide:monitor', 'Paid Ads': 'simple-icons:googleads', 'Social Media': 'lucide:share-2', Software: 'lucide:code-xml', Insights: 'lucide:lightbulb' };
const esc = (s) => s.replace(/&/g, '&amp;').replace(/</g, '&lt;');
const wrap = (s, n) => s.split(' ').reduce((ls, w) => { const l = ls[ls.length - 1]; if (l && (l + ' ' + w).length <= n) ls[ls.length - 1] = l + ' ' + w; else ls.push(w); return ls; }, []);

function cover({ title, category }) {
  const lines = wrap(title, 22).slice(0, 4);
  const fs = lines.length > 3 ? 46 : 52;
  return `<svg xmlns="http://www.w3.org/2000/svg" width="${W}" height="${H}" viewBox="0 0 ${W} ${H}">
  <defs><linearGradient id="bg" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#1a0b0e"/><stop offset="1" stop-color="#0b0b0d"/></linearGradient>
  <linearGradient id="rg" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#ff3b4e"/><stop offset="1" stop-color="#b0122c"/></linearGradient>
  <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse"><path d="M40 0H0V40" fill="none" stroke="#ffffff" stroke-opacity=".05"/></pattern></defs>
  <rect width="${W}" height="${H}" fill="url(#bg)"/><rect width="${W}" height="${H}" fill="url(#grid)"/>
  <circle cx="1010" cy="140" r="260" fill="#e8202f" opacity=".16"/><circle cx="1080" cy="600" r="180" fill="#e8202f" opacity=".1"/>
  <rect x="820" y="190" width="300" height="300" fill="url(#rg)"/>${icon(icons[category] ?? icons.Insights, 895, 265, 150, '#fff', 1.6)}
  <rect x="70" y="90" width="${category.length * 14 + 44}" height="44" fill="#e8202f"/><text x="92" y="120" font-family="Inter, Arial, sans-serif" font-size="20" font-weight="700" fill="#fff" letter-spacing="1">${esc(category.toUpperCase())}</text>
  ${lines.map((l, i) => `<text x="70" y="${215 + i * (fs + 14)}" font-family="Inter, Arial, sans-serif" font-size="${fs}" font-weight="800" fill="#fff">${esc(l)}</text>`).join('')}
  <rect x="70" y="560" width="60" height="6" fill="#e8202f"/><text x="70" y="610" font-family="Inter, Arial, sans-serif" font-size="24" font-weight="700" fill="#fff">GTech Digital <tspan fill="#9a9aa5" font-weight="500">· Insights</tspan></text>
</svg>`;
}

mkdirSync(new URL('../public/posts/', import.meta.url), { recursive: true });
for (const p of [...demoPosts, { slug: 'default', title: 'Insights from the GTech Digital team', category: 'Insights' }]) {
  await sharp(Buffer.from(cover(p))).webp({ quality: 86 }).toFile(new URL(`../public/posts/${p.slug}.webp`, import.meta.url).pathname);
  console.log(p.slug);
}
