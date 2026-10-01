// Static illustrations for long-form pages, drawn in code -> public/pages/<page>/<name>.webp
// Run: npm run page-visuals   (add scenes below for each new page)
import { mkdirSync } from 'node:fs';
import sharp from 'sharp';
import { C, icon, txt, rect, card, chart, frame } from './visuals-lib.mjs';

const U = 0.9; // a fully-built frame of the shared scene style
const up = (x, y, s = 30) => icon('lucide:trending-up', x, y, s, C.green, 2.6);

const scenes = {
  'seo/why': () => frame(U, `
    ${card(60, 60, 840, 380)}${txt(92, 112, 'Organic leads per month', 22, 700, C.grey)}${txt(92, 176, '+186%', 60, 700, C.ink)}${up(300, 136, 40)}
    ${chart('w1', 96, 210, 770, 200, [0.06, 0.1, 0.16, 0.2, 0.3, 0.36, 0.45, 0.52, 0.6, 0.7, 0.8, 0.94], 1)}
    ${[['Organic traffic', '+142%', 'lucide:users'], ['Cost per lead', '-41%', 'lucide:trending-down'], ['Page 1 keywords', '96', 'lucide:search']].map(([l, v, ic], i) => `
      ${card(60 + i * 287, 476, 266, 184)}<circle cx="${110 + i * 287}" cy="${530}" r="28" fill="${C.soft}"/>${icon(ic, 94 + i * 287, 514, 32, C.red, 2.4)}
      ${txt(84 + i * 287, 606, v, 44, 700, C.ink)}${txt(84 + i * 287, 638, l, 18, 700, C.grey)}`).join('')}`),

  'seo/technical': () => {
    const gauge = (cx, label, val, max, unit, ok) => {
      const p = Math.min(1, val / max), a0 = Math.PI * 0.8, a1 = a0 + Math.PI * 1.4 * p, r = 70;
      const pt = (a) => `${(cx + r * Math.cos(a)).toFixed(1)} ${(250 + r * Math.sin(a)).toFixed(1)}`;
      return `${card(cx - 130, 110, 260, 300)}${txt(cx, 156, label, 22, 700, C.grey, 'middle')}
        <path d="M${pt(a0)} A${r} ${r} 0 1 1 ${pt(Math.PI * 2.2)}" fill="none" stroke="${C.line}" stroke-width="16" stroke-linecap="round"/>
        <path d="M${pt(a0)} A${r} ${r} 0 ${p > 0.72 ? 1 : 0} 1 ${pt(a1)}" fill="none" stroke="${ok ? C.green : C.red}" stroke-width="16" stroke-linecap="round"/>
        ${txt(cx, 262, val + unit, 34, 700, C.ink, 'middle')}
        <rect x="${cx - 54}" y="348" width="108" height="36" rx="18" fill="${ok ? '#dcfce7' : C.soft}"/>${txt(cx, 373, ok ? 'Good' : 'Fix', 18, 700, ok ? C.green : C.red, 'middle')}`;
    };
    return frame(U, `
      ${txt(480, 76, 'Core Web Vitals', 30, 700, C.ink, 'middle')}
      ${gauge(190, 'LCP', 1.9, 4, 's', true)}${gauge(480, 'INP', 140, 500, 'ms', true)}${gauge(770, 'CLS', 0.04, 0.25, '', true)}
      ${card(60, 446, 840, 220)}${txt(92, 494, 'Technical audit', 22, 700, C.grey)}
      ${[['Crawl errors fixed', 'lucide:circle-check'], ['Sitemap &amp; robots.txt', 'lucide:circle-check'], ['Canonical tags', 'lucide:circle-check'], ['Schema markup', 'lucide:circle-check'], ['Redirect chains', 'lucide:circle-check'], ['Mobile usability', 'lucide:circle-check']]
        .map(([l, ic], i) => `${icon(ic, 92 + (i % 2) * 410, 520 + Math.floor(i / 2) * 44, 28, C.green, 2.4)}${txt(132 + (i % 2) * 410, 543 + Math.floor(i / 2) * 44, l, 20, 700, C.ink)}`).join('')}`);
  },

  'seo/local': () => frame(U, `
    ${card(60, 60, 470, 600)}
    <rect x="60" y="60" width="470" height="330" rx="22" fill="#eef2f6"/>
    ${[[120, 'M60 180 C 200 160, 300 260, 530 210'], [0, 'M180 60 C 220 200, 160 300, 260 390'], [0, 'M60 300 L 530 330']].map(([, d]) => `<path d="${d}" stroke="#fff" stroke-width="16" fill="none"/>`).join('')}
    <rect x="320" y="90" width="120" height="80" rx="10" fill="#dbe7d4"/><rect x="90" y="250" width="90" height="100" rx="10" fill="#dbe7d4"/>
    ${[[230, 210, true], [390, 260, false], [140, 140, false]].map(([x, y, me]) => `<g transform="translate(${x} ${y})"><path d="M0 -44 C 26 -44 34 -22 34 -12 C 34 10 0 40 0 40 C 0 40 -34 10 -34 -12 C -34 -22 -26 -44 0 -44 Z" fill="${me ? C.red : '#9aa0ae'}"/><circle cx="0" cy="-14" r="12" fill="#fff"/></g>`).join('')}
    ${[0, 1, 2].map((i) => `${rect(90, 412 + i * 82, 410, 66, 14, i === 0 ? C.soft : '#f7f8fa', i === 0 ? `stroke="${C.red}" stroke-width="2.5"` : '')}${rect(110, 430 + i * 82, i === 0 ? 210 : 170, 14, 7, i === 0 ? C.ink : '#c9ccd3')}${[0, 1, 2, 3, 4].map((s) => icon('lucide:star', 110 + s * 22, 450 + i * 82, 18, '#f5b301', 2.6)).join('')}${txt(232, 466 + i * 82, i === 0 ? '4.9 (212)' : '4.2 (38)', 15, 700, C.grey)}`).join('')}
    ${card(570, 60, 330, 280)}${txt(600, 108, 'Google Business Profile', 19, 700, C.grey)}
    ${['Calls', 'Directions', 'Website clicks'].map((l, i) => `${txt(600, 168 + i * 56, l, 20, 700, C.ink)}${rect(760, 152 + i * 56, 100, 18, 9, C.line)}${rect(760, 152 + i * 56, [92, 70, 84][i], 18, 9, C.red)}`).join('')}
    ${card(570, 370, 330, 290)}${txt(600, 418, 'Local pack position', 19, 700, C.grey)}${txt(600, 520, '#1', 96, 700, C.red)}${up(720, 456, 44)}${txt(600, 580, 'for 34 local searches', 20, 700, C.ink)}
    ${txt(600, 616, 'NAP consistent across 48 citations', 16, 500, C.grey)}`),
};

for (const [name, draw] of Object.entries(scenes)) {
  const file = new URL(`../public/pages/${name}.webp`, import.meta.url);
  mkdirSync(new URL('.', file), { recursive: true });
  await sharp(Buffer.from(draw())).webp({ quality: 86, effort: 5 }).toFile(file.pathname);
  console.log(name);
}
