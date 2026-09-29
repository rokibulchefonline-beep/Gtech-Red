import Icon from '@/components/Icon';
import { groupIcons, industryIcons, serviceIcons } from '@/lib/icons';
import Link from 'next/link';
import WhoWeAre from '@/components/WhoWeAre';
import TrustRibbon from '@/components/TrustRibbon';
import DocCards from '@/components/DocCards';
import { industries, services, site } from '@/lib/data';
import { listDocs } from '@/lib/mongo';

export const dynamic = 'force-dynamic';

const highlights: [string, string][] = [
  ['Digital Advertising', 'digital-advertising'], ['Branding', 'branding'],
  ['Search Engine Optimization', 'search-engine-optimization'], ['Website Design', 'website-design'],
  ['Paid Media', 'paid-media'], ['Marketing Advisory', 'marketing-advisory'],
  ['Social Media', 'social-media-marketing'], ['Conversion Rate Optimization', 'conversion-rate-optimization'],
];

export default async function Home() {
  const cases = await listDocs('case_studies', 3);
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

      <section className="dark">
        <div className="wrap">
          <h2>What we do</h2>
          <ul className="list2">
            {highlights.map(([n, s]) => <li key={s}><Link href={`/services/${s}`}>{n}</Link></li>)}
          </ul>
        </div>
      </section>

      <section className="wrap block">
        <h2>Our services</h2>
        <div className="cards">
          {services.map((g) => (
            <Link key={g.slug} className="card" href={`/services/${g.slug}`}>
              <Icon className="card-ico" name={groupIcons[g.slug]} size={28} />
              <h3>{g.title}</h3>
              <p>{g.intro}</p>
              <small>{g.items.slice(0, 3).map((i) => i.name).join(' · ')}</small>
            </Link>
          ))}
        </div>
      </section>

      <WhoWeAre />

      <section className="grad">
        <div className="wrap">
          <h2>Industries we serve</h2>
          <div className="chips">
            {industries.map((i) => <Link key={i.slug} href={`/industries/${i.slug}`}><Icon name={industryIcons[i.slug]} size={16} /> {i.name}</Link>)}
          </div>
        </div>
      </section>

      <section className="wrap block">
        <h2>Latest case studies</h2>
        <DocCards docs={cases} base="/case-studies" />
        <p><Link href="/case-studies">All case studies &rarr;</Link></p>
      </section>

      <section className="cta">
        <div className="wrap"><h2>Ready to grow?</h2><Link className="btn light" href="/contact">Contact us</Link></div>
      </section>
    </>
  );
}
