import { notFound } from 'next/navigation';
import PageEditor, { type PageBase } from '@/components/admin/PageEditor';
import { industryContent } from '@/content/industries';
import { seoMap } from '@/content/seo-map';
import { serviceContent } from '@/content/services';
import { aboutContent } from '@/content/static/about';
import { contactContent } from '@/content/static/contact';
import { homeContent } from '@/content/static/home';
import { industriesHubContent } from '@/content/static/industries-hub';
import { servicesHubContent } from '@/content/static/services-hub';
import { buildGraph, linkStats, semanticLinksFor } from '@/lib/link-graph';

export default async function PageEdit({ params }: { params: Promise<{ kind: string; slug: string }> }) {
  const { kind, slug } = await params;
  const pages = { home: homeContent, about: aboutContent, contact: contactContent, 'services-hub': servicesHubContent, 'industries-hub': industriesHubContent } as Record<string, typeof homeContent>;
  const c = (kind === 'service' ? serviceContent : kind === 'industry' ? industryContent : kind === 'page' ? pages : {})[slug];
  if (!c) notFound();
  const base: PageBase = {
    kind: kind as 'service' | 'industry' | 'page', slug, name: c.short ?? c.slug, metaTitle: c.metaTitle, metaDescription: c.metaDescription,
    hero: { keyword: c.hero.keyword ?? c.short ?? slug, h1: c.hero.h1 ?? `${c.hero.keyword ?? c.short ?? slug} Services of [[GTech Digital]]`,  lead: c.hero.lead, points: c.hero.points },
    sections: c.sections.map((s) => {
      const x = s as unknown as Record<string, unknown>;
      const items: Record<string, unknown> = {};
      for (const f of ['cards', 'steps', 'stats', 'reviews', 'items']) if (Array.isArray(x[f]) && (x[f] as unknown[]).length) items[f] = x[f];
      return { id: s.id, type: s.type, nav: x.nav as string | undefined, heading: x.heading as string | undefined, intro: x.intro as string | undefined, paras: x.paras as string[] | undefined, bullets: x.bullets as string[] | undefined, text: x.text as string | undefined, ...items };
    }),
    faqs: c.faqs,
  };
  const path = kind === 'page' ? (({ home: '/', 'services-hub': '/services', 'industries-hub': '/industries' } as Record<string, string>)[slug] ?? `/${slug}`) : `/${kind === 'service' ? 'services' : 'industries'}/${slug}`;
  const g = buildGraph(), st = linkStats(g)[path];
  const m = kind === 'page' ? undefined : seoMap[slug];
  const info = {
    kw: m?.kw ?? ({ home: 'digital marketing agency uk', about: 'about gtech digital', contact: 'contact gtech digital', 'services-hub': 'gtech digital services', 'industries-hub': 'industry marketing services' } as Record<string, string>)[slug] ?? base.name, sec: m?.sec ?? [], ent: m?.ent ?? [], linksIn: st?.contextualIn ?? 0, linksOut: st?.contextualOut ?? 0,
    anchorsIn: (st?.inbound ?? []).filter((e) => ['related', 'semantic', 'industries', 'sector-services'].includes(e.type)).map((e) => ({ from: e.from, anchor: e.anchor })),
    out: [...(st?.outbound ?? []).filter((e) => ['related', 'semantic', 'industries', 'sector-services'].includes(e.type)).map((e) => ({ href: e.to, anchor: e.anchor })), ...semanticLinksFor(slug).filter(() => false)],
  };
  return <PageEditor base={base} info={info} path={path} />;
}
