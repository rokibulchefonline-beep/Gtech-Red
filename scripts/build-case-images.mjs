// Draws the demo case-study cover images in code and saves them as WebP in public/case/.
// Run: npm run case-images
import { mkdirSync } from 'node:fs';
import sharp from 'sharp';
import { icon } from './visuals-lib.mjs';

const W = 900, H = 650;
const out = new URL('../public/case/', import.meta.url);
mkdirSync(out, { recursive: true });

// Deterministic pseudo-random so every run gives the same image.
const rnd = (seed) => () => (seed = (seed * 1664525 + 1013904223) % 4294967296) / 4294967296;

const bokeh = (seed, n, colors) => {
  const r = rnd(seed);
  return Array.from({ length: n }, () => {
    const rad = 14 + r() * 62;
    return `<circle cx="${(r() * W).toFixed(0)}" cy="${(r() * H).toFixed(0)}" r="${rad.toFixed(0)}" fill="${colors[Math.floor(r() * colors.length)]}" opacity="${(0.1 + r() * 0.28).toFixed(2)}"/>`;
  }).join('');
};

const base = (seed, c1, c2, glow, inner) => `<svg xmlns="http://www.w3.org/2000/svg" width="${W}" height="${H}" viewBox="0 0 ${W} ${H}">
<defs>
  <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="${c1}"/><stop offset="1" stop-color="${c2}"/></linearGradient>
  <radialGradient id="gl" cx=".72" cy=".3" r=".7"><stop offset="0" stop-color="${glow}" stop-opacity=".55"/><stop offset="1" stop-color="${glow}" stop-opacity="0"/></radialGradient>
  <filter id="blur"><feGaussianBlur stdDeviation="9"/></filter>
  <filter id="soft" x="-20%" y="-20%" width="140%" height="150%"><feDropShadow dx="0" dy="14" stdDeviation="16" flood-color="#000" flood-opacity=".45"/></filter>
  <linearGradient id="glass" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#fff" stop-opacity=".22"/><stop offset="1" stop-color="#fff" stop-opacity=".06"/></linearGradient>
  <linearGradient id="vig" x1="0" y1="0" x2="0" y2="1"><stop offset=".45" stop-color="#000" stop-opacity="0"/><stop offset="1" stop-color="#000" stop-opacity=".7"/></linearGradient>
</defs>
<rect width="${W}" height="${H}" fill="url(#bg)"/><rect width="${W}" height="${H}" fill="url(#gl)"/>
<g filter="url(#blur)">${bokeh(seed, 16, ['#ff5b6b', '#ffb3bb', '#ffffff', glow])}</g>
${inner}
<rect width="${W}" height="${H}" fill="url(#vig)"/></svg>`;

const glass = (x, y, w, h, r = 26, extra = '') =>
  `<rect x="${x}" y="${y}" width="${w}" height="${h}" rx="${r}" fill="url(#glass)" stroke="#fff" stroke-opacity=".3" stroke-width="2" filter="url(#soft)" ${extra}/>`;
const chip = (x, y, id, size = 44, col = '#fff') => icon(id, x, y, size, col, 1.8);
const bar = (x, base, h, w = 26, c = '#fff', o = 0.85) => `<rect x="${x}" y="${base - h}" width="${w}" height="${h}" rx="7" fill="${c}" opacity="${o}"/>`;

const scenes = {
  // Restaurant ordering platform: plate, cutlery, order phone.
  chefonline: base(11, '#1c0709', '#7d1024', '#ff6a3d', `
    <ellipse cx="640" cy="470" rx="190" ry="40" fill="#000" opacity=".35" filter="url(#blur)"/>
    <circle cx="640" cy="360" r="150" fill="#f4f0ea" opacity=".95" filter="url(#soft)"/><circle cx="640" cy="360" r="112" fill="none" stroke="#dcd3c6" stroke-width="6"/>
    <circle cx="640" cy="360" r="66" fill="#c4152d"/><circle cx="614" cy="338" r="18" fill="#ff8a95" opacity=".8"/><circle cx="668" cy="384" r="12" fill="#ffd0d5" opacity=".8"/>
    <g transform="translate(440 210)" fill="#f4f0ea" opacity=".9"><rect x="0" y="0" width="14" height="230" rx="7"/><rect x="-16" y="0" width="8" height="90" rx="4"/><rect x="22" y="0" width="8" height="90" rx="4"/></g>
    ${glass(90, 190, 210, 330, 34)}${chip(150, 226, 'lucide:utensils-crossed', 60)}
    <rect x="118" y="318" width="154" height="16" rx="8" fill="#fff" opacity=".8"/><rect x="118" y="348" width="110" height="12" rx="6" fill="#fff" opacity=".5"/>
    <rect x="118" y="420" width="154" height="54" rx="27" fill="#e8202f"/>${chip(176, 432, 'lucide:shopping-bag', 30)}`),

  // Professional services firm: skyline, scales, document.
  'salik-and-co': base(23, '#0d0d12', '#4a1626', '#e8202f', `
    <g fill="#fff" opacity=".13"><rect x="50" y="330" width="90" height="320"/><rect x="150" y="250" width="110" height="400"/><rect x="270" y="360" width="80" height="290"/><rect x="360" y="200" width="120" height="450"/><rect x="490" y="300" width="100" height="350"/><rect x="600" y="240" width="110" height="410"/><rect x="720" y="340" width="130" height="310"/></g>
    <g fill="#ffd9dd" opacity=".55">${Array.from({ length: 34 }, (_, i) => `<rect x="${170 + (i % 6) * 16}" y="${280 + Math.floor(i / 6) * 26}" width="8" height="12" rx="2"/>`).join('')}${Array.from({ length: 24 }, (_, i) => `<rect x="${380 + (i % 5) * 20}" y="${230 + Math.floor(i / 5) * 30}" width="9" height="13" rx="2"/>`).join('')}</g>
    ${glass(560, 90, 260, 200, 30)}${chip(650, 112, 'lucide:scale', 84)}
    <rect x="590" y="220" width="200" height="14" rx="7" fill="#fff" opacity=".7"/><rect x="590" y="248" width="140" height="10" rx="5" fill="#fff" opacity=".4"/>
    ${glass(90, 90, 210, 130, 26)}${chip(118, 118, 'lucide:file-text', 40)}<rect x="176" y="126" width="100" height="12" rx="6" fill="#fff" opacity=".75"/><rect x="176" y="152" width="70" height="10" rx="5" fill="#fff" opacity=".45"/>`),

  // Hospitality awards event: spotlights, stage, trophy, stars.
  arta: base(37, '#120a12', '#5d1030', '#ff3b5c', `
    <polygon points="200,-10 90,650 330,650" fill="#fff" opacity=".07"/><polygon points="450,-10 330,650 580,650" fill="#fff" opacity=".09"/><polygon points="720,-10 610,650 850,650" fill="#fff" opacity=".07"/>
    <ellipse cx="450" cy="560" rx="360" ry="60" fill="#e8202f" opacity=".35"/><ellipse cx="450" cy="548" rx="300" ry="42" fill="#7d1024" opacity=".8"/>
    <g transform="translate(450 330)"><path d="M-70 -110 H70 V-40 A70 70 0 0 1 -70 -40 Z" fill="#ffd28a"/><path d="M-70 -90 H-108 A24 24 0 0 0 -84 -40 H-70" fill="none" stroke="#ffd28a" stroke-width="14"/><path d="M70 -90 H108 A24 24 0 0 1 84 -40 H70" fill="none" stroke="#ffd28a" stroke-width="14"/><rect x="-14" y="30" width="28" height="70" fill="#ffd28a"/><rect x="-62" y="100" width="124" height="30" rx="8" fill="#e8b45f"/></g>
    ${[[130, 150, 46], [770, 190, 56], [230, 380, 32], [690, 420, 38], [560, 110, 30]].map(([x, y, s]) => chip(x, y, 'lucide:sparkles', s, '#ffd9dd')).join('')}
    ${glass(90, 470, 250, 90, 24)}${chip(112, 490, 'lucide:star', 48, '#ffd28a')}${chip(172, 490, 'lucide:star', 48, '#ffd28a')}${chip(232, 490, 'lucide:star', 48, '#ffd28a')}`),

  // Restaurant booking: table from above, calendar card.
  'table-booking': base(41, '#14090b', '#6e1224', '#ff7a45', `
    <circle cx="300" cy="360" r="140" fill="#2a1518" filter="url(#soft)"/><circle cx="300" cy="360" r="128" fill="#3a1d22"/><circle cx="300" cy="360" r="52" fill="#e8202f" opacity=".85"/>
    ${[0, 60, 120, 180, 240, 300].map((a) => `<circle cx="${300 + 190 * Math.cos((a * Math.PI) / 180)}" cy="${360 + 190 * Math.sin((a * Math.PI) / 180)}" r="30" fill="#f1ede6" opacity=".9"/>`).join('')}
    ${glass(520, 150, 300, 340, 32)}<rect x="520" y="150" width="300" height="72" rx="32" fill="#e8202f"/><rect x="520" y="190" width="300" height="32" fill="#e8202f"/>
    ${chip(548, 166, 'lucide:calendar-days', 40)}<rect x="606" y="178" width="120" height="14" rx="7" fill="#fff" opacity=".9"/>
    ${Array.from({ length: 21 }, (_, i) => `<circle cx="${556 + (i % 7) * 40}" cy="${262 + Math.floor(i / 7) * 46}" r="${i === 9 ? 19 : 4}" fill="${i === 9 ? '#e8202f' : '#fff'}" opacity="${i === 9 ? 1 : 0.55}"/>`).join('')}
    ${chip(672, 380, 'lucide:circle-check', 84, '#fff')}`),

  // Analytics growth.
  'demo-brand-five': base(53, '#0f1016', '#5a1224', '#e8202f', `
    ${glass(90, 120, 720, 400, 34)}
    ${[0.3, 0.42, 0.38, 0.6, 0.72, 0.86].map((v, i) => bar(150 + i * 72, 480, 300 * v, 40, i > 3 ? '#e8202f' : '#fff', i > 3 ? 1 : 0.7)).join('')}
    <path d="M150 400 C 250 380, 300 350, 400 310 S 600 230, 720 170" fill="none" stroke="#ffd9dd" stroke-width="7" stroke-linecap="round"/><circle cx="720" cy="170" r="14" fill="#fff" stroke="#e8202f" stroke-width="7"/>
    ${chip(700, 60, 'lucide:trending-up', 84, '#ffd9dd')}${chip(110, 60, 'lucide:chart-column-increasing', 50, '#fff')}`),

  // Growth / launch.
  'demo-brand-six': base(67, '#100a12', '#7a1028', '#ff5468', `
    <path d="M-20 560 C 200 520, 360 420, 520 250 S 800 60, 930 30" fill="none" stroke="#fff" stroke-opacity=".25" stroke-width="60" stroke-linecap="round"/>
    <path d="M-20 560 C 200 520, 360 420, 520 250 S 800 60, 930 30" fill="none" stroke="#e8202f" stroke-width="10" stroke-linecap="round" stroke-dasharray="4 26"/>
    ${chip(560, 120, 'lucide:rocket', 190, '#fff')}
    ${glass(90, 120, 230, 120, 26)}${chip(116, 146, 'lucide:users', 48)}<rect x="180" y="152" width="110" height="14" rx="7" fill="#fff" opacity=".85"/><rect x="180" y="180" width="70" height="10" rx="5" fill="#fff" opacity=".5"/>
    ${glass(120, 400, 260, 120, 26)}${chip(146, 426, 'lucide:target', 48)}<rect x="210" y="432" width="130" height="14" rx="7" fill="#fff" opacity=".85"/><rect x="210" y="460" width="90" height="10" rx="5" fill="#fff" opacity=".5"/>`),
};

for (const [slug, svg] of Object.entries(scenes)) {
  await sharp(Buffer.from(svg)).webp({ quality: 82, effort: 5 }).toFile(new URL(`${slug}.webp`, out).pathname);
  console.log(slug);
}
