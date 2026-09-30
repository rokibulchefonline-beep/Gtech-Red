// Helpers for the animated service visuals (pure SVG strings, one frame per call).
import { readFileSync } from 'node:fs';
import { createRequire } from 'node:module';

const require = createRequire(import.meta.url);
const packs = {
  lucide: JSON.parse(readFileSync(require.resolve('@iconify-json/lucide/icons.json'), 'utf8')),
  'simple-icons': JSON.parse(readFileSync(require.resolve('@iconify-json/simple-icons/icons.json'), 'utf8')),
};

export const W = 960, H = 720;
export const C = { red: '#e8202f', dark: '#b0122c', ink: '#161616', grey: '#8b8f98', line: '#eceef2', soft: '#fff1f2', green: '#16a34a' };

export const clamp = (x, a = 0, b = 1) => Math.min(b, Math.max(a, x));
export const seg = (u, a, b) => clamp((u - a) / (b - a));
export const eo = (x) => 1 - Math.pow(1 - x, 3);
export const S = (u, a, b) => eo(seg(u, a, b));
export const back = (x) => { const c1 = 1.70158, c3 = c1 + 1; return 1 + c3 * Math.pow(x - 1, 3) + c1 * Math.pow(x - 1, 2); };
export const B = (u, a, b) => back(seg(u, a, b));

export function icon(id, x, y, size, color = C.red, sw) {
  const [prefix, name] = id.split(':');
  const p = packs[prefix];
  const d = p.icons[name];
  if (!d) throw new Error('missing icon ' + id);
  const w = d.width ?? p.width ?? 24, h = d.height ?? p.height ?? 24;
  const body = sw ? d.body.replace(/stroke-width="[^"]*"/, `stroke-width="${sw}"`) : d.body;
  return `<svg x="${x}" y="${y}" width="${size}" height="${size}" viewBox="0 0 ${w} ${h}" style="color:${color}" fill="currentColor">${body}</svg>`;
}

export const txt = (x, y, s, size = 20, weight = 700, fill = C.ink, anchor = 'start') =>
  `<text x="${x}" y="${y}" font-family="Liberation Sans, Arial, sans-serif" font-size="${size}" font-weight="${weight}" fill="${fill}" text-anchor="${anchor}">${s}</text>`;

export const rect = (x, y, w, h, r = 8, fill = C.line, extra = '') =>
  `<rect x="${x}" y="${y}" width="${Math.max(0, w)}" height="${h}" rx="${r}" fill="${fill}" ${extra}/>`;

export const card = (x, y, w, h, r = 22, extra = '') =>
  `<rect x="${x}" y="${y}" width="${w}" height="${h}" rx="${r}" fill="#fff" filter="url(#sh)" ${extra}/>`;

/** Fade + rise-in wrapper. */
export const pop = (u, a, inner, dy = 26, len = 0.1) => {
  const p = S(u, a, a + len);
  return `<g opacity="${p.toFixed(3)}" transform="translate(0 ${((1 - p) * dy).toFixed(2)})">${inner}</g>`;
};

/** Scale-in (with overshoot) around a centre point. */
export const zoom = (u, a, cx, cy, inner, len = 0.1) => {
  const s = Math.max(0.001, B(u, a, a + len));
  const o = clamp(seg(u, a, a + len) * 2);
  return `<g opacity="${o.toFixed(3)}" transform="translate(${cx} ${cy}) scale(${s.toFixed(3)}) translate(${-cx} ${-cy})">${inner}</g>`;
};

const at = (pts, f) => { // interpolate y at fraction f (0..1) along evenly spaced pts
  const t = clamp(f) * (pts.length - 1), i = Math.min(pts.length - 2, Math.floor(t));
  return pts[i] + (pts[i + 1] - pts[i]) * (t - i);
};

/** Line chart that draws itself. pts are 0..1 (1 = top). */
export function chart(id, x, y, w, h, pts, prog, color = C.red, area = true) {
  const n = pts.length;
  const px = (i) => x + (w * i) / (n - 1);
  const py = (v) => y + h - v * h;
  const line = pts.map((v, i) => `${i ? 'L' : 'M'}${px(i).toFixed(1)} ${py(v).toFixed(1)}`).join(' ');
  const fill = `${line} L${px(n - 1)} ${y + h} L${px(0)} ${y + h} Z`;
  const cx = x + w * prog, cy = py(at(pts, prog));
  return `<defs><clipPath id="${id}"><rect x="${x - 6}" y="${y - 12}" width="${w * prog + 6}" height="${h + 24}"/></clipPath>
    <linearGradient id="${id}g" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="${color}" stop-opacity=".28"/><stop offset="1" stop-color="${color}" stop-opacity="0"/></linearGradient></defs>
    ${[0, 1, 2, 3].map((k) => `<line x1="${x}" x2="${x + w}" y1="${y + (h * k) / 3}" y2="${y + (h * k) / 3}" stroke="${C.line}" stroke-width="2"/>`).join('')}
    <g clip-path="url(#${id})">${area ? `<path d="${fill}" fill="url(#${id}g)"/>` : ''}<path d="${line}" fill="none" stroke="${color}" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/></g>
    ${prog > 0.02 ? `<circle cx="${cx.toFixed(1)}" cy="${cy.toFixed(1)}" r="9" fill="#fff" stroke="${color}" stroke-width="5"/>` : ''}`;
}

/** Wrap a scene: background, defs, loop fade. */
export function frame(u, body) {
  const fade = clamp(seg(u, 0.93, 1)) ;
  return `<svg xmlns="http://www.w3.org/2000/svg" width="800" height="600" viewBox="0 0 ${W} ${H}">
  <defs>
    <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#fff5f6"/><stop offset="1" stop-color="#ffdfe3"/></linearGradient>
    <linearGradient id="rg" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#ff4b5c"/><stop offset="1" stop-color="#b0122c"/></linearGradient>
    <filter id="sh" x="-20%" y="-20%" width="140%" height="150%"><feDropShadow dx="0" dy="10" stdDeviation="14" flood-color="#8a0f24" flood-opacity=".16"/></filter>
  </defs>
  <rect width="${W}" height="${H}" fill="url(#bg)"/>
  <circle cx="900" cy="40" r="220" fill="#ff8a95" opacity=".18"/><circle cx="30" cy="700" r="200" fill="#e8202f" opacity=".08"/>
  <g opacity="${(1 - fade).toFixed(3)}">${body}</g></svg>`;
}
