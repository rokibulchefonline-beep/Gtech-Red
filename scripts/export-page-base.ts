// Writes the built-in copy of every editable page to backend/database/data/page-base.json, so the Laravel
// panel can show the current text when someone edits a page. Run after changing page copy in content/:
//   npx tsx scripts/export-page-base.ts
import { writeFileSync } from 'fs';
import { industryContent } from '../content/industries';
import { serviceContent } from '../content/services';
import { aboutContent } from '../content/static/about';
import { contactContent } from '../content/static/contact';
import { homeContent } from '../content/static/home';
import { industriesHubContent } from '../content/static/industries-hub';
import { servicesHubContent } from '../content/static/services-hub';
import type { ServiceContent } from '../content/types';

const ITEMS = ['cards', 'steps', 'stats', 'reviews', 'items'] as const;
const pagePath: Record<string, string> = { home: '/', about: '/about', contact: '/contact', 'services-hub': '/services', 'industries-hub': '/industries' };

function base(kind: 'service' | 'industry' | 'page', c: ServiceContent) {
  const name = c.short ?? c.slug;
  return {
    key: `${kind}~${c.slug}`, kind, slug: c.slug, name,
    path: kind === 'page' ? pagePath[c.slug] : `/${kind === 'service' ? 'services' : 'industries'}/${c.slug}`,
    metaTitle: c.metaTitle, metaDescription: c.metaDescription,
    hero: { keyword: c.hero.keyword ?? name, h1: c.hero.h1 ?? `${c.hero.keyword ?? name} Services of [[GTech Digital]]`, lead: c.hero.lead, points: c.hero.points },
    sections: c.sections.map((s) => {
      const x = s as unknown as Record<string, unknown>;
      const out: Record<string, unknown> = { id: s.id, type: s.type, nav: x.nav ?? '' };
      for (const f of ['heading', 'intro', 'text', 'paras', 'bullets', ...ITEMS]) if (x[f] !== undefined) out[f] = x[f];
      return out;
    }).filter((s) => Object.keys(s).length > 3),
    faqs: c.faqs,
  };
}

const pages = [
  ...[homeContent, aboutContent, contactContent, servicesHubContent, industriesHubContent].map((c) => base('page', c)),
  ...Object.values(serviceContent).map((c) => base('service', c)),
  ...Object.values(industryContent).map((c) => base('industry', c)),
];
writeFileSync(new URL('../backend/database/data/page-base.json', import.meta.url), JSON.stringify(pages, null, 1));
console.log(`Wrote ${pages.length} pages`);
