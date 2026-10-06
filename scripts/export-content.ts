// Exports ALL website content from the code into backend/database/data/content.json, so it can be loaded into
// MySQL (php artisan gtech:seed-content). After this, the Laravel panel is the place to edit content.
//   npm run export:content
import { writeFileSync } from 'fs';
import { industryContent } from '../content/industries';
import cookies from '../content/legal/cookies';
import { company } from '../content/legal/company';
import privacy from '../content/legal/privacy';
import terms from '../content/legal/terms';
import type { LegalDoc } from '../content/legal/types';
import { demoPosts } from '../content/posts';
import { seoMap } from '../content/seo-map';
import { serviceContent } from '../content/services';
import { aboutContent } from '../content/static/about';
import { contactContent } from '../content/static/contact';
import { homeContent } from '../content/static/home';
import { industriesHubContent } from '../content/static/industries-hub';
import { servicesHubContent } from '../content/static/services-hub';
import type { ServiceContent } from '../content/types';
import { brandLogos, budgets, coreServices, demoCaseStudies, industries, services, site, stats, testimonials } from '../lib/data';
import { groupIcons, industryIcons, serviceIcons } from '../lib/icons';
import { slugify } from '../lib/util';

const staticPath: Record<string, string> = { home: '/', about: '/about', contact: '/contact', 'services-hub': '/services', 'industries-hub': '/industries' };
const builtInPartners = [
  ['Google Partner', 'Google-Partner.png'], ['Meta Business Partner', 'Meta-1.png'], ['LinkedIn', 'LinkedIn.png'], ['TikTok Marketing Partner', 'TikTok-Partners.png'],
  ['Brevo Partner', 'Brevo.png'], ['Shopify Partner', 'Shopify.png'], ['Klaviyo Partner', 'Klaviyo.png'], ['Google Analytics', 'Google-Analytics.png'],
].map(([name, f]) => ({ name, logo: `/partners/${f}` }));

let sort = 0;
const page = (kind: 'page' | 'service' | 'industry', c: ServiceContent) => ({
  key: `${kind}~${c.slug}`, kind, slug: c.slug, name: c.short ?? c.slug, sort: sort++,
  path: kind === 'page' ? staticPath[c.slug] : `/${kind === 'service' ? 'services' : 'industries'}/${c.slug}`,
  metaTitle: c.metaTitle, metaDescription: c.metaDescription,
  hero: { ...c.hero, h1: c.hero.h1 ?? (kind === 'page' ? '' : `${c.hero.keyword ?? c.short ?? c.slug} Services of [[GTech Digital]]`) },
  sections: c.sections, faqs: c.faqs, related: c.related ?? [], data: {},
});
const legal = (slug: string, d: LegalDoc) => ({
  key: `legal~${slug}`, kind: 'legal', slug, name: d.title, sort: sort++, path: `/${slug}`, metaTitle: '', metaDescription: d.intro,
  hero: { h1: d.title, lead: d.intro, points: [], motion: '' },
  sections: d.sections.map((s) => ({ type: 'legal', id: slugify(s.h), heading: s.h, paras: s.p ?? [], bullets: s.ul ?? [], after: s.after ?? [] })),
  faqs: [], related: [], data: { updated: d.updated },
});

const out = {
  exportedAt: new Date().toISOString(),
  pages: [
    ...[homeContent, aboutContent, contactContent, servicesHubContent, industriesHubContent].map((c) => page('page', c)),
    ...Object.values(serviceContent).map((c) => page('service', c)),
    ...Object.values(industryContent).map((c) => page('industry', c)),
    legal('privacy-policy', privacy), legal('terms', terms), legal('cookie-policy', cookies),
  ],
  serviceGroups: services.map((g, i) => ({ slug: g.slug, title: g.title, intro: g.intro, icon: groupIcons[g.slug] ?? '', sort: i })),
  serviceItems: services.flatMap((g) => g.items.map((it, i) => ({ slug: it.slug, group: g.slug, name: it.name, blurb: it.blurb, icon: serviceIcons[it.slug] ?? '', sort: i }))),
  industries: industries.map((x, i) => ({ slug: x.slug, name: x.name, icon: industryIcons[x.slug] ?? '', sort: i })),
  coreServices: coreServices.map((c, i) => ({ ...c, sort: i })),
  stats: stats.map((s, i) => ({ value: s.value, suffix: s.suffix, label: s.label, decimals: (s as { decimals?: number }).decimals ?? 0, sort: i })),
  testimonials: testimonials.map((t, i) => ({ ...t, sort: i })),
  seoMap: Object.entries(seoMap).map(([slug, e]) => ({ slug, kw: e.kw, sec: e.sec, ent: e.ent, links: e.to.map(([target, anchor, why]) => ({ target, anchor, why })) })),
  settings: {
    general: { siteName: site.name, tagline: site.tagline, siteUrl: 'https://www.gtechdigital.co.uk' },
    contact: { email: site.email, phone: site.phone, address: '', hours: 'Mon to Fri, 9am to 6pm' },
    socials: site.socials.map((s) => ({ name: s.name, url: s.url })),
    forms: { budgets },
    company: { legalName: company.legalName, number: company.number, address: company.address, ico: company.ico },
  },
  demo: {
    posts: demoPosts, caseStudies: demoCaseStudies,
    partners: builtInPartners, clients: brandLogos,
  },
};
writeFileSync(new URL('../backend/database/data/content.json', import.meta.url), JSON.stringify(out, null, 1));
console.log(Object.fromEntries(Object.entries(out).filter(([, v]) => Array.isArray(v)).map(([k, v]) => [k, (v as unknown[]).length])), 'demo posts', out.demo.posts.length);
