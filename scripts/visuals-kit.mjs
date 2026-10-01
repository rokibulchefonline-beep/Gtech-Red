// Reusable illustration templates for service pages (square corners, brand red).
// Each function returns SVG body markup for visuals-lib `frame()`.
import { C, icon, txt, rect, card, chart } from './visuals-lib.mjs';

const up = (x, y, s = 30) => icon('lucide:trending-up', x, y, s, C.green, 2.6);
const box = (x, y, w, h) => card(x, y, w, h, 0);
const chip = (x, y, w, h, ic) => `<rect x="${x}" y="${y}" width="${w}" height="${h}" fill="${C.soft}"/>${icon(ic, x + (w - 28) / 2, y + (h - 28) / 2, 28, C.red, 2.2)}`;
const stars = (x, y, n = 5, s = 22) => Array.from({ length: n }, (_, i) => icon('lucide:star', x + i * (s + 4), y, s, '#f5b301', 2.4).replace('fill="currentColor"', 'fill="#f5b301"')).join('');
const esc = (s) => s.replace(/&/g, '&amp;').replace(/</g, '&lt;');

export function kpiRow(y, items) {
  const w = (840 - 2 * 24) / 3;
  return items.map((k, i) => {
    const x = 60 + i * (w + 24);
    return `${box(x, y, w, 170)}${chip(x + 22, y + 22, 52, 52, k.icon)}${txt(x + 22, y + 122, esc(k.value), 40, 700, C.ink)}${txt(x + 22, y + 152, esc(k.label), 17, 700, C.grey)}`;
  }).join('');
}

export function dashboard({ title, value, pts, bars = false, kpis }) {
  const chartBody = bars
    ? pts.map((v, i) => { const h = 200 * v; return rect(110 + i * 66, 410 - h, 40, h, 0, i >= pts.length - 2 ? C.red : '#ffc9cf'); }).join('')
    : chart('k' + title.length, 96, 210, 770, 200, pts, 1);
  return `${box(60, 60, 840, 390)}${txt(92, 112, esc(title), 22, 700, C.grey)}${txt(92, 176, esc(value), 60, 700, C.ink)}${up(100 + value.length * 37, 136, 40)}
    ${chartBody}${kpiRow(484, kpis)}`;
}

export function searchAd({ query, headline, url, desc, sitelinks, kpis }) {
  return `${box(60, 60, 840, 70)}${icon('lucide:search', 84, 80, 30, C.grey, 2.2)}${txt(130, 105, esc(query), 22, 500, C.ink)}
    <rect x="60" y="150" width="840" height="290" fill="#fff" stroke="${C.red}" stroke-width="3"/>
    <rect x="86" y="172" width="104" height="30" fill="none" stroke="${C.ink}" stroke-width="2"/>${txt(138, 194, 'Sponsored', 15, 700, C.ink, 'middle')}${txt(206, 194, esc(url), 16, 500, '#1a7f37')}
    ${txt(86, 250, esc(headline), 28, 700, '#1a0dab')}${txt(86, 288, esc(desc), 17, 500, '#4d5156')}
    ${sitelinks.map((s, i) => `<rect x="${86 + i * 200}" y="${320}" width="184" height="94" fill="#f7f8fa"/>${txt(100 + i * 200, 356, esc(s), 17, 700, '#1a0dab')}${rect(100 + i * 200, 374, 140, 9, 0, '#c9ccd3')}${rect(100 + i * 200, 392, 100, 9, 0, '#c9ccd3')}`).join('')}
    ${kpiRow(484, kpis)}`;
}

export function annotated({ labels }) {
  const ys = [110, 200, 300, 400, 495, 590];
  const pts = [[476, 149], [396, 182], [566, 300], [336, 413], [566, 470], [512, 598]];
  const tag = (i, label) => { const [x, y] = pts[i], ty = ys[i]; return `<line x1="${x}" y1="${y}" x2="670" y2="${ty}" stroke="${C.red}" stroke-width="2.5" stroke-dasharray="6 6"/><circle cx="${x}" cy="${y}" r="7" fill="${C.red}"/><rect x="670" y="${ty - 22}" width="${label.length * 11.5 + 30}" height="44" fill="${C.red}"/>${txt(685, ty + 7, esc(label), 19, 700, '#fff')}`; };
  return `${box(60, 60, 560, 600)}<rect x="60" y="60" width="560" height="44" fill="#f1f2f5"/>
    ${[0, 1, 2].map((i) => `<circle cx="${86 + i * 20}" cy="82" r="6" fill="${['#ff5f57', '#febc2e', '#28c840'][i]}"/>`).join('')}${rect(160, 72, 300, 20, 0, '#fff')}
    ${rect(96, 136, 380, 26, 0, C.ink)}${rect(96, 176, 300, 12, 0, '#c9ccd3')}${rect(96, 226, 470, 150, 0, '#fde7ea')}${icon('lucide:image', 300, 270, 64, C.red, 1.6)}
    ${rect(96, 404, 240, 18, 0, C.ink)}${[0, 1, 2].map((i) => rect(96, 438 + i * 22, 470 - i * 60, 10, 0, C.line)).join('')}
    ${rect(96, 520, 200, 18, 0, C.ink)}${[0, 1].map((i) => rect(96, 554 + i * 22, 440 - i * 90, 10, 0, C.line)).join('')}${rect(392, 592, 120, 12, 0, C.red)}
    ${labels.map((l, i) => tag(i, l)).join('')}`;
}

export function network({ center, value, sub, nodes }) {
  const pos = [[150, 140], [480, 90], [810, 140], [130, 520], [480, 600], [830, 520]];
  return `${pos.map(([x, y]) => `<line x1="${x}" y1="${y}" x2="480" y2="350" stroke="${C.red}" stroke-width="3" stroke-dasharray="8 8" opacity=".7"/>`).join('')}
    ${nodes.map((n, i) => { const [x, y] = pos[i]; return `${box(x - 100, y - 46, 200, 92)}${chip(x - 84, y - 26, 52, 52, n.icon)}${txt(x - 20, y + 7, esc(n.label), 17, 700, C.ink)}`; }).join('')}
    ${box(320, 266, 320, 168)}<rect x="320" y="266" width="320" height="8" fill="${C.red}"/>
    ${txt(480, 318, esc(center), 22, 700, C.ink, 'middle')}${txt(480, 376, esc(value), 40, 700, C.red, 'middle')}${txt(480, 410, esc(sub), 17, 700, C.grey, 'middle')}`;
}

export function reviews({ score, count, items }) {
  const dist = [0.86, 0.09, 0.03, 0.01, 0.01];
  return `${box(60, 60, 330, 600)}${txt(225, 160, score, 96, 700, C.ink, 'middle')}<g transform="translate(-10 0)">${stars(108, 190, 5, 30)}</g>${txt(225, 264, esc(count), 19, 700, C.grey, 'middle')}
    ${dist.map((d, i) => `${txt(92, 334 + i * 56, String(5 - i), 18, 700, C.ink)}${rect(118, 320 + i * 56, 230, 18, 0, C.line)}${rect(118, 320 + i * 56, 230 * d, 18, 0, i ? '#ffc9cf' : C.red)}`).join('')}
    ${items.map((r, i) => `${box(420, 60 + i * 206, 480, 186)}<circle cx="466" cy="${106 + i * 206}" r="24" fill="${C.red}"/>${txt(466, 114 + i * 206, r.name[0], 22, 700, '#fff', 'middle')}
      ${txt(504, 102 + i * 206, esc(r.name), 19, 700, C.ink)}${stars(504, 112 + i * 206, 5, 18)}${txt(444, 178 + i * 206, esc(r.text), 17, 500, '#4d5156')}${rect(444, 196 + i * 206, 320, 9, 0, C.line)}
      ${r.reply ? `${rect(444, 214 + i * 206, 160, 18, 0, C.soft)}${txt(452, 228 + i * 206, 'Owner replied', 13, 700, C.red)}` : ''}`).join('')}`;
}

export function chat({ query, source, side = 'AI Overview' }) {
  return `${box(50, 60, 430, 600)}${rect(78, 90, 374, 44, 0, '#f1f2f5')}${icon('lucide:search', 92, 100, 24, C.grey, 2)}${txt(126, 119, esc(query), 18, 500, C.ink)}
    <rect x="78" y="156" width="374" height="270" fill="#fff5f6" stroke="${C.red}" stroke-width="2"/>${icon('lucide:sparkles', 96, 172, 26, C.red, 2)}${txt(130, 193, esc(side), 19, 700, C.ink)}
    ${[0, 1, 2, 3, 4].map((i) => rect(96, 216 + i * 24, 330 - (i % 2) * 70, 11, 0, '#e3c9cd')).join('')}
    <rect x="96" y="352" width="230" height="40" fill="#fff" stroke="${C.red}" stroke-width="2"/>${icon('lucide:link', 106, 360, 22, C.red, 2)}${txt(136, 378, esc(source), 15, 700, C.red)}
    ${txt(96, 466, 'People also ask', 18, 700, C.ink)}${[0, 1, 2].map((i) => `${rect(96, 484 + i * 52, 336, 40, 0, '#f6f7f9')}${rect(110, 499 + i * 52, 220 - i * 30, 10, 0, '#c9ccd3')}`).join('')}
    ${box(520, 100, 390, 520)}<rect x="520" y="100" width="390" height="56" fill="${C.ink}"/>${icon('lucide:message-circle', 540, 114, 28, '#fff', 2)}${txt(580, 136, 'AI assistant', 19, 700, '#fff')}
    <rect x="640" y="186" width="246" height="56" fill="${C.line}"/>${rect(656, 202, 200, 10, 0, '#9aa0ae')}${rect(656, 222, 140, 10, 0, '#9aa0ae')}
    <rect x="544" y="270" width="320" height="200" fill="#fff5f6"/>${[0, 1, 2, 3, 4, 5].map((i) => rect(562, 292 + i * 24, 280 - (i % 3) * 50, 10, 0, '#e3c9cd')).join('')}
    <rect x="544" y="490" width="${source.length * 9 + 80}" height="44" fill="${C.red}"/>${txt(560, 518, 'Source: ' + esc(source), 16, 700, '#fff')}
    ${rect(544, 560, 342, 40, 0, '#f6f7f9')}${txt(560, 586, 'Ask a follow-up…', 16, 500, C.grey)}`;
}

export function funnel({ title, stages, kpis }) {
  const max = 760;
  return `${box(60, 60, 840, 400)}${txt(92, 110, esc(title), 22, 700, C.grey)}
    ${stages.map((s, i) => { const w = max * (1 - i * 0.2), x = 100 + (max - w) / 2; return `<rect x="${x}" y="${140 + i * 76}" width="${w}" height="62" fill="${i === stages.length - 1 ? C.red : ['#ffd6db', '#ffb3bb', '#ff7b89', C.red][i]}"/>${txt(480, 180 + i * 76, `${esc(s.label)}  ·  ${esc(s.value)}`, 22, 700, i >= 2 ? '#fff' : C.ink, 'middle')}`; }).join('')}
    ${kpiRow(490, kpis)}`;
}

export function platforms({ title, tiles }) {
  return `${txt(480, 92, esc(title), 30, 700, C.ink, 'middle')}
    ${tiles.map((t, i) => { const x = 60 + (i % 3) * 287, y = 130 + Math.floor(i / 3) * 270; return `${box(x, y, 266, 250)}${chip(x + 24, y + 24, 64, 64, t.icon)}${txt(x + 24, y + 130, esc(t.name), 24, 700, C.ink)}${txt(x + 24, y + 186, esc(t.metric), 36, 700, C.red)}${txt(x + 24, y + 218, esc(t.label), 16, 700, C.grey)}`; }).join('')}`;
}

export function contentGrid({ title, items }) {
  return `${txt(480, 92, esc(title), 30, 700, C.ink, 'middle')}
    ${items.map((t, i) => { const x = 60 + (i % 3) * 287, y = 130 + Math.floor(i / 3) * 270; return `${box(x, y, 266, 250)}<rect x="${x}" y="${y}" width="266" height="110" fill="${i % 2 ? '#fde7ea' : '#ffd0d5'}"/>${icon(t.icon, x + 103, y + 25, 60, C.red, 1.8)}
      <rect x="${x + 20}" y="${y + 128}" width="${t.type.length * 10 + 24}" height="28" fill="${C.ink}"/>${txt(x + 32, y + 147, esc(t.type), 14, 700, '#fff')}${txt(x + 20, y + 190, esc(t.title), 19, 700, C.ink)}${rect(x + 20, y + 210, 200, 9, 0, C.line)}${rect(x + 20, y + 226, 150, 9, 0, C.line)}`; }).join('')}`;
}

export function checklist({ title, score, scoreLabel, items }) {
  const r = 70, circ = 2 * Math.PI * r, p = parseInt(score, 10) / 100;
  return `${box(60, 60, 330, 600)}${txt(225, 120, esc(scoreLabel), 20, 700, C.grey, 'middle')}
    <g transform="rotate(-90 225 270)"><circle cx="225" cy="270" r="${r}" fill="none" stroke="${C.line}" stroke-width="20"/><circle cx="225" cy="270" r="${r}" fill="none" stroke="${C.red}" stroke-width="20" stroke-dasharray="${(circ * p).toFixed(1)} ${circ.toFixed(1)}"/></g>
    ${txt(225, 284, esc(score), 44, 700, C.ink, 'middle')}${rect(100, 420, 250, 14, 0, C.line)}${rect(100, 448, 190, 14, 0, C.line)}${rect(100, 476, 220, 14, 0, C.line)}
    ${box(420, 60, 480, 600)}${txt(450, 112, esc(title), 22, 700, C.grey)}
    ${items.map((it, i) => `${icon(it.ok ? 'lucide:circle-check' : 'lucide:circle-alert', 450, 146 + i * 82, 32, it.ok ? C.green : C.red, 2.4)}${txt(498, 170 + i * 82, esc(it.label), 20, 700, C.ink)}${rect(498, 184 + i * 82, 300, 9, 0, C.line)}`).join('')}`;
}

export function budget({ title, slices, roas }) {
  const r = 150, circ = 2 * Math.PI * r;
  let off = 0;
  const segs = slices.map((s) => { const len = circ * s.pct / 100; const seg = `<circle cx="300" cy="360" r="${r}" fill="none" stroke="${s.color}" stroke-width="64" stroke-dasharray="${len.toFixed(1)} ${(circ - len).toFixed(1)}" stroke-dashoffset="${(-off).toFixed(1)}"/>`; off += len; return seg; }).join('');
  return `${box(60, 60, 500, 600)}${txt(92, 110, esc(title), 22, 700, C.grey)}<g transform="rotate(-90 300 360)">${segs}</g>${txt(300, 372, 'Budget', 26, 700, C.ink, 'middle')}
    ${box(590, 60, 310, 420)}${slices.map((s, i) => `<rect x="618" y="${100 + i * 76}" width="26" height="26" fill="${s.color}"/>${txt(658, 120 + i * 76, esc(s.label), 19, 700, C.ink)}${txt(870, 120 + i * 76, s.pct + '%', 19, 700, C.grey, 'end')}`).join('')}
    ${box(590, 500, 310, 160)}${txt(618, 540, 'Blended ROAS', 19, 700, C.grey)}${txt(618, 610, esc(roas), 56, 700, C.red)}${up(800, 570, 40)}`;
}

export function videoAd({ title, platform, kpis }) {
  return `${box(330, 40, 300, 620)}<rect x="346" y="56" width="268" height="588" fill="#111"/>
    <rect x="346" y="56" width="268" height="420" fill="url(#rg)"/><circle cx="480" cy="266" r="48" fill="#fff" opacity=".9"/><polygon points="466,240 466,292 506,266" fill="${C.red}"/>
    ${txt(366, 512, esc(title), 18, 700, '#fff')}${rect(366, 528, 180, 10, 0, '#555')}<rect x="366" y="566" width="228" height="48" fill="${C.red}"/>${txt(480, 597, 'Shop now', 18, 700, '#fff', 'middle')}
    ${box(40, 120, 260, 120)}${chip(60, 140, 52, 52, platform)}${txt(128, 168, 'Video views', 17, 700, C.grey)}${txt(128, 210, esc(kpis[0]), 30, 700, C.ink)}
    ${box(40, 270, 260, 120)}${chip(60, 290, 52, 52, 'lucide:mouse-pointer-click')}${txt(128, 318, 'Click-through', 17, 700, C.grey)}${txt(128, 360, esc(kpis[1]), 30, 700, C.ink)}
    ${box(660, 160, 260, 120)}${chip(680, 180, 52, 52, 'lucide:shopping-cart')}${txt(748, 208, 'Conversions', 17, 700, C.grey)}${txt(748, 250, esc(kpis[2]), 30, 700, C.ink)}
    ${box(660, 310, 260, 120)}${chip(680, 330, 52, 52, 'lucide:coins')}${txt(748, 358, 'Cost per sale', 17, 700, C.grey)}${txt(748, 400, esc(kpis[3]), 30, 700, C.ink)}`;
}

export function gauges({ title, items, checks }) {
  const g = (cx, it) => {
    const p = Math.min(1, it.p), a0 = Math.PI * 0.8, a1 = a0 + Math.PI * 1.4 * p, r = 70;
    const pt = (a) => `${(cx + r * Math.cos(a)).toFixed(1)} ${(250 + r * Math.sin(a)).toFixed(1)}`;
    return `${box(cx - 130, 110, 260, 300)}${txt(cx, 156, esc(it.label), 22, 700, C.grey, 'middle')}
      <path d="M${pt(a0)} A${r} ${r} 0 1 1 ${pt(Math.PI * 2.2)}" fill="none" stroke="${C.line}" stroke-width="16"/>
      <path d="M${pt(a0)} A${r} ${r} 0 ${p > 0.72 ? 1 : 0} 1 ${pt(a1)}" fill="none" stroke="${C.green}" stroke-width="16"/>
      ${txt(cx, 262, esc(it.value), 34, 700, C.ink, 'middle')}<rect x="${cx - 54}" y="348" width="108" height="36" fill="#dcfce7"/>${txt(cx, 373, 'Good', 18, 700, C.green, 'middle')}`;
  };
  return `${txt(480, 76, esc(title), 30, 700, C.ink, 'middle')}${items.map((it, i) => g(190 + i * 290, it)).join('')}
    ${box(60, 446, 840, 220)}${checks.map((l, i) => `${icon('lucide:circle-check', 92 + (i % 2) * 410, 476 + Math.floor(i / 2) * 58, 28, C.green, 2.4)}${txt(132 + (i % 2) * 410, 499 + Math.floor(i / 2) * 58, esc(l), 20, 700, C.ink)}`).join('')}`;
}

export function code({ file, lang, lines, kpis }) {
  const colors = { k: '#ff7b89', f: '#7dd3fc', s: '#86efac', c: '#6b7280', t: '#e5e7eb' };
  return `<rect x="60" y="60" width="560" height="600" fill="#15161c" filter="url(#sh)"/><rect x="60" y="60" width="560" height="44" fill="#1f2029"/>
    ${[0, 1, 2].map((i) => `<circle cx="${86 + i * 20}" cy="82" r="6" fill="${['#ff5f57', '#febc2e', '#28c840'][i]}"/>`).join('')}
    <rect x="160" y="68" width="${file.length * 10 + 30}" height="36" fill="#15161c"/>${txt(176, 92, esc(file), 16, 500, '#e5e7eb')}
    ${lines.map((ln, i) => `${txt(84, 146 + i * 34, String(i + 1), 15, 500, '#4b5060')}${ln.map(([tone, w], j) => { const x = 120 + ln.slice(0, j).reduce((a, [, ww]) => a + ww + 12, 0) + (ln.indent || 0); return `<rect x="${x}" y="${134 + i * 34}" width="${w}" height="12" fill="${colors[tone]}" opacity=".9"/>`; }).join('')}`).join('')}
    ${box(650, 60, 250, 120)}<rect x="672" y="82" width="76" height="76" fill="${C.soft}"/>${icon(lang, 686, 96, 48, C.red, 1.8)}${txt(766, 128, 'Clean code', 20, 700, C.ink)}
    ${kpis.map((k, i) => `${box(650, 210 + i * 150, 250, 130)}${chip(672, 232 + i * 150, 52, 52, k.icon)}${txt(740, 262 + i * 150, esc(k.value), 30, 700, C.ink)}${txt(672, 318 + i * 150, esc(k.label), 16, 700, C.grey)}`).join('')}`;
}

export function stack({ title, layers }) {
  return `${txt(480, 76, esc(title), 30, 700, C.ink, 'middle')}
    ${layers.map((l, i) => { const y = 112 + i * 112, w = 840 - i * 0; return `${box(60, y, w, 96)}<rect x="60" y="${y}" width="10" height="96" fill="${i === 0 ? C.red : ['#ff7b89', '#ffb3bb', '#ffd0d5', '#fde7ea'][i - 1] ?? C.line}"/>
      ${txt(96, y + 42, esc(l.name), 22, 700, C.ink)}${txt(96, y + 72, esc(l.desc), 16, 500, C.grey)}
      ${l.tags.map((t, j) => `<rect x="${520 + j * 126}" y="${y + 30}" width="114" height="36" fill="${C.soft}"/>${txt(577 + j * 126, y + 54, esc(t), 15, 700, C.red, 'middle')}`).join('')}`; }).join('')}`;
}
