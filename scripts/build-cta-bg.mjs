// Draws the dark, out-of-focus "person working on a laptop" background for the inquiry section.
// Run: npm run cta-bg   ->  public/cta-bg.webp
import sharp from 'sharp';

const W = 1800, H = 900;
const rnd = ((s) => () => (s = (s * 1664525 + 1013904223) % 4294967296) / 4294967296)(7);
const bokeh = Array.from({ length: 22 }, () => `<circle cx="${(rnd() * W).toFixed(0)}" cy="${(rnd() * H).toFixed(0)}" r="${(20 + rnd() * 80).toFixed(0)}" fill="${rnd() > 0.55 ? '#e8202f' : '#ffffff'}" opacity="${(0.05 + rnd() * 0.12).toFixed(2)}"/>`).join('');
const code = Array.from({ length: 26 }, (_, i) => {
  const w = 140 + ((i * 97) % 300), x = 60 + (i % 3) * 30;
  return `<rect x="${x}" y="${80 + i * 34}" width="${w}" height="12" rx="6" fill="${i % 5 === 0 ? '#e8202f' : '#9aa0ae'}" opacity="${i % 5 === 0 ? 0.9 : 0.55}"/>`;
}).join('');

const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="${W}" height="${H}" viewBox="0 0 ${W} ${H}">
<defs>
  <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#0a0a0d"/><stop offset="1" stop-color="#1f0d12"/></linearGradient>
  <radialGradient id="glow" cx=".7" cy=".45" r=".5"><stop offset="0" stop-color="#e8202f" stop-opacity=".35"/><stop offset="1" stop-color="#e8202f" stop-opacity="0"/></radialGradient>
  <linearGradient id="scr" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#2b2f3a"/><stop offset="1" stop-color="#12141a"/></linearGradient>
  <filter id="b1"><feGaussianBlur stdDeviation="6"/></filter>
  <filter id="b2"><feGaussianBlur stdDeviation="22"/></filter>
  <linearGradient id="fade" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="#000" stop-opacity=".78"/><stop offset=".55" stop-color="#000" stop-opacity=".25"/><stop offset="1" stop-color="#000" stop-opacity=".45"/></linearGradient>
</defs>
<rect width="${W}" height="${H}" fill="url(#bg)"/><rect width="${W}" height="${H}" fill="url(#glow)"/>
<g filter="url(#b2)">${bokeh}</g>
<g filter="url(#b1)" transform="translate(760 130) rotate(-8 400 300) skewX(-6)">
  <rect x="0" y="0" width="880" height="520" rx="26" fill="url(#scr)" stroke="#3a3f4c" stroke-width="10"/>
  <g transform="translate(40 20)">${code}</g>
  <g transform="translate(520 90)" opacity=".95">
    <rect x="0" y="0" width="300" height="190" rx="16" fill="#0f1116" stroke="#2c303b" stroke-width="3"/>
    ${[0.3, 0.45, 0.4, 0.62, 0.78, 0.95].map((v, i) => `<rect x="${24 + i * 44}" y="${160 - 120 * v}" width="26" height="${120 * v}" rx="6" fill="${i > 3 ? '#e8202f' : '#5b6070'}"/>`).join('')}
    <path d="M30 140 C 90 120, 130 110, 180 84 S 250 40, 280 22" fill="none" stroke="#ffd0d5" stroke-width="6" stroke-linecap="round"/>
  </g>
  <path d="M-70 545 H 950 L 900 585 H -20 Z" fill="#20232c"/><rect x="330" y="548" width="220" height="14" rx="7" fill="#3a3f4c"/>
</g>
<g filter="url(#b1)" opacity=".8"><ellipse cx="1380" cy="820" rx="360" ry="120" fill="#000"/><ellipse cx="1180" cy="760" rx="150" ry="70" fill="#2a2d36"/><ellipse cx="1500" cy="780" rx="140" ry="64" fill="#23262f"/></g>
<rect width="${W}" height="${H}" fill="url(#fade)"/>
</svg>`;

await sharp(Buffer.from(svg)).webp({ quality: 80, effort: 5 }).toFile(new URL('../public/cta-bg.webp', import.meta.url).pathname);
console.log('cta-bg.webp written');
