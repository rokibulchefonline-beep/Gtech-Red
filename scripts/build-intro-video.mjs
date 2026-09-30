// Renders the 30-second Gtech company intro (drawn in code) to public/videos/intro.mp4 + poster.
// Needs an ffmpeg binary with libx264:  FFMPEG=/path/to/ffmpeg npm run intro-video
// Options: --frame=13   writes one PNG frame at t=13s to public/videos/frame.png (preview, no encode)
import { spawn } from 'node:child_process';
import { mkdirSync } from 'node:fs';
import sharp from 'sharp';
import { icon } from './visuals-lib.mjs';
import { site, stats } from './video-data.mjs';

const W = 1280, H = 720, FPS = 24, DUR = 30, FRAMES = FPS * DUR;
const RED = '#e8202f', DARK = '#b0122c';
const out = new URL('../public/videos/', import.meta.url);
mkdirSync(out, { recursive: true });

const clamp = (x, a = 0, b = 1) => Math.min(b, Math.max(a, x));
const ss = (t, a, b) => clamp((t - a) / (b - a));
const eo = (x) => 1 - Math.pow(1 - x, 3);
const E = (t, a, b) => eo(ss(t, a, b));
const back = (x) => { const c = 1.70158; return 1 + (c + 1) * Math.pow(x - 1, 3) + c * Math.pow(x - 1, 2); };
const F = 'Liberation Sans, Arial, sans-serif';
const txt = (x, y, s, size, fill = '#fff', w = 700, anchor = 'middle', extra = '') =>
  `<text x="${x}" y="${y}" font-family="${F}" font-size="${size}" font-weight="${w}" fill="${fill}" text-anchor="${anchor}" ${extra}>${s}</text>`;
const fade = (t, a, len = 0.6) => E(t, a, a + len);
const rise = (t, a, inner, dy = 30, len = 0.7) => { const p = E(t, a, a + len); return `<g opacity="${p.toFixed(3)}" transform="translate(0 ${((1 - p) * dy).toFixed(1)})">${inner}</g>`; };
const pop = (t, a, cx, cy, inner, len = 0.6) => { const p = ss(t, a, a + len); const s = Math.max(0.001, back(p)); return `<g opacity="${clamp(p * 2).toFixed(3)}" transform="translate(${cx} ${cy}) scale(${s.toFixed(3)}) translate(${-cx} ${-cy})">${inner}</g>`; };
const glass = (x, y, w, h, r = 22) => `<rect x="${x}" y="${y}" width="${w}" height="${h}" rx="${r}" fill="#fff" fill-opacity=".07" stroke="#fff" stroke-opacity=".16" stroke-width="2"/>`;

function wordmark(cx, cy, s = 1) {
  return `<g transform="translate(${cx} ${cy}) scale(${s})">
    <g transform="translate(-235 -62)"><polygon points="14,0 110,0 96,118 0,118" fill="${RED}"/><text x="52" y="86" font-family="${F}" font-size="84" font-weight="800" fill="#fff" text-anchor="middle">G</text></g>
    <text x="-108" y="34" font-family="${F}" font-size="112" font-weight="800" fill="#fff">Tech</text>
    <text x="-100" y="78" font-family="${F}" font-size="22" font-weight="700" fill="${RED}" letter-spacing="17">DIGITAL</text></g>`;
}

const sceneAlpha = (t, a, b, last = false) => (t < a || t > b ? 0 : ss(t, a, a + 0.5) * (last ? 1 - ss(t, b - 0.6, b) : 1 - ss(t, b - 0.5, b)));

// ---- scenes (t is absolute seconds) ---------------------------------------
function s1(t) { // 0-5 logo
  const l = t, sc = back(ss(l, 0.3, 1.3));
  return `
    ${txt(640, 250, 'WELCOME TO', 22, '#c9c9d0', 700, 'middle', `letter-spacing="10" opacity="${fade(l, 0.6)}"`)}
    <g opacity="${clamp(ss(l, 0.3, 0.9) * 2).toFixed(2)}" transform="translate(0 0)">${wordmark(640, 380, Math.max(0.01, sc))}</g>
    <rect x="${640 - 260 * E(l, 1.3, 2.3)}" y="470" width="${520 * E(l, 1.3, 2.3)}" height="4" rx="2" fill="${RED}"/>
    ${rise(l, 1.9, txt(640, 540, 'Digital marketing  •  Web design  •  Software', 30, '#d9d9de', 500))}`;
}
function s2(t) { // 5-10 who we are
  const l = t - 5;
  const items = stats.slice(0, 4);
  return `
    ${txt(640, 190, 'WHO WE ARE', 20, RED, 700, 'middle', `letter-spacing="9" opacity="${fade(l, 0.2)}"`)}
    ${rise(l, 0.4, txt(640, 270, 'We help ambitious brands', 64, '#fff', 800))}
    ${rise(l, 0.8, txt(640, 346, 'grow faster online.', 64, RED, 800))}
    ${rise(l, 1.3, txt(640, 410, 'Strategy. Creative. Technology.', 28, '#c9c9d0', 500))}
    ${items.map((s, i) => {
      const x = 190 + i * 300, v = s.value * E(l, 1.8 + i * 0.15, 3.6 + i * 0.15);
      return rise(l, 1.7 + i * 0.15, `<rect x="${x - 26}" y="480" width="52" height="5" rx="2.5" fill="${RED}"/>${txt(x, 568, (s.decimals ? v.toFixed(s.decimals) : Math.round(v)) + s.suffix, 62, '#fff', 800)}${txt(x, 606, s.label, 20, '#a9a9b3', 500)}`, 24, 0.6);
    }).join('')}`;
}
const tiles = [['lucide:search', 'SEO', 'Rank higher on Google'], ['lucide:megaphone', 'Paid Media', 'Ads that pay back'], ['lucide:share-2', 'Social Media', 'Grow your audience'],
  ['lucide:monitor', 'Web Design', 'Fast, converting sites'], ['lucide:code-xml', 'Software', 'Built around you'], ['lucide:sparkles', 'Branding', 'Stand out, stay clear']];
function s3(t) { // 10-17 services
  const l = t - 10;
  return `
    ${rise(l, 0.2, txt(640, 120, 'Everything you need to grow', 50, '#fff', 800))}
    ${tiles.map(([ic, name, sub], i) => {
      const x = 130 + (i % 3) * 350, y = 200 + Math.floor(i / 3) * 210;
      return pop(l, 0.7 + i * 0.4, x + 150, y + 90, `${glass(x, y, 320, 180, 26)}<circle cx="${x + 62}" cy="${y + 90}" r="38" fill="${RED}"/>${icon(ic, x + 40, y + 68, 44, '#fff', 2)}${txt(x + 118, y + 88, name, 30, '#fff', 800, 'start')}${txt(x + 118, y + 122, sub, 18, '#a9a9b3', 500, 'start')}`);
    }).join('')}`;
}
function s4(t) { // 17-22 process
  const l = t - 17, xs = [260, 640, 1020];
  const steps = [['lucide:search', 'Discover', 'Audit, goals, plan'], ['lucide:rocket', 'Build', 'Design and launch'], ['lucide:trending-up', 'Grow', 'Measure and improve']];
  return `
    ${rise(l, 0.2, txt(640, 130, 'How we work', 50, '#fff', 800))}
    ${[0, 1].map((i) => `<line x1="${xs[i] + 82}" y1="360" x2="${xs[i] + 82 + (xs[i + 1] - xs[i] - 164) * E(l, 1.1 + i * 1.2, 2.1 + i * 1.2)}" y2="360" stroke="${RED}" stroke-width="5" stroke-dasharray="3 16" stroke-linecap="round"/>`).join('')}
    ${steps.map(([ic, name, sub], i) => pop(l, 0.6 + i * 1.1, xs[i], 360, `<circle cx="${xs[i]}" cy="360" r="82" fill="url(#rg)"/><circle cx="${xs[i]}" cy="360" r="98" fill="none" stroke="${RED}" stroke-opacity=".35" stroke-width="3"/>${icon(ic, xs[i] - 34, 326, 68, '#fff', 1.8)}${txt(xs[i], 508, name, 40, '#fff', 800)}${txt(xs[i], 548, sub, 22, '#a9a9b3', 500)}`)).join('')}`;
}
function s5(t) { // 22-27 results
  const l = t - 22;
  const cards = [['312%', 'return on ad spend', [0.1, 0.25, 0.3, 0.5, 0.62, 0.9]], ['3x', 'more qualified leads', [0.15, 0.2, 0.34, 0.4, 0.66, 0.85]], ['185%', 'organic traffic growth', [0.08, 0.2, 0.3, 0.48, 0.6, 0.92]]];
  return `
    ${rise(l, 0.2, txt(640, 130, 'Results that matter', 50, '#fff', 800))}
    ${cards.map(([big, label, pts], i) => {
      const x = 100 + i * 370, p = E(l, 0.9 + i * 0.3, 2.6 + i * 0.3);
      const n = parseFloat(big), shown = (n * p).toFixed(big === '3x' ? 1 : 0) + big.replace(/[0-9.]/g, '');
      const cx = (k) => x + 40 + (260 * k) / (pts.length - 1), cy = (v) => 470 - v * 110;
      const path = pts.map((v, k) => `${k ? 'L' : 'M'}${cx(k).toFixed(1)} ${cy(v).toFixed(1)}`).join(' ');
      return pop(l, 0.5 + i * 0.3, x + 170, 350, `${glass(x, 200, 340, 300, 26)}${txt(x + 34, 290, shown, 74, RED, 800, 'start')}${txt(x + 36, 328, label, 22, '#c9c9d0', 500, 'start')}
        <defs><clipPath id="cp${i}"><rect x="${x + 30}" y="350" width="${300 * p}" height="140"/></clipPath></defs>
        <g clip-path="url(#cp${i})"><path d="${path}" fill="none" stroke="#fff" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/><path d="${path} L${cx(pts.length - 1)} 480 L${cx(0)} 480 Z" fill="${RED}" fill-opacity=".25"/></g>`);
    }).join('')}`;
}
function s6(t) { // 27-30 CTA
  const l = t - 27, pulse = 1 + 0.03 * Math.sin(l * 7);
  return `
    ${rise(l, 0.1, txt(640, 300, 'Let’s grow', 96, '#fff', 800))}
    ${rise(l, 0.35, `<text x="640" y="410" font-family="${F}" font-size="96" font-weight="800" text-anchor="middle" fill="${RED}">together.</text>`)}
    ${pop(l, 0.9, 640, 520, `<g transform="translate(640 520) scale(${pulse}) translate(-640 -520)"><rect x="440" y="486" width="400" height="68" rx="34" fill="url(#rg)"/>${txt(640, 531, site.email, 28, '#fff', 700)}</g>`)}`;
}

const scenes = [[0, 5, s1], [5, 10, s2], [10, 17, s3], [17, 22, s4], [22, 27, s5], [27, 30, s6]];

function frame(t) {
  const glowX = 640 + 380 * Math.sin(t / 3.2), glowY = 360 + 160 * Math.cos(t / 4.1);
  const body = scenes.map(([a, b, fn], i) => {
    const al = sceneAlpha(t, a, b, i === scenes.length - 1 || i === 0 && false);
    const al2 = i === 0 ? (t < 0.05 ? 0 : 1 - ss(t, b - 0.5, b)) * ss(t, 0, 0.4) : al;
    return al2 > 0.001 ? `<g opacity="${al2.toFixed(3)}">${fn(t)}</g>` : '';
  }).join('');
  const brand = t > 5 ? `<g opacity="${Math.min(fade(t, 5.3), 1 - ss(t, 29.4, 30)).toFixed(2)}"><polygon points="52,34 78,34 74,64 48,64" fill="${RED}"/>${txt(63, 57, 'G', 26, '#fff', 800)}${txt(92, 56, 'TECH DIGITAL', 16, '#e9e9ee', 700, 'start', 'letter-spacing="4"')}</g>` : '';
  return `<svg xmlns="http://www.w3.org/2000/svg" width="${W}" height="${H}" viewBox="0 0 ${W} ${H}">
  <defs>
    <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#09090c"/><stop offset="1" stop-color="#1c0a10"/></linearGradient>
    <radialGradient id="gl" cx=".5" cy=".5" r=".5"><stop offset="0" stop-color="${RED}" stop-opacity=".38"/><stop offset="1" stop-color="${RED}" stop-opacity="0"/></radialGradient>
    <linearGradient id="rg" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#ff4b5c"/><stop offset="1" stop-color="${DARK}"/></linearGradient>
    <pattern id="grid" width="64" height="64" patternUnits="userSpaceOnUse"><path d="M64 0H0V64" fill="none" stroke="#fff" stroke-opacity=".045" stroke-width="1"/></pattern>
  </defs>
  <rect width="${W}" height="${H}" fill="url(#bg)"/><rect width="${W}" height="${H}" fill="url(#grid)"/>
  <circle cx="${glowX.toFixed(0)}" cy="${glowY.toFixed(0)}" r="420" fill="url(#gl)"/>
  ${body}${brand}
  <rect x="0" y="${H - 6}" width="${(W * t / DUR).toFixed(1)}" height="6" fill="${RED}"/>
  </svg>`;
}

const only = process.argv.find((a) => a.startsWith('--frame='));
if (only) {
  await sharp(Buffer.from(frame(Number(only.slice(8))))).png().toFile(new URL('frame.png', out).pathname);
  console.log('frame.png written');
  process.exit(0);
}

const ffmpeg = process.env.FFMPEG || 'ffmpeg';
const dest = new URL('intro.mp4', out).pathname;
const enc = spawn(ffmpeg, ['-y', '-loglevel', 'error', '-f', 'rawvideo', '-pix_fmt', 'rgb24', '-s', `${W}x${H}`, '-r', String(FPS), '-i', '-',
  '-c:v', 'libx264', '-preset', 'slow', '-crf', '24', '-pix_fmt', 'yuv420p', '-movflags', '+faststart', '-an', dest], { stdio: ['pipe', 'inherit', 'inherit'] });
const done = new Promise((res, rej) => { enc.on('close', (c) => (c ? rej(new Error('ffmpeg exit ' + c)) : res())); });
for (let i = 0; i < FRAMES; i++) {
  const buf = await sharp(Buffer.from(frame(i / FPS))).removeAlpha().raw().toBuffer();
  if (!enc.stdin.write(buf)) await new Promise((r) => enc.stdin.once('drain', r));
  if (i % 120 === 0) console.log(`frame ${i}/${FRAMES}`);
}
enc.stdin.end();
await done;
await sharp(Buffer.from(frame(13.5))).webp({ quality: 82 }).toFile(new URL('intro-poster.webp', out).pathname);
console.log('intro.mp4 + intro-poster.webp written');
