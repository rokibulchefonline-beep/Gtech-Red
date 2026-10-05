import Icon from '@/components/Icon';
import { groupIcons } from '@/lib/icons';
import Link from 'next/link';
import HeroVideo from '@/components/HeroVideo';
import OurServices from '@/components/OurServices';
import BrandGrid from '@/components/BrandGrid';
import WhoWeAre from '@/components/WhoWeAre';
import HowWeWork from '@/components/HowWeWork';
import InquirySection from '@/components/InquirySection';
import IndustriesSection from '@/components/IndustriesSection';
import PartnerStrip from '@/components/PartnerStrip';
import StatsBar from '@/components/StatsBar';
import Results from '@/components/Results';
import Testimonials from '@/components/Testimonials';
import CaseStudies from '@/components/CaseStudies';
import { seoFor } from '@/lib/seo';
import Schema from '@/components/Schema';
import { ids, itemListNode, pageNode } from '@/lib/schema';
import { services } from '@/lib/data';
import { getStaticPage, homeSection } from '@/lib/content';
import Hl from '@/components/Hl';
import { Rt } from '@/components/Rt';
import { homeContent } from '@/content/static/home';


export const generateMetadata = async () => { const c = await getStaticPage('home'); return seoFor('/', { title: { absolute: c.metaTitle }, description: c.metaDescription }); };

export default async function Home() {
  const c = await getStaticPage('home');
  const lines = (c.hero.h1 ?? homeContent.hero.h1!).split('|');
  return (
    <>
      <Schema path="/" nodes={[
        pageNode({ path: '/', name: c.metaTitle, description: c.metaDescription, mainEntity: ids.org }),
        itemListNode('/', 'GTech Digital services', services.map((g) => [g.title, `/services/${g.slug}`] as [string, string])),
      ]} />
      <section className="hero-video">
        <HeroVideo />
        <div className="wrap">
          <div className="hero-head">
            <h1 className="hero-title">
              {lines.map((l, i) => <span key={i} className={i === lines.length - 1 && i > 0 ? 'hero-last' : undefined}>{l.includes('[[') ? <Hl>{l}</Hl> : l.trim() || ' '}</span>)}
            </h1>
            <Rt as="p" className="hero-sub" html={c.hero.lead} />
          </div>
          <div className="hero-ctas">
            <Link className="btn-red" href="/contact">Let&apos;s Talk</Link>
            <Link className="btn-outline" href="/services">Our Services</Link>
          </div>
        </div>
      </section>

      <PartnerStrip />
      <StatsBar />

      <WhoWeAre />
      <OurServices />
      <BrandGrid />
      <HowWeWork head={await homeSection('how')} steps={(await homeSection('how-steps')).steps} />
      <CaseStudies />
      <Results />
      <Testimonials />

      <IndustriesSection />

      <InquirySection />
    </>
  );
}
