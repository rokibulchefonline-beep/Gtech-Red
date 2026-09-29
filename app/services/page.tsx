import type { Metadata } from 'next';
import Link from 'next/link';
import PageHead from '@/components/PageHead';
import { services } from '@/lib/data';

export const metadata: Metadata = { title: 'Our Services' };

export default function Services() {
  return (
    <>
      <PageHead title="Our Services" sub="Marketing, web and software under one roof." />
      <section className="wrap block">
        <div className="cards">
          {services.map((g) => (
            <Link key={g.slug} className="card" href={`/services/${g.slug}`}>
              <h3>{g.title}</h3>
              <p>{g.intro}</p>
              <small>{g.items.map((i) => i.name).join(' · ')}</small>
            </Link>
          ))}
        </div>
      </section>
    </>
  );
}
