// Section background images drawn in code -> public/bg/*.webp   Run: npm run backgrounds
import sharp from 'sharp';

const W = 1920, H = 1080;
const rnd = (s) => () => (s = (s * 1664525 + 1013904223) % 4294967296) / 4294967296;
const blobs = (seed, n, colors, rMin, rMax, op) => { const r = rnd(seed); return Array.from({ length: n }, () =>
  `<circle cx="${(r() * W).toFixed(0)}" cy="${(r() * H).toFixed(0)}" r="${(rMin + r() * (rMax - rMin)).toFixed(0)}" fill="${colors[Math.floor(r() * colors.length)]}" opacity="${op}"/>`).join(''); };
const dots = (op) => `<pattern id="d" width="28" height="28" patternUnits="userSpaceOnUse"><circle cx="2" cy="2" r="1.6" fill="#b0122c" opacity="${op}"/></pattern><rect width="${W}" height="${H}" fill="url(#d)"/>`;
const grid = (op) => `<pattern id="g" width="80" height="80" patternUnits="userSpaceOnUse"><path d="M80 0H0V80" fill="none" stroke="#fff" stroke-opacity="${op}"/></pattern><rect width="${W}" height="${H}" fill="url(#g)"/>`;
const svg = (body) => `<svg xmlns="http://www.w3.org/2000/svg" width="${W}" height="${H}" viewBox="0 0 ${W} ${H}"><defs><filter id="b"><feGaussianBlur stdDeviation="90"/></filter></defs>${body}</svg>`;

const bgs = {
  'light-mesh': svg(`<rect width="${W}" height="${H}" fill="#fff8f8"/><g filter="url(#b)">${blobs(3, 7, ['#ffc9cf', '#ffe3e6', '#ffd6c9'], 220, 420, 0.9)}</g>${dots(0.12)}`),
  'dark-mesh': svg(`<rect width="${W}" height="${H}" fill="#0b0b0e"/><g filter="url(#b)">${blobs(9, 5, ['#e8202f', '#7a0f22', '#3a0a14'], 200, 380, 0.55)}</g>${grid(0.05)}`),
  'red-wave': svg(`<defs><linearGradient id="rg" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#e8202f"/><stop offset="1" stop-color="#6e0c1d"/></linearGradient></defs>
    <rect width="${W}" height="${H}" fill="url(#rg)"/>
    ${[0, 1, 2, 3, 4, 5].map((i) => `<path d="M0 ${300 + i * 110} C 480 ${180 + i * 110}, 960 ${480 + i * 110}, 1920 ${260 + i * 110}" fill="none" stroke="#fff" stroke-opacity="${0.05 + i * 0.012}" stroke-width="2"/>`).join('')}
    <circle cx="1700" cy="160" r="260" fill="#fff" opacity=".06"/><circle cx="180" cy="980" r="320" fill="#000" opacity=".12"/>`),
  'soft-shapes': svg(`<rect width="${W}" height="${H}" fill="#faf8f8"/>
    <path d="M1920 0 V420 C 1700 520, 1500 300, 1260 360 S 980 120, 900 0 Z" fill="#ffe3e6"/>
    <path d="M0 1080 V700 C 220 620, 380 820, 640 760 S 900 960, 1000 1080 Z" fill="#ffe9ec"/>
    <circle cx="1650" cy="860" r="120" fill="none" stroke="#ffc9cf" stroke-width="3"/><circle cx="260" cy="200" r="70" fill="none" stroke="#ffc9cf" stroke-width="3"/>${dots(0.08)}`),
};
for (const [n, s] of Object.entries(bgs)) { await sharp(Buffer.from(s)).webp({ quality: 72, effort: 5 }).toFile(new URL(`../public/bg/${n}.webp`, import.meta.url).pathname); console.log(n); }
