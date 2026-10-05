import Link from 'next/link';
import SemanticLinks from '@/components/SemanticLinks';
import TocBar from '@/components/TocBar';
import Icon from '@/components/Icon';
import InquirySection from '@/components/InquirySection';
import FaqSection from '@/components/service/FaqSection';
import { Block, Head, Tick } from '@/components/service/ServicePage';
import type { ServiceContent } from '@/content/types';
import { findItem, industries, site } from '@/lib/data';
import { industryIcons, serviceIcons, uiIcons } from '@/lib/icons';
import Schema from '@/components/Schema';
import { seoMap } from '@/content/seo-map';
import { semanticLinksFor } from '@/lib/link-graph';
import { BASE, breadcrumbNode, faqNode, pageNode, serviceNode } from '@/lib/schema';

// Long-form industry page. Uses the same section blocks as the service pages.
export default function IndustryPage({ c }: { c: ServiceContent }) {
  const ind = industries.find((i) => i.slug === c.slug);
  const name = ind?.name ?? '';
  const related = c.related.map((r) => findItem(r)).filter(Boolean);
  const others = industries.filter((i) => i.slug !== c.slug);
  const path = `/industries/${c.slug}`;
  const nodes = [
    pageNode({ path, name: c.metaTitle.split('|')[0].trim(), description: c.metaDescription, mainEntity: `${BASE}${path}#service`, about: seoMap[c.slug]?.ent, image: c.hero.motion }),
    breadcrumbNode(path, [['Industries', '/industries'], [name, path]]),
    serviceNode({ path, slug: c.slug, name: c.metaTitle.split('|')[0].trim(), description: c.metaDescription, category: 'Industry marketing', audience: `${name} businesses`, related: [...related.map((r) => ({ name: r!.item.name, path: `/services/${r!.item.slug}` })), ...semanticLinksFor(c.slug).filter((l) => l.group !== 'page').map((l) => ({ name: l.anchor, path: l.href }))] }),
    faqNode(path, c.faqs),
  ];

  return (
    <>
      <Schema path={path} nodes={nodes} />

      <section className="sp-hero">
        <div className="wrap sp-hero-in">
          <nav className="sp-crumbs" aria-label="Breadcrumb">
            <Link href="/">Home</Link><span>/</span><Link href="/industries">Industries</Link><span>/</span><b>{name}</b>
          </nav>
          <h1>{c.hero.keyword ?? name} Services of <span className="red">GTech Digital</span></h1>
          <p className="sp-lead">{c.hero.lead}</p>
          <div className="sp-hero-btns">
            <Link className="sp-btn-red" href={`/contact?service=${encodeURIComponent(name)}`}>Book a Free Audit</Link>
            <a className="sp-btn-line" href="#services">See What We Do</a>
          </div>
          <ul className="sp-hero-points">{c.hero.points.map((p) => <li key={p}><Tick />{p}</li>)}</ul>
          <p className="sp-updated">Reviewed by GTech Digital specialists · Updated {new Date().toLocaleDateString('en-GB', { month: 'long', year: 'numeric' })}</p>
          <div className="sp-hero-show">
            {/* eslint-disable-next-line @next/next/no-img-element */}
            <img src={c.hero.motion} alt={`${name} marketing results dashboard`} width={800} height={600} />
            <span className="sp-float a"><Icon name={industryIcons[c.slug]} size={20} />{name}</span>
            <span className="sp-float b"><Icon name="lucide:trending-up" size={20} />Revenue-focused</span>
          </div>
        </div>
      </section>

      <TocBar label="On this page" items={[...c.sections.filter((s) => 'nav' in s && s.nav).map((s) => ({ id: s.id, label: 'nav' in s ? s.nav ?? '' : '' })), { id: 'faq', label: 'FAQs' }]} />

      {c.sections.map((s) => <Block key={s.id} s={s} slug={c.slug} name={name} />)}

      <FaqSection title={`Frequently Asked Questions About ${c.short}`} faqs={c.faqs} />

      <SemanticLinks slug={c.slug} name={c.short ?? name} />

      <section className="sp-sec sp-grey"><div className="wrap">
        <Head s={{ heading: `Recommended Services for ${name} Businesses` }} />
        <div className="sp-related">
          {related.map((r) => r && (
            <Link key={r.item.slug} href={`/services/${r.item.slug}`} className="sp-rel">
              <span className="sp-card-ico solid"><Icon name={serviceIcons[r.item.slug]} size={22} /></span>
              <h3>{r.item.name}</h3><p>{r.item.blurb}</p><span className="sp-more">Explore <Icon name={uiIcons.arrowRight} size={16} /></span>
            </Link>
          ))}
        </div>
        <div className="ind-others">
          <p>Other industries we serve:</p>
          {others.map((o) => <Link key={o.slug} href={`/industries/${o.slug}`}><Icon name={industryIcons[o.slug]} size={16} />{o.name}</Link>)}
        </div>
      </div></section>

      <InquirySection />
    </>
  );
}
