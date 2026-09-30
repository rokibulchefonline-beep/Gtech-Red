import Icon from '@/components/Icon';
import { groupIcons, industryIcons, serviceIcons } from '@/lib/icons';
import Link from 'next/link';
import OurServices from '@/components/OurServices';
import BrandGrid from '@/components/BrandGrid';
import WhoWeAre from '@/components/WhoWeAre';
import TrustRibbon from '@/components/TrustRibbon';
import CaseStudies from '@/components/CaseStudies';
import { industries, site } from '@/lib/data';

export const dynamic = 'force-dynamic';

export default async function Home() {
  return (
    <>
      <section className="hero-video">
        <video autoPlay muted loop playsInline preload="metadata" aria-hidden="true">
          <source src={process.env.NEXT_PUBLIC_HERO_VIDEO_URL || '/videos/hero.mp4'} type="video/mp4" />
        </video>
        <div className="wrap">
          <h1 className="hero-title">
            <span>Digital Marketing</span>
            <span>Agency for Scalable</span>
            <span className="hero-row">
              <span>Growth</span>
              <span className="hero-sub">We help businesses grow with smart, conversion-focused marketing.</span>
            </span>
          </h1>
          <div className="hero-ctas">
            <Link className="btn-red" href="/contact">Let&apos;s Talk</Link>
            <Link className="btn-outline" href="/services">Our Services</Link>
          </div>
        </div>
      </section>

      <TrustRibbon />

      <WhoWeAre />
      <BrandGrid />
      <OurServices />
      <CaseStudies />

      <section className="grad">
        <div className="wrap">
          <h2>Industries we serve</h2>
          <div className="chips">
            {industries.map((i) => <Link key={i.slug} href={`/industries/${i.slug}`}><Icon name={industryIcons[i.slug]} size={16} /> {i.name}</Link>)}
          </div>
        </div>
      </section>

      <section className="cta">
        <div className="wrap"><h2>Ready to grow?</h2><Link className="btn light" href="/contact">Contact us</Link></div>
      </section>
    </>
  );
}
