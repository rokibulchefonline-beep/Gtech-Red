import type { Metadata } from 'next';
import { seoFor } from '@/lib/seo';
import Link from 'next/link';
import Icon from '@/components/Icon';
import InquirySection from '@/components/InquirySection';
import FaqSection from '@/components/service/FaqSection';
import { Block, Tick } from '@/components/service/ServicePage';
import { services } from '@/lib/data';
import { groupIcons, serviceIcons, uiIcons } from '@/lib/icons';
import TocBar from '@/components/TocBar';
import Hl from '@/components/Hl';
import { Rt } from '@/components/Rt';
import { getStaticPage } from '@/lib/content';
import { groupInfo as baseGroups } from '@/content/static/services-hub';
import Schema from '@/components/Schema';
import { BASE, breadcrumbNode, faqNode, itemListNode, pageNode } from '@/lib/schema';

// Services hub. Targets the brand + "services" query (GTech Digital services), not the
// home page's main agency keyword.
const base = 'https://www.gtechdigital.co.uk';

const baseMeta: Metadata = {
  title: { absolute: 'GTech Digital Services | Marketing, Web & Software Solutions' },
  description: 'Explore every GTech Digital service: SEO, Google Ads, social media, web design, custom software and branding, delivered by one UK team.',
  alternates: { canonical: '/services' },
};
export const generateMetadata = async () => { const c = await getStaticPage('services-hub'); return seoFor('/services', { ...baseMeta, title: { absolute: c.metaTitle }, description: c.metaDescription }); };

// Every service, so a category grid can include closely related services from other categories.
const allItems = Object.fromEntries(services.flatMap((g) => g.items.map((it) => [it.slug, it])));

export default async function ServicesHub() {
  const c = await getStaticPage('services-hub');
  const faqs = c.faqs;
  const sec = (id: string) => c.sections.find((x) => x.id === id) as unknown as { heading: string; paras?: string[]; bullets?: string[]; cards?: { icon: string; title: string; text: string }[]; steps?: { title: string; text: string }[] };
  const groupInfo: Record<string, (typeof baseGroups)[string]> = Object.fromEntries(Object.entries(baseGroups).map(([k, g]) => { const e = sec(`group-${k}`); return [k, { ...g, h2: e.heading, line: e.paras?.[0] ?? g.line, points: e.bullets ?? g.points }]; }));
  const why = sec('why'), proc = sec('process');
  const total = services.reduce((n, g) => n + g.items.length, 0);
  const nodes = [
    pageNode({ path: '/services', type: 'CollectionPage', name: 'GTech Digital Services', description: c.metaDescription, mainEntity: `${BASE}/services#list`, about: ['Digital marketing', 'Web design and development', 'Custom software development', 'Branding'] }),
    breadcrumbNode('/services', [['Services', '/services']]),
    itemListNode('/services', 'GTech Digital services', services.flatMap((g) => [[g.title, `/services/${g.slug}`] as [string, string], ...g.items.map((it) => [it.name, `/services/${it.slug}`] as [string, string])])),
    faqNode('/services', faqs),
  ];

  return (
    <>
      <Schema path="/services" nodes={nodes} />

      <section className="sp-hero">
        <div className="wrap sp-hero-in">
          <nav className="sp-crumbs" aria-label="Breadcrumb"><Link href="/">Home</Link><span>/</span><b>Services</b></nav>
          <h1><Hl>{c.hero.h1 ?? ''}</Hl></h1>
          <Rt as="p" className="sp-lead" html={c.hero.lead} />
          <div className="sp-hero-btns">
            <Link className="sp-btn-red" href="/contact">Book a Free Audit</Link>
            <a className="sp-btn-line" href="#digital-marketing">Explore Services</a>
          </div>
          <ul className="sp-hero-points">{c.hero.points.map((p) => <li key={p}><Tick />{p}</li>)}</ul>
          <div className="sp-hero-show">
            {/* eslint-disable-next-line @next/next/no-img-element */}
            <img src="/services/hub.webp" alt="GTech Digital results across marketing, web and software" width={800} height={600} />
            <span className="sp-float a"><Icon name="lucide:layout-grid" size={20} />All Services</span>
            <span className="sp-float b"><Icon name="lucide:trending-up" size={20} />Revenue-focused</span>
          </div>
        </div>
      </section>

      <TocBar className="sh-tabs" label="Service categories" items={services.map((g) => ({ id: g.slug, label: groupInfo[g.slug]?.title ?? g.title, icon: groupIcons[g.slug] }))} />

      {services.map((g, n) => {
        const info = groupInfo[g.slug];
        const title = info?.title ?? g.title;
        const shown = (info?.cards ?? g.items.map((it) => it.slug)).map((slug) => allItems[slug]).filter(Boolean).slice(0, 6);
        return (
          <section key={g.slug} id={g.slug} className={`sz ${n % 2 ? 'flip alt' : ''}`}><div className="wrap"><div className="sz-in">
              <div className="sz-media">
                {/* eslint-disable-next-line @next/next/no-img-element */}
                <img src={info?.image} alt={`${title} results dashboard`} loading="lazy" width={800} height={600} />
              </div>
              <div className="sz-copy">
                <span className="sz-ico"><Icon name={groupIcons[g.slug]} size={24} /></span>
                <h2><Hl>{info?.h2 ?? `${title} Services`}</Hl></h2>
                <Rt as="p" html={info?.line ?? g.intro} />
                <ul className="sz-points">{info?.points.map((p) => <li key={p}><Tick /><Rt html={p} /></li>)}</ul>
                <div className="sz-btns">
                  <Link className="sz-btn" href={`/services/${g.slug}`} aria-label={`View all ${title} services`}>View All {g.items.length} Services <Icon name={uiIcons.arrowRight} size={18} /></Link>
                </div>
              </div>
            </div>
            <div className="sz-grid">
              {shown.map((it) => (
                <Link key={it.slug} href={`/services/${it.slug}`} className="sz-card">
                  <span className="sz-card-ico"><Icon name={serviceIcons[it.slug]} size={22} /></span>
                  <h3>{it.name}</h3>
                  <p>{it.blurb}</p>
                  <span className="sz-card-more">Learn more <Icon name={uiIcons.arrowRight} size={16} /></span>
                </Link>
              ))}
            </div>
          </div></section>
        );
      })}

      <section className="sh-why"><div className="wrap">
        <div className="sp-head center"><h2><Hl>{why.heading}</Hl></h2></div>
        <div className="sh-why-grid">
          {(why.cards ?? []).map((w) => <div key={w.title} className="sh-why-card"><span className="sp-card-ico solid"><Icon name={w.icon} size={22} /></span><h3>{w.title}</h3><p>{w.text}</p></div>)}
        </div>
      </div></section>

      <Block slug="services" name="GTech Digital" s={{
        type: 'steps', id: 'process', heading: proc.heading,
        steps: proc.steps ?? [],
      }} />

      <FaqSection title="Frequently Asked Questions About Our Services" faqs={faqs} />
      <InquirySection />
    </>
  );
}
