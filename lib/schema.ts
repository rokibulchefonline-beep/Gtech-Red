import { seoMap } from '@/content/seo-map';
import type { Settings } from '@/lib/settings';

// JSON-LD builders. Every page emits one connected @graph: Organization <- WebSite <- WebPage <- the
// page's main entity (Service, Article, ...), so search engines and AI assistants can follow one
// consistent entity: GTech Digital, its services, the sectors it serves and the evidence behind them.

export type JsonLd = Record<string, unknown>;
export const BASE = 'https://www.gtechdigital.co.uk';
export const BUILD_DATE = new Date().toISOString().slice(0, 10);
export const ids = { org: `${BASE}/#organization`, site: `${BASE}/#website` };
const abs = (p: string) => (p.startsWith('http') ? p : `${BASE}${p === '/' ? '/' : p}`);
export const pageId = (path: string) => `${abs(path)}#webpage`;
const ref = (id: string) => ({ '@id': id });

export function orgNode(s: Settings): JsonLd {
  const c = s.contact;
  return {
    '@type': ['Organization', 'ProfessionalService'], '@id': ids.org, name: s.general.siteName, url: BASE + '/', logo: { '@type': 'ImageObject', url: `${BASE}/logo.png` }, image: `${BASE}/logo.png`,
    description: 'GTech Digital is a UK digital agency providing digital marketing, SEO, Google Ads, social media marketing, web design and development, custom software development and branding services.',
    email: c.email, telephone: c.phone, priceRange: '££', areaServed: { '@type': 'Country', name: 'United Kingdom' }, slogan: s.general.tagline,
    ...(c.address ? { address: { '@type': 'PostalAddress', streetAddress: c.address, addressCountry: 'GB' } } : {}),
    contactPoint: { '@type': 'ContactPoint', contactType: 'sales', email: c.email, telephone: c.phone, areaServed: 'GB', availableLanguage: 'English' },
    sameAs: s.socials.map((x) => x.url).filter(Boolean),
    knowsAbout: ['Digital marketing', 'Search engine optimisation', 'Answer engine optimisation', 'Generative engine optimisation', 'Google Ads', 'Social media marketing', 'Web design', 'Web development', 'Custom software development', 'Branding'],
  };
}
export const siteNode = (s: Settings): JsonLd => ({ '@type': 'WebSite', '@id': ids.site, url: BASE + '/', name: s.general.siteName, description: s.general.tagline, inLanguage: 'en-GB', publisher: ref(ids.org) });

export function pageNode(o: { path: string; name: string; description: string; type?: string | string[]; mainEntity?: string; about?: string[]; image?: string; published?: string; modified?: string; speakable?: boolean; breadcrumb?: boolean }): JsonLd {
  return {
    '@type': o.type ?? 'WebPage', '@id': pageId(o.path), url: abs(o.path), name: o.name, description: o.description, inLanguage: 'en-GB',
    isPartOf: ref(ids.site), about: ref(ids.org), publisher: ref(ids.org),
    ...(o.breadcrumb !== false && o.path !== '/' ? { breadcrumb: ref(`${abs(o.path)}#breadcrumb`) } : {}),
    ...(o.mainEntity ? { mainEntity: ref(o.mainEntity) } : {}),
    ...(o.about?.length ? { mentions: o.about.map((n) => ({ '@type': 'Thing', name: n })) } : {}),
    ...(o.image ? { primaryImageOfPage: { '@type': 'ImageObject', url: abs(o.image) } } : {}),
    ...(o.published ? { datePublished: o.published } : {}), dateModified: o.modified ?? BUILD_DATE,
    ...(o.speakable === false ? {} : { speakable: { '@type': 'SpeakableSpecification', cssSelector: ['h1', '.sp-lead'] } }),
  };
}

export const breadcrumbNode = (path: string, items: [string, string][]): JsonLd => ({
  '@type': 'BreadcrumbList', '@id': `${abs(path)}#breadcrumb`,
  itemListElement: [['Home', '/'], ...items].map(([name, p], i) => ({ '@type': 'ListItem', position: i + 1, name, item: abs(p) })),
});

export function serviceNode(o: { path: string; slug: string; name: string; description: string; category?: string; audience?: string; related?: { name: string; path: string }[] }): JsonLd {
  const e = seoMap[o.slug];
  return {
    '@type': 'Service', '@id': `${abs(o.path)}#service`, name: o.name, serviceType: o.name, description: o.description, url: abs(o.path),
    provider: ref(ids.org), areaServed: { '@type': 'Country', name: 'United Kingdom' }, mainEntityOfPage: ref(pageId(o.path)),
    ...(o.category ? { category: o.category } : {}), ...(o.audience ? { audience: { '@type': 'BusinessAudience', name: o.audience } } : {}),
    ...(e ? { keywords: [e.kw, ...e.sec].join(', '), about: e.ent.map((n) => ({ '@type': 'Thing', name: n })) } : {}),
    ...(o.related?.length ? { isRelatedTo: o.related.map((r) => ({ '@type': 'Service', name: r.name, url: abs(r.path) })) } : {}),
    termsOfService: `${BASE}/terms`,
  };
}

export const faqNode = (path: string, faqs: { q: string; a: string }[]): JsonLd => ({
  '@type': 'FAQPage', '@id': `${abs(path)}#faq`, isPartOf: ref(pageId(path)), inLanguage: 'en-GB',
  mainEntity: faqs.map((f) => ({ '@type': 'Question', name: f.q, acceptedAnswer: { '@type': 'Answer', text: f.a } })),
});

export const itemListNode = (path: string, name: string, items: [string, string][]): JsonLd => ({
  '@type': 'ItemList', '@id': `${abs(path)}#list`, name, numberOfItems: items.length,
  itemListElement: items.map(([n, p], i) => ({ '@type': 'ListItem', position: i + 1, name: n, url: abs(p) })),
});

export function articleNode(o: { path: string; type?: string; headline: string; description: string; image?: string; published?: string; modified?: string; section?: string; keywords?: string[]; words?: number; author?: string }): JsonLd {
  return {
    '@type': o.type ?? 'Article', '@id': `${abs(o.path)}#article`, headline: o.headline, description: o.description, mainEntityOfPage: ref(pageId(o.path)), url: abs(o.path), inLanguage: 'en-GB',
    ...(o.image ? { image: abs(o.image) } : {}), ...(o.published ? { datePublished: o.published } : {}), dateModified: o.modified ?? o.published ?? BUILD_DATE,
    ...(o.section ? { articleSection: o.section } : {}), ...(o.keywords?.length ? { keywords: o.keywords.join(', ') } : {}), ...(o.words ? { wordCount: o.words } : {}),
    author: o.author ? { '@type': 'Organization', name: o.author, url: `${BASE}/about` } : ref(ids.org), publisher: ref(ids.org), isPartOf: ref(pageId(o.path)),
  };
}

/** Wraps nodes in one @graph with the shared Organization and WebSite nodes. */
export const buildGraph = (s: Settings, nodes: JsonLd[]): JsonLd => ({ '@context': 'https://schema.org', '@graph': [orgNode(s), siteNode(s), ...nodes] });

/** Safe to embed in a <script> tag. */
export const toScript = (v: unknown) => JSON.stringify(v).replace(/</g, '\\u003c');

/** Validates the custom JSON-LD admins can add per page. Returns an error string or null. */
export function validateCustomSchema(text: string): string | null {
  if (!text.trim()) return null;
  if (text.length > 20000) return 'The custom schema is too long (20,000 characters max).';
  let v: unknown;
  try { v = JSON.parse(text); } catch { return 'Custom schema is not valid JSON.'; }
  const items = Array.isArray(v) ? v : [v];
  if (!items.length || items.some((x) => !x || typeof x !== 'object')) return 'Custom schema must be a JSON object or an array of objects.';
  if (items.some((x) => !(x as JsonLd)['@type'] && !(x as JsonLd)['@graph'])) return 'Each custom schema object needs an @type (or an @graph).';
  return null;
}

/** Schema types each kind of page emits automatically (shown in Admin > SEO). */
export const autoSchemaTypes = (path: string) =>
  path === '/' ? ['Organization', 'ProfessionalService', 'WebSite', 'WebPage', 'ItemList']
  : path.startsWith('/services/') ? ['Organization', 'WebSite', 'WebPage', 'BreadcrumbList', 'Service', 'FAQPage']
  : path.startsWith('/industries/') ? ['Organization', 'WebSite', 'WebPage', 'BreadcrumbList', 'Service', 'FAQPage']
  : path.startsWith('/case-studies/') ? ['Organization', 'WebSite', 'WebPage', 'BreadcrumbList', 'Article']
  : path.startsWith('/blogs/') ? ['Organization', 'WebSite', 'WebPage', 'BreadcrumbList', 'BlogPosting']
  : path === '/about' ? ['Organization', 'WebSite', 'AboutPage', 'BreadcrumbList', 'FAQPage']
  : path === '/contact' ? ['Organization', 'WebSite', 'ContactPage', 'BreadcrumbList', 'FAQPage']
  : ['Organization', 'WebSite', 'CollectionPage', 'BreadcrumbList', 'ItemList'];
