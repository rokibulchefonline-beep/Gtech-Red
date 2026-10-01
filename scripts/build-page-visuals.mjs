// Static illustrations for long-form pages, drawn in code -> public/pages/<page>/<name>.webp
// Run: npm run page-visuals   (add scenes below for each new page)
import { mkdirSync } from 'node:fs';
import sharp from 'sharp';
import { C, icon, txt, rect, card, chart, frame } from './visuals-lib.mjs';
import * as K from './visuals-kit.mjs';

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

  // ---------- Google Ads ----------
  'google-ads/search': () => frame(U, K.searchAd({ query: 'emergency plumber manchester', url: 'yourbusiness.co.uk', headline: '24/7 Emergency Plumber | Here in 60 Minutes', desc: 'Gas Safe engineers. Fixed prices. No call-out fee. Book online in seconds.', sitelinks: ['Book now', 'Prices', 'Reviews', 'Areas'], kpis: [{ icon: 'lucide:mouse-pointer-click', value: '8.4%', label: 'Click-through rate' }, { icon: 'lucide:phone', value: '312', label: 'Calls this month' }, { icon: 'lucide:coins', value: '£18', label: 'Cost per lead' }] })),
  'google-ads/pmax': () => frame(U, K.platforms({ title: 'Performance Max & Shopping', tiles: [{ icon: 'lucide:search', name: 'Search', metric: '4.9x', label: 'ROAS' }, { icon: 'lucide:shopping-bag', name: 'Shopping', metric: '£42k', label: 'Revenue' }, { icon: 'simple-icons:youtube', name: 'YouTube', metric: '210k', label: 'Views' }, { icon: 'lucide:monitor', name: 'Display', metric: '1.2M', label: 'Impressions' }, { icon: 'lucide:mail', name: 'Gmail', metric: '6.1%', label: 'Open-to-click' }, { icon: 'lucide:map-pin', name: 'Maps', metric: '1,840', label: 'Direction requests' }] })),
  'google-ads/display': () => frame(U, K.videoAd({ title: 'Summer collection', platform: 'simple-icons:youtube', kpis: ['184k', '2.6%', '1,206', '£11.40'] })),
  'google-ads/tracking': () => frame(U, K.checklist({ title: 'Conversion tracking', scoreLabel: 'Tracking health', score: '96%', items: [{ ok: true, label: 'GA4 linked to Google Ads' }, { ok: true, label: 'Enhanced conversions' }, { ok: true, label: 'Call tracking' }, { ok: true, label: 'Offline conversion import' }, { ok: true, label: 'Consent Mode v2' }, { ok: false, label: 'Duplicate tag removed' }] })),
  // ---------- Reputation management ----------
  'reputation/reviews': () => frame(U, K.reviews({ score: '4.9', count: '1,284 Google reviews', items: [{ name: 'Sarah M', text: 'Fast, friendly and fairly priced.', reply: true }, { name: 'James T', text: 'Best service in town, highly recommend.', reply: true }, { name: 'Aisha K', text: 'Booked online and they arrived early.', reply: false }] })),
  'reputation/monitoring': () => frame(U, K.dashboard({ title: 'Average star rating', value: '4.2 → 4.8', pts: [0.3, 0.32, 0.36, 0.4, 0.5, 0.58, 0.66, 0.74, 0.8, 0.86, 0.9, 0.94], kpis: [{ icon: 'lucide:bell', value: '< 2 hrs', label: 'Response time' }, { icon: 'lucide:message-square', value: '100%', label: 'Reviews answered' }, { icon: 'lucide:star', value: '+620', label: 'New reviews' }] })),
  'reputation/serp': () => frame(U, K.network({ center: 'Your brand search', value: '9 / 10', sub: 'positive page-one results', nodes: [{ icon: 'lucide:globe', label: 'Your website' }, { icon: 'lucide:map-pin', label: 'Google profile' }, { icon: 'lucide:newspaper', label: 'Press feature' }, { icon: 'simple-icons:linkedin', label: 'LinkedIn' }, { icon: 'lucide:star', label: 'Trustpilot' }, { icon: 'lucide:users', label: 'Case study' }] })),
  'reputation/ai': () => frame(U, K.chat({ query: 'is yourbrand trustworthy', source: 'yourbrand.co.uk', side: 'AI Overview' })),
  // ---------- Content marketing ----------
  'content/strategy': () => frame(U, K.network({ center: 'Pillar page', value: '24', sub: 'supporting articles', nodes: [{ icon: 'lucide:file-text', label: 'How-to guide' }, { icon: 'lucide:circle-help', label: 'FAQ answers' }, { icon: 'lucide:scale', label: 'Comparison' }, { icon: 'lucide:list-checks', label: 'Checklist' }, { icon: 'lucide:calculator', label: 'Cost guide' }, { icon: 'lucide:book-open', label: 'Glossary' }] })),
  'content/articles': () => frame(U, K.contentGrid({ title: 'Monthly content plan', items: [{ icon: 'lucide:file-text', type: 'Guide', title: 'Complete buyer guide' }, { icon: 'lucide:circle-help', type: 'FAQ', title: 'Top 20 questions' }, { icon: 'lucide:scale', type: 'Comparison', title: 'Option A vs B' }, { icon: 'lucide:calculator', type: 'Cost guide', title: 'Prices in 2026' }, { icon: 'lucide:list-checks', type: 'Checklist', title: 'Before you buy' }, { icon: 'lucide:newspaper', type: 'News', title: 'Industry update' }] })),
  'content/video': () => frame(U, K.videoAd({ title: 'How it works in 60s', platform: 'simple-icons:youtube', kpis: ['96k', '4.1%', '842', '£6.20'] })),
  'content/distribution': () => frame(U, K.dashboard({ title: 'Organic traffic from content', value: '+212%', pts: [0.05, 0.08, 0.12, 0.15, 0.22, 0.3, 0.38, 0.47, 0.58, 0.68, 0.8, 0.94], kpis: [{ icon: 'lucide:file-text', value: '48', label: 'Articles published' }, { icon: 'lucide:sparkles', value: '31', label: 'AI citations' }, { icon: 'lucide:mail', value: '2,140', label: 'New subscribers' }] })),
  // ---------- SEO backlinks ----------
  'backlinks/pr': () => frame(U, K.network({ center: 'yourwebsite.co.uk', value: 'Authority 52', sub: '+38 links from PR', nodes: [{ icon: 'lucide:newspaper', label: 'National press' }, { icon: 'lucide:mic', label: 'Podcast' }, { icon: 'lucide:radio', label: 'Regional news' }, { icon: 'lucide:pen-line', label: 'Trade magazine' }, { icon: 'lucide:graduation-cap', label: 'University' }, { icon: 'lucide:building-2', label: 'Association' }] })),
  'backlinks/outreach': () => frame(U, K.funnel({ title: 'Outreach campaign', stages: [{ label: 'Prospects found', value: '640' }, { label: 'Relevant sites', value: '210' }, { label: 'Replies', value: '74' }, { label: 'Links earned', value: '29' }], kpis: [{ icon: 'lucide:link', value: '29', label: 'New backlinks' }, { icon: 'lucide:shield-check', value: '100%', label: 'White-hat' }, { icon: 'lucide:trending-up', value: '+9', label: 'Authority score' }] })),
  'backlinks/local': () => frame(U, K.checklist({ title: 'Local citations', scoreLabel: 'NAP consistency', score: '98%', items: [{ ok: true, label: 'Google Business Profile' }, { ok: true, label: 'Bing Places' }, { ok: true, label: 'Apple Business Connect' }, { ok: true, label: 'Yell & Thomson Local' }, { ok: true, label: 'Industry directories' }, { ok: false, label: 'Old address fixed' }] })),
  'backlinks/audit': () => frame(U, K.dashboard({ title: 'Healthy referring domains', value: '412', pts: [0.4, 0.36, 0.3, 0.34, 0.42, 0.5, 0.56, 0.62, 0.7, 0.78, 0.86, 0.92], bars: true, kpis: [{ icon: 'lucide:shield-alert', value: '57', label: 'Toxic links found' }, { icon: 'lucide:unlink', value: '57', label: 'Removed or disavowed' }, { icon: 'lucide:link', value: '+96', label: 'New quality links' }] })),
  // ---------- Digital advertising ----------
  'digital-advertising/search': () => frame(U, K.searchAd({ query: 'accountant for small business', url: 'yourfirm.co.uk', headline: 'Small Business Accountants | Fixed Monthly Fees', desc: 'ICAEW chartered. Xero partners. Free 30-minute consultation.', sitelinks: ['Pricing', 'Services', 'Reviews', 'Contact'], kpis: [{ icon: 'lucide:mouse-pointer-click', value: '7.2%', label: 'Click-through rate' }, { icon: 'lucide:users', value: '186', label: 'Leads this month' }, { icon: 'lucide:coins', value: '£24', label: 'Cost per lead' }] })),
  'digital-advertising/social': () => frame(U, K.platforms({ title: 'Social advertising', tiles: [{ icon: 'simple-icons:facebook', name: 'Facebook', metric: '£9.80', label: 'Cost per lead' }, { icon: 'simple-icons:instagram', name: 'Instagram', metric: '3.4%', label: 'Click-through' }, { icon: 'simple-icons:tiktok', name: 'TikTok', metric: '1.1M', label: 'Video views' }, { icon: 'lucide:linkedin', name: 'LinkedIn', metric: '£42', label: 'Cost per B2B lead' }, { icon: 'simple-icons:pinterest', name: 'Pinterest', metric: '5.2x', label: 'ROAS' }, { icon: 'simple-icons:youtube', name: 'YouTube', metric: '£0.03', label: 'Cost per view' }] })),
  'digital-advertising/display': () => frame(U, K.videoAd({ title: 'Spring offer', platform: 'lucide:monitor', kpis: ['1.4M', '0.9%', '734', '£14.10'] })),
  'digital-advertising/retargeting': () => frame(U, K.funnel({ title: 'Retargeting journey', stages: [{ label: 'Visited site', value: '24,800' }, { label: 'Saw retargeting ad', value: '11,200' }, { label: 'Returned', value: '2,960' }, { label: 'Converted', value: '612' }], kpis: [{ icon: 'lucide:repeat', value: '21%', label: 'Return rate' }, { icon: 'lucide:shopping-cart', value: '612', label: 'Conversions' }, { icon: 'lucide:coins', value: '8.1x', label: 'Retargeting ROAS' }] })),
  // ---------- Paid media ----------
  'paid-media/planning': () => frame(U, K.budget({ title: 'Media plan', roas: '5.6x', slices: [{ label: 'Google Search', pct: 38, color: C.red }, { label: 'Meta', pct: 26, color: '#ff7b89' }, { label: 'YouTube', pct: 16, color: '#b0122c' }, { label: 'LinkedIn', pct: 12, color: '#ffc9cf' }, { label: 'TikTok', pct: 8, color: '#161616' }] })),
  'paid-media/channels': () => frame(U, K.platforms({ title: 'Cross-channel performance', tiles: [{ icon: 'lucide:search', name: 'Google Ads', metric: '6.1x', label: 'ROAS' }, { icon: 'simple-icons:facebook', name: 'Meta', metric: '4.3x', label: 'ROAS' }, { icon: 'lucide:linkedin', name: 'LinkedIn', metric: '£38', label: 'Cost per lead' }, { icon: 'simple-icons:tiktok', name: 'TikTok', metric: '3.8x', label: 'ROAS' }, { icon: 'simple-icons:youtube', name: 'YouTube', metric: '£0.02', label: 'Cost per view' }, { icon: 'lucide:monitor', name: 'Programmatic', metric: '2.1M', label: 'Impressions' }] })),
  'paid-media/creative': () => frame(U, K.contentGrid({ title: 'Creative testing', items: [{ icon: 'lucide:image', type: 'Variant A', title: 'Product photo' }, { icon: 'lucide:video', type: 'Variant B', title: 'UGC video' }, { icon: 'lucide:quote', type: 'Variant C', title: 'Customer review' }, { icon: 'lucide:percent', type: 'Variant D', title: 'Offer-led' }, { icon: 'lucide:sparkles', type: 'Winner', title: '+64% CTR vs control' }, { icon: 'lucide:layers', type: 'Next', title: 'Scale winning angle' }] })),
  'paid-media/attribution': () => frame(U, K.dashboard({ title: 'Revenue from paid media', value: '£184k', pts: [0.2, 0.26, 0.24, 0.34, 0.4, 0.46, 0.52, 0.6, 0.66, 0.74, 0.82, 0.92], bars: true, kpis: [{ icon: 'lucide:coins', value: '-32%', label: 'Cost per acquisition' }, { icon: 'lucide:trending-up', value: '5.6x', label: 'Blended ROAS' }, { icon: 'lucide:chart-column-increasing', value: '100%', label: 'Spend tracked' }] })),
};

const only = process.argv.find((a) => a.startsWith('--only='))?.slice(7);
for (const [name, draw] of Object.entries(scenes)) {
  if (only && !name.startsWith(only)) continue;
  const file = new URL(`../public/pages/${name}.webp`, import.meta.url);
  mkdirSync(new URL('.', file), { recursive: true });
  await sharp(Buffer.from(draw())).webp({ quality: 86, effort: 5 }).toFile(file.pathname);
  console.log(name);
}
