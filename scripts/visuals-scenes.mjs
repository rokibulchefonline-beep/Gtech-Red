import { C, S, B, seg, clamp, icon, txt, rect, card, pop, zoom, chart, frame } from './visuals-lib.mjs';

const num = (v, d = 0) => v.toFixed(d);
const upArrow = (x, y, s = 30, col = C.green) => icon('lucide:trending-up', x, y, s, col, 2.6);

/* 1. SEO ---------------------------------------------------------------- */
export function seo(u) {
  const q = 'digital agency';
  const typed = q.slice(0, Math.floor(seg(u, 0.05, 0.28) * (q.length + 0.99)));
  const caret = u < 0.3 && Math.floor(u * 40) % 2 === 0 ? `<rect x="${162 + typed.length * 12.6}" y="98" width="3" height="30" fill="${C.ink}"/>` : '';
  const g = seg(u, 0.32, 0.78);
  const trend = [0.12, 0.2, 0.18, 0.32, 0.4, 0.38, 0.58, 0.72, 0.92];
  const res = (y, hi, a) => pop(u, a, `
    ${card(60, y, 500, 108, 20, hi ? `stroke="${C.red}" stroke-width="3"` : '')}
    <circle cx="106" cy="${y + 40}" r="20" fill="${hi ? C.red : C.line}"/>
    ${rect(140, y + 24, hi ? 250 : 210, 16, 8, hi ? C.ink : '#c9ccd3')}
    ${rect(140, y + 52, 130, 11, 6, hi ? '#86efac' : C.line)}
    ${rect(140, y + 74, 340, 10, 5, C.line)}${rect(140, y + 90, 260, 10, 5, C.line)}`);
  return frame(u, `
    ${pop(u, 0, `${card(60, 60, 840, 96, 48)}${icon('lucide:search', 92, 90, 36, C.red, 2.6)}
      ${txt(162, 118, typed, 26, 500, C.ink)}${caret}
      <rect x="742" y="80" width="140" height="56" rx="28" fill="url(#rg)"/>${txt(812, 116, 'Search', 22, 700, '#fff', 'middle')}`, 14)}
    ${res(184, true, 0.3)}${res(310, false, 0.36)}${res(436, false, 0.42)}
    ${zoom(u, 0.5, 528, 196, `<rect x="486" y="176" width="72" height="40" rx="20" fill="url(#rg)"/>${txt(522, 205, '#1', 22, 700, '#fff', 'middle')}`, 0.12)}

    ${pop(u, 0.2, `${card(590, 184, 310, 262)}${txt(614, 226, 'Organic traffic', 18, 700, C.grey)}
      ${txt(614, 286, '+' + num(240 * S(u, 0.32, 0.78)) + '%', 50, 700, C.ink)}
      ${upArrow(840, 250, 34)}
      ${chart('c1', 616, 316, 262, 100, trend, g)}`)}
    ${pop(u, 0.5, `${card(60, 574, 500, 110)}${txt(88, 620, 'Qualified leads', 18, 700, C.grey)}
      ${[0.3, 0.45, 0.6, 0.8, 1].map((v, i) => { const h = 62 * v * S(u, 0.55 + i * 0.05, 0.75 + i * 0.05); return rect(340 + i * 44, 664 - h, 28, h, 8, i === 4 ? C.red : '#ffb3bb'); }).join('')}
      ${txt(88, 664, num(1240 * S(u, 0.55, 0.85)), 34, 700, C.ink)}`)}
    ${pop(u, 0.58, `${card(590, 470, 310, 214)}${txt(614, 512, 'Keywords on page 1', 18, 700, C.grey)}
      ${txt(614, 590, num(128 * S(u, 0.6, 0.9)), 64, 700, C.red)}
      ${[0, 1, 2, 3, 4, 5, 6].map((i) => rect(614 + i * 38, 660 - 40 * S(u, 0.62 + i * 0.03, 0.85) * (0.4 + i * 0.1), 26, 40 * S(u, 0.62 + i * 0.03, 0.85) * (0.4 + i * 0.1), 6, i > 4 ? C.red : '#ffc9cf')).join('')}`)}
  `);
}

/* 2. Paid media --------------------------------------------------------- */
export function paid(u) {
  const bars = [0.35, 0.42, 0.5, 0.48, 0.66, 0.78, 0.92];
  const line = [0.2, 0.3, 0.34, 0.44, 0.58, 0.7, 0.9];
  const pulse = 1 + 0.05 * Math.sin(u * Math.PI * 6);
  return frame(u, `
    ${pop(u, 0, `${card(60, 60, 560, 380)}${txt(88, 106, 'Campaign performance', 20, 700, C.ink)}
      ${bars.map((v, i) => { const h = 250 * v * S(u, 0.08 + i * 0.05, 0.36 + i * 0.05); return rect(94 + i * 70, 400 - h, 44, h, 10, i > 4 ? 'url(#rg)' : '#ffc9cf'); }).join('')}
      ${chart('c2', 100, 170, 470, 230, line, seg(u, 0.3, 0.75), C.dark, false)}`, 14)}
    ${pop(u, 0.16, `${card(650, 60, 250, 178)}${txt(674, 104, 'ROAS', 18, 700, C.grey)}
      ${txt(674, 176, num(4.8 * S(u, 0.2, 0.6), 1) + 'x', 66, 700, C.ink)}${upArrow(842, 108, 32)}${rect(674, 200, 200, 10, 5, C.line)}${rect(674, 200, 200 * 0.82 * S(u, 0.2, 0.6), 10, 5, C.red)}`)}
    ${pop(u, 0.26, `${card(650, 262, 250, 178)}${txt(674, 306, 'Cost per lead', 18, 700, C.grey)}
      ${txt(674, 378, '-' + num(38 * S(u, 0.3, 0.7)) + '%', 66, 700, C.ink)}${icon('lucide:trending-down', 842, 310, 32, C.green, 2.6)}${rect(674, 402, 200, 10, 5, C.line)}${rect(674, 402, 200 * 0.62 * S(u, 0.3, 0.7), 10, 5, C.green)}`)}
    ${pop(u, 0.38, `${card(60, 470, 560, 214)}
      <circle cx="112" cy="524" r="26" fill="${C.soft}"/>${icon('simple-icons:googleads', 98, 510, 28, C.red)}
      <rect x="146" y="510" width="44" height="26" rx="6" fill="none" stroke="${C.ink}" stroke-width="2.5"/>${txt(168, 530, 'Ad', 16, 700, C.ink, 'middle')}
      ${rect(206, 514, 220, 14, 7, '#c9ccd3')}
      ${rect(88, 566, 380, 20, 10, C.ink)}${rect(88, 600, 470, 12, 6, C.line)}${rect(88, 622, 400, 12, 6, C.line)}
      <g transform="translate(${470} 664) scale(${pulse}) translate(${-470} -664)"><rect x="380" y="640" width="180" height="40" rx="20" fill="url(#rg)"/>${txt(470, 668, 'Get a quote', 18, 700, '#fff', 'middle')}</g>`)}
    ${pop(u, 0.48, `${card(650, 470, 250, 214)}${txt(674, 512, 'Funnel', 18, 700, C.grey)}
      ${[['Clicks', 200, '#ffc9cf'], ['Leads', 140, '#ff7b89'], ['Sales', 84, C.red]].map(([l, w, c], i) => `${rect(674, 534 + i * 46, w * S(u, 0.52 + i * 0.07, 0.8 + i * 0.05), 34, 10, c)}${txt(684, 558 + i * 46, l, 15, 700, i ? '#fff' : C.ink)}`).join('')}`)}
  `);
}

/* 3. Social ------------------------------------------------------------- */
export function social(u) {
  const bob = (p) => Math.sin(u * Math.PI * 2 + p) * 6;
  const heart = 1 + 0.35 * Math.max(0, Math.sin(seg(u, 0.4, 0.55) * Math.PI));
  const ring = 2 * Math.PI * 62, sweep = S(u, 0.3, 0.72) * 0.72;
  return frame(u, `
    ${pop(u, 0, `<rect x="330" y="40" width="300" height="640" rx="44" fill="#fff" filter="url(#sh)"/><rect x="346" y="56" width="268" height="608" rx="34" fill="#fafafa"/>
      <rect x="440" y="66" width="80" height="20" rx="10" fill="${C.ink}"/>
      <circle cx="382" cy="130" r="22" fill="url(#rg)"/>${rect(416, 116, 110, 12, 6, C.ink)}${rect(416, 138, 70, 9, 5, C.line)}
      <rect x="362" y="170" width="236" height="250" rx="20" fill="url(#rg)"/>
      <circle cx="480" cy="290" r="54" fill="#fff" opacity=".22"/>${icon('lucide:megaphone', 446, 256, 68, '#fff', 2)}
      ${zoom(u, 0.4, 388, 452, icon('lucide:heart', 372, 436, 34, C.red, 2.4).replace('fill="currentColor"', `fill="${C.red}"`), 0.12)}
      ${icon('lucide:message-circle', 420, 438, 32, C.ink, 2.2)}${icon('lucide:share-2', 464, 438, 32, C.ink, 2.2)}
      ${txt(372, 500, num(12400 * S(u, 0.4, 0.8)).replace(/\B(?=(\d{3})+(?!\d))/g, ',') + ' likes', 17, 700, C.ink)}
      ${rect(372, 520, 200, 11, 6, C.line)}${rect(372, 540, 150, 11, 6, C.line)}
      ${rect(372, 580, 100, 40, 20, 'url(#rg)')}${txt(422, 606, 'Follow', 16, 700, '#fff', 'middle')}`, 16, 0.12)}
    ${pop(u, 0.14, `${card(40, 110, 270, 220)}${txt(64, 152, 'Followers', 18, 700, C.grey)}${txt(64, 210, '+' + num(128 * S(u, 0.2, 0.7)) + '%', 50, 700, C.ink)}
      ${chart('c3', 66, 236, 220, 70, [0.1, 0.2, 0.18, 0.4, 0.5, 0.75, 0.95], seg(u, 0.22, 0.7))}`)}
    ${pop(u, 0.5, `${card(40, 372, 270, 130, 65)}${['simple-icons:instagram', 'simple-icons:facebook', 'simple-icons:tiktok'].map((n, i) => `<circle cx="${100 + i * 74}" cy="437" r="28" fill="${C.soft}"/>${icon(n, 100 + i * 74 - 14, 423, 28, C.red)}`).join('')}`)}
    ${pop(u, 0.42, `<g transform="translate(0 ${bob(0)})"><rect x="660" y="100" width="240" height="76" rx="38" fill="#fff" filter="url(#sh)"/><circle cx="702" cy="138" r="24" fill="${C.soft}"/>${icon('lucide:heart', 690, 126, 24, C.red, 2.4).replace('fill="currentColor"', `fill="${C.red}"`)}${txt(742, 132, '12.4k', 22, 700)}${txt(742, 154, 'new likes', 14, 500, C.grey)}</g>`, 20)}
    ${pop(u, 0.52, `<g transform="translate(0 ${bob(2)})"><rect x="670" y="200" width="230" height="76" rx="38" fill="#fff" filter="url(#sh)"/><circle cx="712" cy="238" r="24" fill="${C.soft}"/>${icon('lucide:message-circle', 700, 226, 24, C.red, 2.4)}${txt(752, 232, '860', 22, 700)}${txt(752, 254, 'comments', 14, 500, C.grey)}</g>`, 20)}
    ${pop(u, 0.62, `<g transform="translate(0 ${bob(4)})"><rect x="660" y="300" width="240" height="76" rx="38" fill="#fff" filter="url(#sh)"/><circle cx="702" cy="338" r="24" fill="${C.soft}"/>${icon('lucide:users', 690, 326, 24, C.red, 2.4)}${txt(742, 332, '+3.2k', 22, 700)}${txt(742, 354, 'new followers', 14, 500, C.grey)}</g>`, 20)}
    ${pop(u, 0.3, `${card(660, 420, 240, 250)}${txt(684, 462, 'Engagement', 18, 700, C.grey)}
      <g transform="rotate(-90 780 566)"><circle cx="780" cy="566" r="62" fill="none" stroke="${C.line}" stroke-width="18"/>
      <circle cx="780" cy="566" r="62" fill="none" stroke="url(#rg)" stroke-width="18" stroke-linecap="round" stroke-dasharray="${(ring * sweep).toFixed(1)} ${ring.toFixed(1)}"/></g>
      ${txt(780, 576, num(72 * S(u, 0.3, 0.72)) + '%', 34, 700, C.ink, 'middle')}`)}
  `);
}

/* 4. Web design & development ------------------------------------------- */
export function web(u) {
  const arc = (p) => { const a0 = Math.PI * 0.8, a1 = Math.PI * 2.2, a = a0 + (a1 - a0) * p; const x = 780 + 74 * Math.cos(a), y = 210 + 74 * Math.sin(a); return `M${(780 + 74 * Math.cos(a0)).toFixed(1)} ${(210 + 74 * Math.sin(a0)).toFixed(1)} A74 74 0 ${p > 0.5 ? 1 : 0} 1 ${x.toFixed(1)} ${y.toFixed(1)}`; };
  const code = [[40, C.red], [110, '#3b3f4a'], [70, '#7c8190'], [130, C.red], [60, '#3b3f4a']];
  return frame(u, `
    ${pop(u, 0, `${card(60, 60, 570, 440)}<rect x="60" y="60" width="570" height="52" rx="22" fill="#f4f5f7"/><rect x="60" y="90" width="570" height="22" fill="#f4f5f7"/>
      ${[0, 1, 2].map((i) => `<circle cx="${92 + i * 22}" cy="86" r="7" fill="${['#ff5f57', '#febc2e', '#28c840'][i]}"/>`).join('')}${rect(200, 74, 260, 24, 12, '#fff')}
      ${rect(90, 136, 60, 12, 6, C.ink)}${rect(430, 136, 40, 10, 5, C.line)}${rect(486, 136, 40, 10, 5, C.line)}<rect x="544" y="128" width="58" height="26" rx="13" fill="url(#rg)"/>
      ${rect(90, 190, 300 * S(u, 0.1, 0.24), 26, 10, C.ink)}${rect(90, 230, 230 * S(u, 0.16, 0.3), 26, 10, C.ink)}
      ${rect(90, 280, 260 * S(u, 0.22, 0.34), 12, 6, C.line)}${rect(90, 302, 200 * S(u, 0.24, 0.36), 12, 6, C.line)}
      ${zoom(u, 0.3, 160, 356, `<rect x="90" y="332" width="140" height="44" rx="22" fill="url(#rg)"/>${txt(160, 362, 'Get started', 17, 700, '#fff', 'middle')}`, 0.1)}
      ${pop(u, 0.2, `<rect x="400" y="180" width="200" height="200" rx="22" fill="url(#rg)"/><circle cx="500" cy="280" r="46" fill="#fff" opacity=".25"/>${icon('lucide:monitor', 466, 246, 68, '#fff', 2)}`, 20, 0.14)}
      ${[0, 1, 2].map((i) => pop(u, 0.36 + i * 0.05, `<rect x="${90 + i * 174}" y="404" width="158" height="76" rx="16" fill="#f4f5f7"/><circle cx="${118 + i * 174}" cy="430" r="12" fill="${C.red}" opacity="${0.4 + i * 0.3}"/>${rect(104 + i * 174, 452, 100, 9, 5, '#d5d8de')}`, 16, 0.08)).join('')}`, 14)}
    ${pop(u, 0.24, `${card(660, 60, 240, 300)}${txt(780, 104, 'Performance', 18, 700, C.grey, 'middle')}
      <path d="${arc(1)}" fill="none" stroke="${C.line}" stroke-width="16" stroke-linecap="round"/>
      ${S(u, 0.3, 0.7) > 0.01 ? `<path d="${arc(S(u, 0.3, 0.7) * 0.98)}" fill="none" stroke="url(#rg)" stroke-width="16" stroke-linecap="round"/>` : ''}
      ${txt(780, 222, num(98 * S(u, 0.3, 0.7)), 50, 700, C.ink, 'middle')}
      ${icon('lucide:zap', 764, 300, 32, C.red, 2.4)}${txt(780, 350, 'Fast load', 15, 500, C.grey, 'middle')}`)}
    ${pop(u, 0.4, `<rect x="660" y="384" width="240" height="116" rx="22" fill="#1b1d24" filter="url(#sh)"/>
      ${code.map(([w, c], i) => rect(686 + (i % 2) * 20, 408 + i * 17, w * S(u, 0.44 + i * 0.05, 0.6 + i * 0.05), 9, 5, c)).join('')}`)}
    ${pop(u, 0.5, `${card(60, 530, 570, 154)}${txt(88, 574, 'Conversion rate', 18, 700, C.grey)}${txt(88, 640, '+' + num(64 * S(u, 0.54, 0.86)) + '%', 52, 700, C.ink)}${upArrow(258, 604, 34)}
      ${[0.3, 0.42, 0.5, 0.66, 0.8, 1].map((v, i) => { const h = 100 * v * S(u, 0.55 + i * 0.04, 0.8 + i * 0.04); return rect(340 + i * 44, 664 - h, 30, h, 8, i === 5 ? 'url(#rg)' : '#ffc9cf'); }).join('')}`)}
    ${pop(u, 0.58, `${card(660, 530, 240, 154)}${icon('lucide:shield-check', 684, 552, 34, C.red, 2.4)}${txt(730, 578, 'Secure', 20, 700)}
      ${icon('lucide:smartphone', 684, 602, 34, C.red, 2.4)}${txt(730, 628, 'Responsive', 20, 700)}${icon('lucide:search', 684, 648, 26, C.red, 2.4)}${txt(730, 668, 'SEO ready', 18, 700)}`)}
  `);
}

/* 5. Custom software ---------------------------------------------------- */
export function software(u) {
  const dash = -u * 240;
  const link = (x1, y1, x2, y2, a) => `<line x1="${x1}" y1="${y1}" x2="${x2}" y2="${y2}" stroke="#ffc9cf" stroke-width="4"/><line x1="${x1}" y1="${y1}" x2="${x2}" y2="${y2}" stroke="${C.red}" stroke-width="4" stroke-dasharray="8 22" stroke-dashoffset="${dash}" opacity="${S(u, a, a + 0.1)}"/>`;
  const node = (cx, cy, ic, a) => zoom(u, a, cx, cy, `<circle cx="${cx}" cy="${cy}" r="38" fill="#fff" filter="url(#sh)"/>${icon(ic, cx - 18, cy - 18, 36, C.red, 2.4)}`, 0.1);
  const steps = [['lucide:database', 'Data'], ['lucide:settings-2', 'Logic'], ['lucide:circle-check', 'Live']];
  return frame(u, `
    ${pop(u, 0, `${card(60, 60, 600, 420)}<rect x="60" y="60" width="86" height="420" rx="22" fill="#1b1d24"/><rect x="124" y="60" width="22" height="420" fill="#1b1d24"/>
      ${['lucide:layout-dashboard', 'lucide:users', 'lucide:chart-column', 'lucide:settings'].map((n, i) => `${i === 0 ? `<rect x="78" y="${96 + i * 62}" width="50" height="44" rx="14" fill="url(#rg)"/>` : ''}${icon(n, 91, 105 + i * 62, 24, i === 0 ? '#fff' : '#9aa0ae', 2.2)}`).join('')}
      ${txt(176, 106, 'Dashboard', 22, 700)}${rect(480, 88, 150, 26, 13, C.line)}
      ${[['Revenue', 84, '$'], ['Orders', 62, ''], ['Users', 91, '']].map(([l, v, p], i) => pop(u, 0.1 + i * 0.05, `<rect x="${176 + i * 156}" y="132" width="140" height="84" rx="16" fill="#f7f8fa"/>${txt(192 + i * 156, 160, l, 14, 700, C.grey)}${txt(192 + i * 156, 196, p + num(v * S(u, 0.14 + i * 0.05, 0.5)) + 'k', 26, 700, C.ink)}`, 14, 0.08)).join('')}
      ${[0.4, 0.55, 0.45, 0.7, 0.62, 0.85, 1].map((v, i) => { const h = 110 * v * S(u, 0.22 + i * 0.04, 0.5 + i * 0.04); return rect(196 + i * 60, 350 - h, 36, h, 9, i === 6 ? 'url(#rg)' : '#ffc9cf'); }).join('')}
      ${[0, 1].map((i) => pop(u, 0.4 + i * 0.05, `${rect(176, 376 + i * 48, 454, 34, 10, '#f7f8fa')}<circle cx="198" cy="${393 + i * 48}" r="9" fill="${i ? C.grey : C.red}"/>${rect(220, 388 + i * 48, 150, 10, 5, '#c9ccd3')}${rect(560, 388 + i * 48, 50, 10, 5, i ? '#c9ccd3' : '#86efac')}`, 10, 0.06)).join('')}`, 14)}
    ${link(780, 240, 780, 130, 0.3)}${link(780, 240, 700, 340, 0.36)}${link(780, 240, 860, 340, 0.42)}
    ${zoom(u, 0.24, 780, 240, `<circle cx="780" cy="240" r="${52 + 6 * Math.sin(u * Math.PI * 6)}" fill="${C.soft}"/><circle cx="780" cy="240" r="46" fill="url(#rg)" filter="url(#sh)"/>${icon('lucide:cloud', 758, 218, 44, '#fff', 2.2)}`, 0.12)}
    ${node(780, 122, 'lucide:webhook', 0.36)}${node(694, 356, 'lucide:database', 0.42)}${node(866, 356, 'lucide:smartphone', 0.48)}
    ${pop(u, 0.52, `${card(60, 520, 840, 164)}${txt(88, 566, 'Automated workflow', 18, 700, C.grey)}
      ${steps.map(([ic, l], i) => { const on = S(u, 0.56 + i * 0.1, 0.66 + i * 0.1); const x = 92 + i * 270; return `<rect x="${x}" y="588" width="216" height="74" rx="20" fill="${on > 0.5 ? C.soft : '#f4f5f7'}" stroke="${on > 0.5 ? C.red : 'none'}" stroke-width="2.5"/>${icon(ic, x + 22, 611, 28, on > 0.5 ? C.red : C.grey, 2.4)}${txt(x + 64, 634, l, 22, 700)}${i < 2 ? icon('lucide:arrow-right', x + 226, 612, 30, C.grey, 2.4) : ''}`; }).join('')}`)}
  `);
}

/* 6. Branding ----------------------------------------------------------- */
export function branding(u) {
  const sw = ['#e8202f', '#b0122c', '#161616', '#f1f2f5', '#ff8a95'];
  return frame(u, `
    ${pop(u, 0, `${card(60, 60, 370, 300)}
      ${zoom(u, 0.06, 245, 190, `<path d="M245 118 L292 146 L292 202 L245 230 L198 202 L198 146 Z" fill="url(#rg)"/><path d="M245 146 L268 160 L268 188 L245 202 L222 188 L222 160 Z" fill="#fff"/>`, 0.14)}
      ${rect(150, 262, 190 * S(u, 0.16, 0.3), 22, 11, C.ink)}${rect(180, 298, 130 * S(u, 0.2, 0.34), 12, 6, C.line)}`, 14)}
    ${pop(u, 0.14, `${card(460, 60, 440, 136)}${txt(486, 100, 'Colour palette', 17, 700, C.grey)}${sw.map((c, i) => zoom(u, 0.2 + i * 0.05, 510 + i * 78, 150, `<circle cx="${510 + i * 78}" cy="150" r="30" fill="${c}" stroke="#e5e7eb" stroke-width="2"/>`, 0.1)).join('')}`)}
    ${pop(u, 0.3, `${card(460, 220, 210, 140)}${zoom(u, 0.34, 520, 300, txt(520, 322, 'Aa', 64, 700, C.red, 'middle'), 0.12)}${rect(580, 268, 64, 12, 6, C.ink)}${rect(580, 292, 50, 10, 5, C.line)}${rect(580, 312, 60, 10, 5, C.line)}`)}
    ${pop(u, 0.38, `${card(690, 220, 210, 140)}${['lucide:gem', 'lucide:sparkles', 'lucide:star', 'lucide:heart'].map((n, i) => zoom(u, 0.42 + i * 0.05, 738 + (i % 2) * 90, 270 + Math.floor(i / 2) * 60, `<circle cx="${738 + (i % 2) * 90}" cy="${270 + Math.floor(i / 2) * 60}" r="24" fill="${C.soft}"/>${icon(n, 726 + (i % 2) * 90, 258 + Math.floor(i / 2) * 60, 24, C.red, 2.4)}`, 0.09)).join('')}`)}
    ${pop(u, 0.5, `${card(60, 390, 560, 294)}${txt(88, 434, 'Brand recall', 18, 700, C.grey)}${txt(88, 500, '+' + num(86 * S(u, 0.54, 0.88)) + '%', 56, 700, C.ink)}${upArrow(268, 458, 36)}
      ${chart('c6', 96, 530, 490, 120, [0.1, 0.16, 0.14, 0.3, 0.42, 0.5, 0.72, 0.9], seg(u, 0.54, 0.88))}`)}
    ${pop(u, 0.6, `${card(650, 390, 250, 294)}
      <g transform="rotate(-6 775 500)"><rect x="672" y="430" width="206" height="118" rx="14" fill="url(#rg)"/><path d="M700 470 L716 480 L716 500 L700 510 L684 500 L684 480 Z" fill="#fff"/>${rect(736, 468, 100, 12, 6, '#fff', 'opacity=".9"')}${rect(736, 490, 70, 9, 5, '#fff', 'opacity=".6"')}</g>
      <circle cx="712" cy="616" r="30" fill="url(#rg)"/>${txt(712, 626, 'G', 28, 700, '#fff', 'middle')}${rect(756, 600, 110, 14, 7, C.ink)}${rect(756, 626, 80, 10, 5, C.line)}`)}
  `);
}

export const scenes = { seo, paid, social, web, software, branding };


/* ===== Batch 2 heroes ===== */
const sq = (x, y, w, h, extra = '') => card(x, y, w, h, 0, extra);
const starRow = (x, y, n, s, on) => Array.from({ length: n }, (_, i) => icon('lucide:star', x + i * (s + 4), y, s, i < on ? '#f5b301' : '#d9dbe1', 2.4).replace('fill="currentColor"', `fill="${i < on ? '#f5b301' : 'none'}"`)).join('');

export function reputation(u) {
  const rating = 4.2 + 0.7 * S(u, 0.1, 0.7);
  const revs = [['Sarah M', 'Brilliant service, quick reply.'], ['James T', 'Highly recommend to anyone.'], ['Aisha K', 'Friendly team, fair price.']];
  return frame(u, `
    ${pop(u, 0, `${sq(60, 60, 330, 600)}${txt(225, 170, num(rating, 1), 100, 700, C.ink, 'middle')}${starRow(98, 200, 5, 30, Math.round(rating))}
      ${txt(225, 276, num(1284 * S(u, 0.1, 0.75)).replace(/\B(?=(\d{3})+(?!\d))/g, ',') + ' reviews', 20, 700, C.grey, 'middle')}
      ${[0.86, 0.09, 0.03, 0.01, 0.01].map((d, i) => `${txt(92, 344 + i * 56, String(5 - i), 18, 700, C.ink)}${rect(118, 330 + i * 56, 230, 18, 0, C.line)}${rect(118, 330 + i * 56, 230 * d * S(u, 0.15 + i * 0.04, 0.6), 18, 0, i ? '#ffc9cf' : C.red)}`).join('')}`, 16, 0.12)}
    ${revs.map(([n, t], i) => pop(u, 0.3 + i * 0.12, `${sq(420, 60 + i * 206, 480, 186)}<circle cx="466" cy="${106 + i * 206}" r="24" fill="${C.red}"/>${txt(466, 114 + i * 206, n[0], 22, 700, '#fff', 'middle')}
      ${txt(504, 102 + i * 206, n, 19, 700, C.ink)}${starRow(504, 112 + i * 206, 5, 18, 5)}${txt(444, 178 + i * 206, t, 18, 500, '#4d5156')}
      ${zoom(u, 0.45 + i * 0.12, 525, 222 + i * 206, `<rect x="444" y="${208 + i * 206}" width="160" height="26" fill="${C.soft}"/>${txt(456, 226 + i * 206, 'Owner replied', 14, 700, C.red)}`, 0.08)}`, 24)).join('')}`);
}

export function content(u) {
  const items = [['lucide:file-text', 'Guide'], ['lucide:circle-help', 'FAQ'], ['lucide:scale', 'Comparison'], ['lucide:calculator', 'Cost guide'], ['lucide:list-checks', 'Checklist'], ['lucide:video', 'Video']];
  return frame(u, `
    ${items.map(([ic, l], i) => { const x = 60 + (i % 3) * 287, y = 60 + Math.floor(i / 3) * 210; return pop(u, 0.05 + i * 0.08, `${sq(x, y, 266, 190)}<rect x="${x}" y="${y}" width="266" height="96" fill="${i % 2 ? '#fde7ea' : '#ffd0d5'}"/>${icon(ic, x + 107, y + 22, 52, C.red, 1.8)}
      <rect x="${x + 18}" y="${y + 112}" width="${l.length * 10 + 24}" height="26" fill="${C.ink}"/>${txt(x + 30, y + 130, l, 14, 700, '#fff')}${rect(x + 18, y + 152, 200, 9, 0, C.line)}${rect(x + 18, y + 168, 140, 9, 0, C.line)}`, 20, 0.1); }).join('')}
    ${pop(u, 0.55, `${sq(60, 490, 840, 170)}${txt(92, 534, 'Organic traffic from content', 20, 700, C.grey)}${txt(92, 600, '+' + num(212 * S(u, 0.6, 0.9)) + '%', 50, 700, C.ink)}
      ${chart('cc', 400, 520, 470, 120, [0.05, 0.1, 0.16, 0.22, 0.32, 0.44, 0.58, 0.76, 0.94], seg(u, 0.6, 0.9))}`)}`);
}

export function backlinks(u) {
  const pos = [[150, 140, 'lucide:newspaper', 'National press'], [480, 90, 'lucide:mic', 'Podcast'], [810, 140, 'lucide:radio', 'Regional news'], [130, 520, 'lucide:pen-line', 'Trade blog'], [480, 600, 'lucide:graduation-cap', 'University'], [830, 520, 'lucide:building-2', 'Association']];
  const dash = -u * 300;
  return frame(u, `
    ${pos.map(([x, y], i) => `<line x1="${x}" y1="${y}" x2="480" y2="350" stroke="${C.red}" stroke-width="3" stroke-dasharray="8 10" stroke-dashoffset="${dash}" opacity="${S(u, 0.2 + i * 0.08, 0.3 + i * 0.08) * 0.8}"/>`).join('')}
    ${pos.map(([x, y, ic, l], i) => zoom(u, 0.1 + i * 0.08, x, y, `${sq(x - 100, y - 46, 200, 92)}<rect x="${x - 84}" y="${y - 26}" width="52" height="52" fill="${C.soft}"/>${icon(ic, x - 72, y - 14, 28, C.red, 2)}${txt(x - 20, y + 7, l, 17, 700, C.ink)}`, 0.1)).join('')}
    ${pop(u, 0, `${sq(320, 266, 320, 168)}<rect x="320" y="266" width="320" height="8" fill="${C.red}"/>${txt(480, 318, 'yourwebsite.co.uk', 22, 700, C.ink, 'middle')}
      ${txt(480, 378, 'Authority ' + num(34 + 18 * S(u, 0.2, 0.8)), 40, 700, C.red, 'middle')}${txt(480, 412, '+' + num(38 * S(u, 0.2, 0.8)) + ' new links', 17, 700, C.grey, 'middle')}`, 14)}`);
}

export function digital(u) {
  const tiles = [['simple-icons:google', 'Search', '7.2%', 'CTR'], ['simple-icons:facebook', 'Facebook', '£9.80', 'Cost per lead'], ['simple-icons:instagram', 'Instagram', '3.4%', 'CTR'], ['simple-icons:tiktok', 'TikTok', '1.1M', 'Views'], ['lucide:monitor', 'Display', '1.4M', 'Impressions'], ['lucide:repeat', 'Retargeting', '8.1x', 'ROAS']];
  return frame(u, `
    ${txt(480, 86, 'Digital advertising, every channel', 28, 700, C.ink, 'middle')}
    ${tiles.map(([ic, n, m, l], i) => { const x = 60 + (i % 3) * 287, y = 120 + Math.floor(i / 3) * 270; const p = S(u, 0.15 + i * 0.07, 0.4 + i * 0.07);
      return pop(u, 0.05 + i * 0.07, `${sq(x, y, 266, 250)}<rect x="${x + 24}" y="${y + 24}" width="64" height="64" fill="${C.soft}"/>${icon(ic, x + 40, y + 40, 32, C.red, 2)}${txt(x + 24, y + 130, n, 24, 700, C.ink)}
        <g opacity="${p.toFixed(2)}">${txt(x + 24, y + 186, m, 36, 700, C.red)}${txt(x + 24, y + 218, l, 16, 700, C.grey)}</g>`, 22, 0.1); }).join('')}`);
}

export function paidmedia(u) {
  const slices = [['Google Search', 38, C.red], ['Meta', 26, '#ff7b89'], ['YouTube', 16, C.dark], ['LinkedIn', 12, '#ffc9cf'], ['TikTok', 8, '#161616']];
  const r = 150, circ = 2 * Math.PI * r, sweep = S(u, 0.1, 0.6);
  let off = 0;
  const segs = slices.map(([, pct, col]) => { const len = circ * pct / 100 * sweep; const sgm = `<circle cx="300" cy="360" r="${r}" fill="none" stroke="${col}" stroke-width="64" stroke-dasharray="${len.toFixed(1)} ${(circ - len).toFixed(1)}" stroke-dashoffset="${(-off).toFixed(1)}"/>`; off += len; return sgm; }).join('');
  return frame(u, `
    ${pop(u, 0, `${sq(60, 60, 500, 600)}${txt(92, 110, 'Media plan', 22, 700, C.grey)}<g transform="rotate(-90 300 360)">${segs}</g>${txt(300, 372, 'Budget', 26, 700, C.ink, 'middle')}`, 14)}
    ${pop(u, 0.2, `${sq(590, 60, 310, 420)}${slices.map(([l, pct, col], i) => `<rect x="618" y="${100 + i * 76}" width="26" height="26" fill="${col}"/>${txt(658, 120 + i * 76, l, 19, 700, C.ink)}${txt(870, 120 + i * 76, pct + '%', 19, 700, C.grey, 'end')}`).join('')}`)}
    ${pop(u, 0.45, `${sq(590, 500, 310, 160)}${txt(618, 540, 'Blended ROAS', 19, 700, C.grey)}${txt(618, 610, num(5.6 * S(u, 0.5, 0.85), 1) + 'x', 56, 700, C.red)}${upArrow(800, 570, 40)}`)}`);
}

Object.assign(scenes, { reputation, content, backlinks, digital, paidmedia });

/* ===== Batch 3: social platform heroes (one template, five platforms) ===== */
function platformHero(ic, name, metrics, cta) {
  return (u) => {
    const bob = (p) => Math.sin(u * Math.PI * 2 + p) * 6;
    const likes = 1 + 0.35 * Math.max(0, Math.sin(seg(u, 0.4, 0.55) * Math.PI));
    return frame(u, `
      ${pop(u, 0, `<rect x="330" y="40" width="300" height="640" fill="#fff" filter="url(#sh)"/><rect x="346" y="56" width="268" height="608" fill="#fafafa"/>
        <circle cx="382" cy="100" r="22" fill="url(#rg)"/>${icon(ic, 370, 88, 24, '#fff')}${txt(416, 96, name, 17, 700, C.ink)}${rect(416, 106, 80, 9, 0, C.line)}
        <rect x="362" y="136" width="236" height="300" fill="url(#rg)"/><circle cx="480" cy="286" r="56" fill="#fff" opacity=".22"/>${icon(ic, 450, 256, 60, '#fff')}
        <g transform="translate(386 466) scale(${likes}) translate(-386 -466)">${icon('lucide:heart', 372, 452, 28, C.red, 2.4).replace('fill="currentColor"', `fill="${C.red}"`)}</g>
        ${icon('lucide:message-circle', 416, 452, 28, C.ink, 2.2)}${icon('lucide:share-2', 460, 452, 28, C.ink, 2.2)}
        ${rect(372, 500, 200, 11, 0, C.line)}${rect(372, 520, 150, 11, 0, C.line)}
        <rect x="372" y="560" width="216" height="46" fill="url(#rg)"/>${txt(480, 590, cta, 17, 700, '#fff', 'middle')}`, 16, 0.12)}
      ${metrics.map(([label, target, fmt, mi], i) => {
        const x = i < 2 ? 40 : 660, y = i % 2 ? 380 : 130, v = target * S(u, 0.2 + i * 0.08, 0.75);
        return pop(u, 0.15 + i * 0.1, `<g transform="translate(0 ${bob(i * 1.5)})">${sq(x, y, 260, 200)}<rect x="${x + 22}" y="${y + 22}" width="52" height="52" fill="${C.soft}"/>${icon(mi, x + 34, y + 34, 28, C.red, 2.2)}
          ${txt(x + 22, y + 128, fmt(v), 42, 700, C.ink)}${txt(x + 22, y + 164, label, 17, 700, C.grey)}</g>`, 22);
      }).join('')}`);
  };
}
const k = (v) => (v >= 1000 ? (v / 1000).toFixed(v >= 10000 ? 0 : 1) + 'k' : Math.round(v).toString());
const facebook = platformHero('simple-icons:facebook', 'Facebook', [['Leads this month', 612, (v) => num(v), 'lucide:users'], ['Cost per lead', 8.6, (v) => '£' + v.toFixed(2), 'lucide:coins'], ['People reached', 182000, k, 'lucide:eye'], ['Engagement rate', 5.4, (v) => v.toFixed(1) + '%', 'lucide:heart']], 'Get quote');
const instagram = platformHero('simple-icons:instagram', 'Instagram', [['Reel views', 248000, k, 'lucide:eye'], ['New followers', 18400, k, 'lucide:users'], ['Shop orders', 1920, (v) => num(v), 'lucide:shopping-bag'], ['ROAS', 4.6, (v) => v.toFixed(1) + 'x', 'lucide:trending-up']], 'Shop now');
const linkedin = platformHero('lucide:linkedin', 'LinkedIn', [['B2B leads', 410, (v) => num(v), 'lucide:users'], ['Cost per lead', 39, (v) => '£' + Math.round(v), 'lucide:coins'], ['Meetings booked', 62, (v) => num(v), 'lucide:briefcase'], ['Pipeline', 480, (v) => '£' + Math.round(v) + 'k', 'lucide:trending-up']], 'Download guide');
const tiktok = platformHero('simple-icons:tiktok', 'TikTok', [['Video views', 1200000, (v) => (v / 1e6).toFixed(1) + 'M', 'lucide:eye'], ['Watch-through', 62, (v) => Math.round(v) + '%', 'lucide:play'], ['Orders', 1140, (v) => num(v), 'lucide:shopping-cart'], ['ROAS', 3.8, (v) => v.toFixed(1) + 'x', 'lucide:trending-up']], 'Shop now');
const pinterest = platformHero('simple-icons:pinterest', 'Pinterest', [['Monthly views', 420000, k, 'lucide:eye'], ['Pin saves', 18000, k, 'lucide:bookmark'], ['Outbound clicks', 9200, k, 'lucide:mouse-pointer-click'], ['ROAS', 5.0, (v) => v.toFixed(1) + 'x', 'lucide:trending-up']], 'Visit site');
Object.assign(scenes, { facebook, instagram, linkedin, tiktok, pinterest });

/* ===== Batch 4: web build heroes + Local/Ecommerce SEO (centre panel + counting metrics) ===== */
function panelHero(ic, name, kind, metrics) {
  const tones = ['#ff9eaa', '#9ec5ff', '#a7e3b5', '#c9ccd3'];
  const centre = (u) => {
    if (kind === 'map') {
      const pins = [[400, 250], [520, 210], [470, 340], [560, 400], [410, 450]];
      return `<rect x="346" y="100" width="268" height="380" fill="#eef1f4"/>
        ${[[346, 220, 614, 260], [346, 380, 614, 350], [440, 100, 470, 480], [540, 100, 520, 480]].map(([a, b, c, d]) => `<line x1="${a}" y1="${b}" x2="${c}" y2="${d}" stroke="#fff" stroke-width="12"/>`).join('')}
        ${pins.map(([x, y], i) => { const d = S(u, 0.1 + i * 0.08, 0.3 + i * 0.08); const me = i === 2; return `<g opacity="${d}" transform="translate(0 ${(1 - d) * -40})"><path d="M${x} ${y} c-20 -28 -20 -54 0 -54 c20 0 20 26 0 54z" fill="${me ? C.red : '#9aa0aa'}"/><circle cx="${x}" cy="${y - 34}" r="${me ? 10 : 7}" fill="#fff"/></g>`; }).join('')}
        <circle cx="470" cy="306" r="${30 + 30 * seg(u, 0.5, 0.9)}" fill="none" stroke="${C.red}" stroke-width="3" opacity="${1 - seg(u, 0.5, 0.9)}"/>
        <rect x="362" y="500" width="236" height="90" fill="#fff" stroke="${C.red}" stroke-width="3"/>${txt(380, 532, '1  Your Business', 17, 700, C.ink)}${txt(380, 562, '4.9 ★  Open now', 15, 700, C.red)}`;
    }
    if (kind === 'shop') {
      return `${[0, 1, 2, 3].map((i) => { const x = 362 + (i % 2) * 124, y = 100 + Math.floor(i / 2) * 200, d = S(u, 0.08 + i * 0.07, 0.3 + i * 0.07);
          return `<g opacity="${d}"><rect x="${x}" y="${y}" width="112" height="180" fill="#fff" stroke="${C.line}" stroke-width="2"/><rect x="${x + 10}" y="${y + 10}" width="92" height="92" fill="${i === 0 ? C.soft : '#f4f5f7'}"/>${icon('lucide:shopping-bag', x + 38, y + 38, 36, i === 0 ? C.red : '#b6bac3', 2)}
            ${rect(x + 10, y + 116, 80, 9, 0, C.ink)}${rect(x + 10, y + 134, 56, 9, 0, C.line)}${txt(x + 10, y + 168, '£' + (24 + i * 11), 16, 700, C.red)}</g>`; }).join('')}
        <rect x="362" y="510" width="236" height="60" fill="url(#rg)"/>${txt(480, 548, 'Add to basket', 17, 700, '#fff', 'middle')}`;
    }
    const lines = [[0, 150, 0], [0, 110, 1], [24, 170, 2], [24, 120, 0], [48, 140, 1], [48, 90, 2], [24, 60, 0], [0, 180, 1], [24, 130, 2], [0, 70, 0]];
    const shown = Math.floor(S(u, 0.05, 0.6) * (lines.length + 0.99));
    const prog = S(u, 0.55, 0.85);
    return `<rect x="346" y="100" width="268" height="360" fill="#1d1f27"/>
      ${lines.slice(0, shown).map(([ind, w, t], i) => `<rect x="${366 + ind}" y="${120 + i * 32}" width="${w}" height="12" fill="${tones[t]}" opacity=".9"/>`).join('')}
      ${u < 0.6 && Math.floor(u * 40) % 2 ? `<rect x="${366 + (lines[Math.max(0, shown - 1)] || [0])[0]}" y="${120 + shown * 32}" width="10" height="14" fill="#fff"/>` : ''}
      ${rect(362, 490, 236, 14, 0, C.line)}<rect x="362" y="490" width="${236 * prog}" height="14" fill="url(#rg)"/>
      ${txt(362, 540, prog < 1 ? 'Deploying…' : 'Live', 18, 700, prog < 1 ? C.grey : C.green)}${prog >= 1 ? icon('lucide:circle-check', 572, 520, 26, C.green, 2.4) : ''}
      ${txt(362, 580, 'All checks passed', 15, 700, C.grey)}`;
  };
  return (u) => {
    const bob = (p) => Math.sin(u * Math.PI * 2 + p) * 6;
    return frame(u, `
      ${pop(u, 0, `<rect x="330" y="40" width="300" height="580" fill="#fff" filter="url(#sh)"/><rect x="330" y="40" width="300" height="44" fill="#f1f2f5"/>
        ${[0, 1, 2].map((i) => `<circle cx="${352 + i * 18}" cy="62" r="5" fill="${['#ff5f57', '#febc2e', '#28c840'][i]}"/>`).join('')}
        ${icon(ic, 414, 50, 22, C.red)}${txt(444, 68, name, 16, 700, C.ink)}${centre(u)}`, 16, 0.12)}
      ${metrics.map(([label, target, fmt, mi], i) => {
        const x = i < 2 ? 40 : 660, y = i % 2 ? 380 : 130, v = target * S(u, 0.2 + i * 0.08, 0.75);
        return pop(u, 0.15 + i * 0.1, `<g transform="translate(0 ${bob(i * 1.5)})">${sq(x, y, 260, 200)}<rect x="${x + 22}" y="${y + 22}" width="52" height="52" fill="${C.soft}"/>${icon(mi, x + 34, y + 34, 28, C.red, 2.2)}
          ${txt(x + 22, y + 128, fmt(v), 42, 700, C.ink)}${txt(x + 22, y + 164, label, 17, 700, C.grey)}</g>`, 22);
      }).join('')}`);
  };
}
const pct = (v) => Math.round(v) + '%';
const ms = (v) => Math.round(v) + 'ms';
const sec = (v) => v.toFixed(1) + 's';
const wordpress = panelHero('simple-icons:wordpress', 'WordPress', 'code', [['PageSpeed score', 96, (v) => Math.round(v), 'lucide:gauge'], ['Load time', 1.2, sec, 'lucide:zap'], ['Plugins removed', 9, (v) => Math.round(v), 'lucide:puzzle'], ['Uptime', 99.98, (v) => v.toFixed(2) + '%', 'lucide:activity']]);
const php = panelHero('simple-icons:php', 'PHP 8.3', 'code', [['Response time', 180, ms, 'lucide:zap'], ['Faster than legacy', 4, (v) => v.toFixed(1) + 'x', 'lucide:gauge'], ['API endpoints', 42, (v) => Math.round(v), 'lucide:webhook'], ['Test coverage', 86, pct, 'lucide:shield-check']]);
const cms = panelHero('lucide:layout-template', 'Your CMS', 'code', [['Pages managed', 640, (v) => Math.round(v), 'lucide:file-text'], ['Publish time', 5, (v) => Math.round(v) + ' min', 'lucide:clock'], ['Editors trained', 24, (v) => Math.round(v), 'lucide:users'], ['Rankings kept', 100, pct, 'lucide:trending-up']]);
const laravel = panelHero('simple-icons:laravel', 'Laravel 11', 'code', [['Median response', 95, ms, 'lucide:zap'], ['Jobs per hour', 48000, k, 'lucide:layers'], ['Test coverage', 88, pct, 'lucide:shield-check'], ['Admin hours saved', 11, (v) => Math.round(v) + 'h/wk', 'lucide:clock']]);
const maintenance = panelHero('lucide:wrench', 'Site health', 'code', [['Uptime', 99.98, (v) => v.toFixed(2) + '%', 'lucide:activity'], ['Security score', 98, pct, 'lucide:shield-check'], ['Backups kept', 365, (v) => Math.round(v), 'lucide:database'], ['Response time', 15, (v) => '&lt; ' + Math.round(v) + 'm', 'lucide:bell']]);
const ecommerce = panelHero('lucide:shopping-cart', 'Your store', 'shop', [['Online revenue', 96, (v) => '£' + Math.round(v) + 'k', 'lucide:coins'], ['Conversion rate', 3.4, (v) => v.toFixed(1) + '%', 'lucide:shopping-cart'], ['Average order', 68, (v) => '£' + Math.round(v), 'lucide:receipt'], ['Cart abandonment', 24, (v) => '-' + Math.round(v) + '%', 'lucide:trending-down']]);
const webdesign = panelHero('lucide:palette', 'New design', 'code', [['Enquiry rate', 3.0, (v) => v.toFixed(1) + '%', 'lucide:mouse-pointer-click'], ['Leads vs old site', 71, (v) => '+' + Math.round(v) + '%', 'lucide:trending-up'], ['Bounce rate', 38, (v) => '-' + Math.round(v) + '%', 'lucide:trending-down'], ['Accessibility', 98, pct, 'lucide:accessibility']]);
const localseo = panelHero('lucide:map-pin', 'Google Maps', 'map', [['Map pack keywords', 48, (v) => Math.round(v), 'lucide:map-pin'], ['Calls from Google', 312, (v) => Math.round(v), 'lucide:phone'], ['Direction requests', 1840, (v) => Math.round(v).toLocaleString('en-GB'), 'lucide:navigation'], ['Google rating', 4.9, (v) => v.toFixed(1) + ' ★', 'lucide:star']]);
const ecomseo = panelHero('lucide:search', 'Organic shop', 'shop', [['Organic revenue', 184, (v) => '£' + Math.round(v) + 'k', 'lucide:coins'], ['Organic orders', 132, (v) => '+' + Math.round(v) + '%', 'lucide:shopping-cart'], ['Ranking keywords', 2860, (v) => Math.round(v).toLocaleString('en-GB'), 'lucide:search'], ['Free listing clicks', 64, (v) => '+' + Math.round(v) + '%', 'lucide:shopping-bag']]);
Object.assign(scenes, { wordpress, php, cms, laravel, maintenance, ecommerce, webdesign, localseo, ecomseo });
