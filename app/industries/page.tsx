import type { Metadata } from 'next';
import { seoFor } from '@/lib/seo';
import Link from 'next/link';
import Icon from '@/components/Icon';
import InquirySection from '@/components/InquirySection';
import { Tick } from '@/components/service/ServicePage';
import { industryContent } from '@/content/industries';
import { industries } from '@/lib/data';
import { industryIcons, uiIcons } from '@/lib/icons';
import Schema from '@/components/Schema';
import Hl from '@/components/Hl';
import { Rt } from '@/components/Rt';
import { getStaticPage } from '@/lib/content';
import { BASE, breadcrumbNode, itemListNode, pageNode } from '@/lib/schema';

const baseMeta: Metadata = {
  title: { absolute: 'Industries We Serve | Sector Marketing & Software | GTech Digital' },
  description: 'GTech Digital helps UK businesses in ecommerce, healthcare, hospitality, property, finance, education, travel, automotive, B2B and SaaS grow with tailored marketing, websites and software.',
  alternates: { canonical: '/industries' },
};
export const generateMetadata = async () => { const c = await getStaticPage('industries-hub'); return seoFor('/industries', { ...baseMeta, title: { absolute: c.metaTitle }, description: c.metaDescription }); };

const how = [
  { icon: 'lucide:search', title: 'Sector research', text: 'We study your market, buyers and competitors first.' },
  { icon: 'lucide:shield-check', title: 'Compliance-aware', text: 'Campaigns that respect the rules of your industry.' },
  { icon: 'lucide:trending-up', title: 'Tracked to revenue', text: 'Every channel measured against leads and sales.' },
];

export default async function Industries() {
  const c = await getStaticPage('industries-hub');
  return (
    <>
      <Schema path="/industries" nodes={[
        pageNode({ path: '/industries', type: 'CollectionPage', name: 'Industry Marketing Services of GTech Digital', description: c.metaDescription, mainEntity: `${BASE}/industries#list` }),
        breadcrumbNode('/industries', [['Industries', '/industries']]),
        itemListNode('/industries', 'Industries GTech Digital serves', industries.map((i) => [i.name, `/industries/${i.slug}`] as [string, string])),
      ]} />

      <section className="ih-hero">
        <div className="wrap ih-hero-in">
          <nav className="sp-crumbs left" aria-label="Breadcrumb"><Link href="/">Home</Link><span>/</span><b>Industries</b></nav>
          <h1><Hl>{c.hero.h1 ?? ''}</Hl></h1>
          <Rt as="p" className="ih-lead" html={c.hero.lead} />
          <ul className="ih-points">{c.hero.points.map((p) => <li key={p}><Tick />{p}</li>)}</ul>
          <div className="ih-btns"><Link className="sp-btn-red" href="/contact">Book a Free Audit</Link><a className="sp-btn-line light" href="#sectors">Explore Industries</a></div>
        </div>
      </section>

      <section className="ih-how"><div className="wrap ih-how-grid">
        {how.map((h) => <div key={h.title}><span className="ih-how-ico"><Icon name={h.icon} size={22} /></span><h3>{h.title}</h3><p>{h.text}</p></div>)}
      </div></section>

      <section id="sectors" className="ih-sec"><div className="wrap">
        <div className="ih-grid">
          {industries.map((i) => {
            const ic = industryContent[i.slug];
            return (
              <Link key={i.slug} href={`/industries/${i.slug}`} className="ih-card">
                <span className="ih-img">
                  {/* eslint-disable-next-line @next/next/no-img-element */}
                  <img src={`/pages/industries/${i.slug}/growth.webp`} alt={`${i.name} marketing results dashboard`} loading="lazy" width={800} height={600} />
                  <span className="ih-chip"><Icon name={industryIcons[i.slug]} size={18} /></span>
                </span>
                <span className="ih-body">
                  <h2>{i.name}</h2>
                  <p>{ic?.hero.lead.replace(/<[^>]+>/g, '')}</p>
                  <span className="ih-more">Explore {i.name} <Icon name={uiIcons.arrowRight} size={16} /></span>
                </span>
              </Link>
            );
          })}
        </div>
      </div></section>
      <InquirySection />
    </>
  );
}
