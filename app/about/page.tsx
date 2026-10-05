import type { Metadata } from 'next';
import { seoFor } from '@/lib/seo';
import Link from 'next/link';
import CaseCarousel from '@/components/CaseCarousel';
import InquirySection from '@/components/InquirySection';
import IntroVideo from '@/components/IntroVideo';
import PartnerStrip from '@/components/PartnerStrip';
import FaqSection from '@/components/service/FaqSection';
import { Block, Head, Tick } from '@/components/service/ServicePage';
import Hl from '@/components/Hl';
import { Rt } from '@/components/Rt';
import { getStaticPage } from '@/lib/content';
import { site } from '@/lib/data';
import { listDocs } from '@/lib/mongo';
import Schema from '@/components/Schema';
import { breadcrumbNode, faqNode, ids, pageNode } from '@/lib/schema';


// Entity map: GTech Digital (Organization) -> UK digital marketing, web design and software agency;
// services (SEO, Google Ads, social media, web development, custom software, branding); platform
// partnerships (Google Partner, Meta, Shopify, HubSpot); team, values, process, industries, clients.

const base = 'https://www.gtechdigital.co.uk';

const baseMeta: Metadata = {
  title: { absolute: 'About GTech Digital | UK Digital Marketing, Web & Software Agency' },
  description:
    'Meet GTech Digital, a UK agency combining digital marketing, web design and custom software under one roof, with certified specialists and a focus on measurable growth.',
  alternates: { canonical: '/about' },
};
export const generateMetadata = async () => { const c = await getStaticPage('about'); return seoFor('/about', { ...baseMeta, title: { absolute: c.metaTitle }, description: c.metaDescription }); };

export default async function About() {
  const c = await getStaticPage('about');
  const cut = c.sections.findIndex((x) => x.id === 'reviews');
  const sections = c.sections.slice(0, cut), later = c.sections.slice(cut), faqs = c.faqs;
  const cases = await listDocs('case_studies', 6);
  const nodes = [
    pageNode({ path: '/about', type: 'AboutPage', name: 'About GTech Digital', description: c.metaDescription, mainEntity: ids.org, about: ['Digital marketing agency', 'Web design', 'Custom software'] }),
    breadcrumbNode('/about', [['About Us', '/about']]),
    faqNode('/about', faqs),
  ];

  return (
    <>
      <Schema path="/about" nodes={nodes} />

      <section className="sp-hero">
        <div className="wrap sp-hero-in">
          <nav className="sp-crumbs" aria-label="Breadcrumb"><Link href="/">Home</Link><span>/</span><b>About Us</b></nav>
          <h1><Hl>{c.hero.h1 ?? ''}</Hl></h1>
          <Rt as="p" className="sp-lead" html={c.hero.lead} />
          <div className="sp-hero-btns">
            <Link className="sp-btn-red" href="/contact">Work With Us</Link>
            <Link className="sp-btn-line" href="/case-studies">See Our Work</Link>
          </div>
          <ul className="sp-hero-points">{c.hero.points.map((p) => <li key={p}><Tick />{p}</li>)}</ul>
          <div className="sp-hero-show about-video"><IntroVideo /></div>
        </div>
      </section>

      <div className="sp-after-hero"><PartnerStrip /></div>

      {sections.map((s) => <Block key={s.id} s={s} slug="about" name="GTech Digital" />)}

      {cases.length > 0 && (
        <section id="case-studies" className="sp-sec cases"><div className="wrap">
          <Head s={{ heading: 'Case Studies and Client Results' }} />
          <CaseCarousel docs={cases} />
          <p className="cases-all"><Link className="btn-dark" href="/case-studies">View All Case Studies</Link></p>
        </div></section>
      )}

      {later.map((s) => <Block key={s.id} s={s} slug="about" name="GTech Digital" />)}

      <FaqSection title="Frequently Asked Questions About GTech Digital" faqs={faqs} />
      <InquirySection />
    </>
  );
}
