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


export const generateMetadata = () => seoFor('/');

export default async function Home() {
  return (
    <>
      <Schema path="/" nodes={[
        pageNode({ path: '/', name: 'GTech Digital | Digital Marketing Agency UK', description: 'GTech Digital is a UK digital marketing agency growing businesses with SEO, Google Ads, social media, web design and custom software.', mainEntity: ids.org }),
        itemListNode('/', 'GTech Digital services', services.map((g) => [g.title, `/services/${g.slug}`] as [string, string])),
      ]} />
      <section className="hero-video">
        <HeroVideo />
        <div className="wrap">
          <div className="hero-head">
            <h1 className="hero-title">
              <span>Digital Marketing</span>
              <span>Agency for Scalable</span>
              <span className="hero-last">Growth</span>
            </h1>
            <p className="hero-sub">GTech Digital helps UK businesses grow with smart, conversion-focused marketing.</p>
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
      <HowWeWork />
      <CaseStudies />
      <Results />
      <Testimonials />

      <IndustriesSection />

      <InquirySection />
    </>
  );
}
