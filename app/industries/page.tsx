import type { Metadata } from 'next';
import Link from 'next/link';
import Icon from '@/components/Icon';
import InquirySection from '@/components/InquirySection';
import { Tick } from '@/components/service/ServicePage';
import { industryContent } from '@/content/industries';
import { industries } from '@/lib/data';
import { industryIcons, uiIcons } from '@/lib/icons';

export const metadata: Metadata = {
  title: { absolute: 'Industries We Serve | Sector Marketing & Software | GTech Digital' },
  description: 'GTech Digital helps UK businesses in ecommerce, healthcare, hospitality, property, finance, education, travel, automotive, B2B and SaaS grow with tailored marketing, websites and software.',
  alternates: { canonical: '/industries' },
};

export default function Industries() {
  return (
    <>
      <section className="sp-hero compact">
        <div className="wrap sp-hero-in">
          <nav className="sp-crumbs" aria-label="Breadcrumb"><Link href="/">Home</Link><span>/</span><b>Industries</b></nav>
          <p className="sp-hero-eyebrow">Industries we serve</p>
          <h1>Marketing Built for <span className="red">Your Industry</span></h1>
          <p className="sp-lead">Every sector has its own customers, rules and buying journeys. We tailor marketing, websites and software to the way your industry works.</p>
          <ul className="sp-hero-points">{['Sector-specific strategy', 'Compliance-aware campaigns', 'Results tracked to revenue'].map((p) => <li key={p}><Tick />{p}</li>)}</ul>
        </div>
      </section>
      <section className="sp-sec"><div className="wrap">
        <div className="ih-grid">
          {industries.map((i) => {
            const c = industryContent[i.slug];
            return (
              <Link key={i.slug} href={`/industries/${i.slug}`} className="ih-card">
                <span className="sh-item-ico"><Icon name={industryIcons[i.slug]} size={22} /></span>
                <h2>{i.name}</h2>
                <p>{c?.hero.lead}</p>
                <span className="ih-more">Explore {i.name} <Icon name={uiIcons.arrowRight} size={16} /></span>
              </Link>
            );
          })}
        </div>
      </div></section>
      <InquirySection />
    </>
  );
}
