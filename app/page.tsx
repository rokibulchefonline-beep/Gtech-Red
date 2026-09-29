import Link from 'next/link';
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
      <section className="hero">
        <div className="wrap">
          <h1>We grow brands with <span className="hl">digital</span>, web &amp; software</h1>
          <p>{site.tagline}. Strategy, design and engineering under one roof.</p>
          <Link className="btn" href="/contact">Get a free consultation</Link>
          <Link className="btn ghost" href="/case-studies">See our work</Link>
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
              <h3>{g.title}</h3>
              <p>{g.intro}</p>
              <small>{g.items.slice(0, 3).map((i) => i.name).join(' · ')}</small>
            </Link>
          ))}
        </div>
      </section>

      <section className="grad">
        <div className="wrap">
          <h2>Industries we serve</h2>
          <div className="chips">
            {industries.map((i) => <Link key={i.slug} href={`/industries/${i.slug}`}>{i.name}</Link>)}
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
