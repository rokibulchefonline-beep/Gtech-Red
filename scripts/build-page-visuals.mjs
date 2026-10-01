// Static illustrations for long-form pages, drawn in code -> public/pages/<page>/<name>.webp
// Run: npm run page-visuals   (add scenes below for each new page)
import { mkdirSync } from 'node:fs';
import sharp from 'sharp';
import { C, icon, txt, rect, card, chart, frame } from './visuals-lib.mjs';

const U = 0.9; // a fully-built frame of the shared scene style
const up = (x, y, s = 30) => icon('lucide:trending-up', x, y, s, C.green, 2.6);

const scenes = {
  'seo/technical': () => {
    const gauge = (cx, label, val, max, unit, ok) => {
      const p = Math.min(1, val / max), a0 = Math.PI * 0.8, a1 = a0 + Math.PI * 1.4 * p, r = 70;
      const pt = (a) => `${(cx + r * Math.cos(a)).toFixed(1)} ${(250 + r * Math.sin(a)).toFixed(1)}`;
      return `${card(cx - 130, 110, 260, 300, 0)}${txt(cx, 156, label, 22, 700, C.grey, 'middle')}
        <path d="M${pt(a0)} A${r} ${r} 0 1 1 ${pt(Math.PI * 2.2)}" fill="none" stroke="${C.line}" stroke-width="16" stroke-linecap="round"/>
        <path d="M${pt(a0)} A${r} ${r} 0 ${p > 0.72 ? 1 : 0} 1 ${pt(a1)}" fill="none" stroke="${ok ? C.green : C.red}" stroke-width="16" stroke-linecap="round"/>
        ${txt(cx, 262, val + unit, 34, 700, C.ink, 'middle')}
        <rect x="${cx - 54}" y="348" width="108" height="36" fill="${ok ? '#dcfce7' : C.soft}"/>${txt(cx, 373, ok ? 'Good' : 'Fix', 18, 700, ok ? C.green : C.red, 'middle')}`;
    };
    return frame(U, `
      ${txt(480, 76, 'Core Web Vitals', 30, 700, C.ink, 'middle')}
      ${gauge(190, 'LCP', 1.9, 4, 's', true)}${gauge(480, 'INP', 140, 500, 'ms', true)}${gauge(770, 'CLS', 0.04, 0.25, '', true)}
      ${card(60, 446, 840, 220, 0)}${txt(92, 494, 'Technical audit', 22, 700, C.grey)}
      ${[['Crawl errors fixed', 'lucide:circle-check'], ['Sitemap &amp; robots.txt', 'lucide:circle-check'], ['Canonical tags', 'lucide:circle-check'], ['Schema markup', 'lucide:circle-check'], ['Redirect chains', 'lucide:circle-check'], ['Mobile usability', 'lucide:circle-check']]
        .map(([l, ic], i) => `${icon(ic, 92 + (i % 2) * 410, 520 + Math.floor(i / 2) * 44, 28, C.green, 2.4)}${txt(132 + (i % 2) * 410, 543 + Math.floor(i / 2) * 44, l, 20, 700, C.ink)}`).join('')}`);
  },

  'seo/onpage': () => {
    const tag = (x, y, label, tx, ty) => `<line x1="${x}" y1="${y}" x2="${tx}" y2="${ty}" stroke="${C.red}" stroke-width="2.5" stroke-dasharray="6 6"/><circle cx="${x}" cy="${y}" r="7" fill="${C.red}"/>
      <rect x="${tx}" y="${ty - 22}" width="${label.length * 11.5 + 30}" height="44" fill="${C.red}"/>${txt(tx + 15, ty + 7, label, 19, 700, '#fff')}`;
    return frame(U, `
      ${card(60, 60, 560, 600, 0)}<rect x="60" y="60" width="560" height="44" fill="#f1f2f5"/>
      ${[0, 1, 2].map((i) => `<circle cx="${86 + i * 20}" cy="82" r="6" fill="${['#ff5f57', '#febc2e', '#28c840'][i]}"/>`).join('')}${rect(160, 72, 300, 20, 0, '#fff')}
      ${rect(96, 136, 380, 26, 0, C.ink)}${rect(96, 176, 300, 12, 0, '#c9ccd3')}
      ${rect(96, 226, 470, 150, 0, '#fde7ea')}${icon('lucide:image', 300, 270, 64, C.red, 1.6)}
      ${rect(96, 404, 240, 18, 0, C.ink)}${[0, 1, 2].map((i) => rect(96, 438 + i * 22, 470 - i * 60, 10, 0, C.line)).join('')}
      ${rect(96, 520, 200, 18, 0, C.ink)}${[0, 1].map((i) => rect(96, 554 + i * 22, 440 - i * 90, 10, 0, C.line)).join('')}
      ${rect(392, 592, 120, 12, 0, C.red)}
      ${tag(476, 149, 'Title tag + H1', 670, 110)}${tag(396, 182, 'Meta description', 670, 200)}${tag(566, 300, 'Image alt text', 670, 300)}
      ${tag(336, 413, 'H2 headings', 670, 400)}${tag(512, 598, 'Internal links', 670, 590)}${tag(566, 470, 'Schema markup', 670, 495)}`);
  },

  'seo/offpage': () => {
    const nodes = [[150, 140, 'lucide:newspaper', 'UK news site'], [480, 90, 'lucide:mic', 'Podcast'], [810, 140, 'lucide:graduation-cap', 'Trade body'],
      [130, 520, 'lucide:pen-line', 'Industry blog'], [480, 600, 'lucide:map-pin', 'Local directory'], [830, 520, 'lucide:users', 'Partner site']];
    return frame(U, `
      ${nodes.map(([x, y]) => `<line x1="${x}" y1="${y}" x2="480" y2="350" stroke="${C.red}" stroke-width="3" stroke-dasharray="8 8" opacity=".7"/>`).join('')}
      ${nodes.map(([x, y, ic, l]) => `${card(x - 95, y - 46, 190, 92, 0)}<rect x="${x - 79}" y="${y - 26}" width="52" height="52" fill="${C.soft}"/>${icon(ic, x - 67, y - 14, 28, C.red, 2)}${txt(x - 16, y + 7, l, 17, 700, C.ink)}`).join('')}
      ${card(330, 270, 300, 160, 0)}<rect x="330" y="270" width="300" height="8" fill="${C.red}"/>
      ${txt(480, 322, 'yourwebsite.co.uk', 22, 700, C.ink, 'middle')}${txt(480, 378, 'Authority 48', 40, 700, C.red, 'middle')}${txt(480, 410, '+126 referring domains', 17, 700, C.grey, 'middle')}`);
  },

  'seo/aeo-geo': () => frame(U, `
    ${card(50, 60, 430, 600, 0)}${rect(78, 90, 374, 44, 0, '#f1f2f5')}${icon('lucide:search', 92, 100, 24, C.grey, 2)}${txt(126, 119, 'best seo agency uk', 18, 500, C.ink)}
    <rect x="78" y="156" width="374" height="270" fill="#fff5f6" stroke="${C.red}" stroke-width="2"/>${icon('lucide:sparkles', 96, 172, 26, C.red, 2)}${txt(130, 193, 'AI Overview', 19, 700, C.ink)}
    ${[0, 1, 2, 3, 4].map((i) => rect(96, 216 + i * 24, 330 - (i % 2) * 70, 11, 0, '#e3c9cd')).join('')}
    <rect x="96" y="352" width="210" height="40" fill="#fff" stroke="${C.red}" stroke-width="2"/>${icon('lucide:link', 106, 360, 22, C.red, 2)}${txt(136, 378, 'yourwebsite.co.uk', 15, 700, C.red)}
    ${txt(96, 466, 'People also ask', 18, 700, C.ink)}${[0, 1, 2].map((i) => `${rect(96, 484 + i * 52, 336, 40, 0, '#f6f7f9')}${rect(110, 499 + i * 52, 220 - i * 30, 10, 0, '#c9ccd3')}`).join('')}
    ${card(520, 100, 390, 520, 0)}<rect x="520" y="100" width="390" height="56" fill="${C.ink}"/>${icon('lucide:message-circle', 540, 114, 28, '#fff', 2)}${txt(580, 136, 'AI assistant', 19, 700, '#fff')}
    <rect x="640" y="186" width="246" height="56" fill="${C.line}"/>${rect(656, 202, 200, 10, 0, '#9aa0ae')}${rect(656, 222, 140, 10, 0, '#9aa0ae')}
    <rect x="544" y="270" width="320" height="200" fill="#fff5f6"/>${[0, 1, 2, 3, 4, 5].map((i) => rect(562, 292 + i * 24, 280 - (i % 3) * 50, 10, 0, '#e3c9cd')).join('')}
    <rect x="544" y="490" width="250" height="44" fill="${C.red}"/>${txt(560, 518, 'Source: yourwebsite.co.uk', 16, 700, '#fff')}
    ${rect(544, 560, 342, 40, 0, '#f6f7f9')}${txt(560, 586, 'Ask a follow-up…', 16, 500, C.grey)}`),
};

for (const [name, draw] of Object.entries(scenes)) {
  const file = new URL(`../public/pages/${name}.webp`, import.meta.url);
  mkdirSync(new URL('.', file), { recursive: true });
  await sharp(Buffer.from(draw())).webp({ quality: 86, effort: 5 }).toFile(file.pathname);
  console.log(name);
}
