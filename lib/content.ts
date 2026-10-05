import type { ServiceContent } from '@/content/types';
import { brandLogos } from '@/lib/data';
import { cache } from 'react';
import { aboutContent } from '@/content/static/about';
import { contactContent } from '@/content/static/contact';
import { homeContent } from '@/content/static/home';
import { industriesHubContent } from '@/content/static/industries-hub';
import { servicesHubContent } from '@/content/static/services-hub';
import { findOne, list } from '@/lib/store';

// Public-site getters for content managed in the admin. Each falls back to the built-in defaults
// when the database is empty or unreachable, so the site always renders.
type Logo = { name: string; logo: string; url?: string };

async function logos(coll: 'partners' | 'clients', fallback: Logo[]): Promise<Logo[]> {
  try {
    const rows = await list(coll, { sort: { order: 1 }, limit: 100 });
    const shown = rows.filter((r) => r.visible !== false && r.logo).map((r) => ({ name: r.name, logo: r.logo, url: r.url }));
    return rows.length ? shown : fallback;
  } catch { return fallback; }
}
export const builtInPartners: Logo[] = [
  ['Google Partner', 'Google-Partner.png'], ['Meta Business Partner', 'Meta-1.png'], ['LinkedIn', 'LinkedIn.png'], ['TikTok Marketing Partner', 'TikTok-Partners.png'],
  ['Brevo Partner', 'Brevo.png'], ['Shopify Partner', 'Shopify.png'], ['Klaviyo Partner', 'Klaviyo.png'], ['Google Analytics', 'Google-Analytics.png'],
].map(([name, f]) => ({ name, logo: `/partners/${f}` }));
export const getPartners = () => logos('partners', builtInPartners);
export const getClients = () => logos('clients', brandLogos);

/** Apply admin edits (Admin > Pages) on top of a service or industry page definition. */
export async function withOverrides(kind: 'service' | 'industry' | 'page', c: ServiceContent): Promise<ServiceContent> {
  let o;
  try { o = await findOne('page_content', { _id: `${kind}~${c.slug}` }); } catch { return c; }
  if (!o) return c;
  return {
    ...c,
    metaTitle: o.metaTitle || c.metaTitle,
    metaDescription: o.metaDescription || c.metaDescription,
    hero: {
      ...c.hero,
      ...(o.hero?.h1 && { h1: o.hero.h1 }),
      ...(o.hero?.keyword && { keyword: o.hero.keyword }),
      ...(o.hero?.lead && { lead: o.hero.lead }),
      ...(o.hero?.points?.length && { points: o.hero.points }),
    },
    sections: c.sections.map((s) => {
      const e = o.sections?.[s.id];
      if (!e) return s;
      const x: Record<string, unknown> = { ...s };
      if (e.heading) x.heading = e.heading;
      if (e.intro && 'intro' in s) x.intro = e.intro;
      if (e.paras?.length && 'paras' in s) x.paras = e.paras;
      if (e.bullets?.length && 'bullets' in s) x.bullets = e.bullets;
      if (e.text && 'text' in s) x.text = e.text;
      for (const f of ['cards', 'steps', 'stats', 'reviews', 'items'] as const) {
        const base = (s as Record<string, unknown>)[f];
        if (Array.isArray(base) && Array.isArray(e[f])) x[f] = base.map((b, i) => ({ ...b, ...(e[f][i] ?? {}) }));
      }
      return x as typeof s;
    }),
    faqs: o.faqs?.length ? o.faqs : c.faqs,
  };
}

export const getStaticPage = cache((slug: 'home' | 'about' | 'contact' | 'services-hub' | 'industries-hub') => withOverrides('page', { home: homeContent, about: aboutContent, contact: contactContent, 'services-hub': servicesHubContent, 'industries-hub': industriesHubContent }[slug]));
/** Section of a static page by id, with admin edits applied. */
export async function homeSection<T = Record<string, unknown>>(id: string): Promise<T & { heading: string; paras?: string[]; bullets?: string[]; text?: string; steps?: { title: string; text: string }[] }> {
  const c = await getStaticPage('home');
  return c.sections.find((x) => x.id === id) as never;
}
