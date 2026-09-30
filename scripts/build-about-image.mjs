// Draws the tall "Who We Are" image (portrait) and saves public/about-tall.webp.
// Run: npm run about-image
import sharp from 'sharp';
import { icon } from './visuals-lib.mjs';

const W = 900, H = 1200;
const rnd = ((s) => () => (s = (s * 1664525 + 1013904223) % 4294967296) / 4294967296)(19);
const bokeh = Array.from({ length: 20 }, () => `<circle cx="${(rnd() * W).toFixed(0)}" cy="${(rnd() * H).toFixed(0)}" r="${(20 + rnd() * 90).toFixed(0)}" fill="${rnd() > 0.5 ? '#ff5b6b' : '#ffffff'}" opacity="${(0.08 + rnd() * 0.2).toFixed(2)}"/>`).join('');
const glass = (x, y, w, h, r = 30) => `<rect x="${x}" y="${y}" width="${w}" height="${h}" rx="${r}" fill="url(#glass)" stroke="#fff" stroke-opacity=".3" stroke-width="2" filter="url(#soft)"/>`;
const ic = (id, x, y, s, c = '#fff') => icon(id, x, y, s, c, 1.8);
const bars = [0.3, 0.45, 0.4, 0.62, 0.78, 0.95].map((v, i) => `<rect x="${86 + i * 52}" y="${470 - 170 * v}" width="30" height="${170 * v}" rx="8" fill="${i > 3 ? '#e8202f' : '#fff'}" opacity="${i > 3 ? 1 : 0.75}"/>`).join('');
const avatars = [[150, '#e8202f'], [290, '#ff8a95'], [430, '#ffffff'], [570, '#b0122c']].map(([x, c], i) => `<circle cx="${x + 70}" cy="640" r="44" fill="${c}" opacity="${i === 2 ? 0.9 : 1}"/><circle cx="${x + 70}" cy="628" r="16" fill="${i === 2 ? '#b0122c' : '#fff'}" opacity=".9"/><path d="M${x + 38} 668 a32 26 0 0 1 64 0" fill="${i === 2 ? '#b0122c' : '#fff'}" opacity=".9"/>`).join('');
const skyline = Array.from({ length: 11 }, (_, i) => { const h = 120 + ((i * 83) % 230); return `<rect x="${i * 84 - 10}" y="${H - h}" width="72" height="${h}" fill="#fff" opacity="${0.06 + (i % 3) * 0.03}"/>`; }).join('');

const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="${W}" height="${H}" viewBox="0 0 ${W} ${H}">
<defs>
  <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#e8202f"/><stop offset=".55" stop-color="#8c0f26"/><stop offset="1" stop-color="#1a0709"/></linearGradient>
  <linearGradient id="glass" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#fff" stop-opacity=".24"/><stop offset="1" stop-color="#fff" stop-opacity=".07"/></linearGradient>
  <filter id="blur"><feGaussianBlur stdDeviation="14"/></filter>
  <filter id="soft" x="-20%" y="-20%" width="140%" height="150%"><feDropShadow dx="0" dy="16" stdDeviation="18" flood-color="#000" flood-opacity=".35"/></filter>
  <linearGradient id="vig" x1="0" y1="0" x2="0" y2="1"><stop offset=".6" stop-color="#000" stop-opacity="0"/><stop offset="1" stop-color="#000" stop-opacity=".55"/></linearGradient>
</defs>
<rect width="${W}" height="${H}" fill="url(#bg)"/>
<g filter="url(#blur)">${bokeh}</g>
${skyline}
${glass(60, 70, 780, 460)}
${ic('lucide:chart-column-increasing', 90, 100, 54)}<rect x="164" y="112" width="220" height="18" rx="9" fill="#fff" opacity=".85"/><rect x="164" y="142" width="140" height="12" rx="6" fill="#fff" opacity=".5"/>
${bars}
<path d="M90 430 C 230 400, 330 360, 450 300 S 660 190, 790 140" fill="none" stroke="#ffd0d5" stroke-width="8" stroke-linecap="round"/><circle cx="790" cy="140" r="15" fill="#fff" stroke="#e8202f" stroke-width="8"/>
${glass(60, 560, 780, 220)}${avatars}
<rect x="120" y="700" width="180" height="14" rx="7" fill="#fff" opacity=".8"/><rect x="330" y="700" width="140" height="14" rx="7" fill="#fff" opacity=".55"/><rect x="500" y="700" width="180" height="14" rx="7" fill="#fff" opacity=".8"/>
${glass(60, 820, 370, 190)}${ic('lucide:rocket', 90, 852, 84)}<rect x="196" y="866" width="190" height="16" rx="8" fill="#fff" opacity=".85"/><rect x="196" y="898" width="120" height="12" rx="6" fill="#fff" opacity=".5"/><rect x="90" y="960" width="280" height="12" rx="6" fill="#fff" opacity=".45"/>
${glass(470, 820, 370, 190)}${ic('lucide:target', 500, 852, 84)}<rect x="606" y="866" width="190" height="16" rx="8" fill="#fff" opacity=".85"/><rect x="606" y="898" width="120" height="12" rx="6" fill="#fff" opacity=".5"/><rect x="500" y="960" width="280" height="12" rx="6" fill="#fff" opacity=".45"/>
<rect width="${W}" height="${H}" fill="url(#vig)"/></svg>`;

await sharp(Buffer.from(svg)).webp({ quality: 84, effort: 5 }).toFile(new URL('../public/about-tall.webp', import.meta.url).pathname);
console.log('about-tall.webp written');
