import type { Metadata } from 'next';
import Link from 'next/link';
import { notFound } from 'next/navigation';
import PageHead from '@/components/PageHead';
import { findGroup, findItem, services } from '@/lib/data';

type Props = { params: Promise<{ slug: string }> };

export const dynamicParams = false;

export function generateStaticParams() {
  return [
    ...services.map((g) => ({ slug: g.slug })),
    ...services.flatMap((g) => g.items.map((i) => ({ slug: i.slug }))),
  ];
}

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const { slug } = await params;
  const title = findGroup(slug)?.title ?? findItem(slug)?.item.name;
  return { title, description: findGroup(slug)?.intro ?? findItem(slug)?.item.blurb };
}

export default async function ServicePage({ params }: Props) {
  const { slug } = await params;

  const group = findGroup(slug);
  if (group) {
    return (
      <>
        <PageHead title={group.title} sub={group.intro} />
        <section className="wrap block">
          <div className="cards">
            {group.items.map((i) => (
              <Link key={i.slug} className="card" href={`/services/${i.slug}`}><h3>{i.name}</h3><p>{i.blurb}</p></Link>
            ))}
          </div>
        </section>
        <section className="cta">
          <div className="wrap"><h2>Talk to us about {group.title}</h2><Link className="btn light" href="/contact">Contact us</Link></div>
        </section>
      </>
    );
  }

  const found = findItem(slug);
  if (!found) notFound();
  const { group: g, item } = found;
  return (
    <>
      <PageHead title={item.name} sub={item.blurb} back={{ href: `/services/${g.slug}`, label: g.title }} />
      <section className="wrap block prose">
        <h2>How we help</h2>
        <p>Replace with detailed copy for {item.name}: approach, deliverables, tools, pricing model.</p>
        <h2>Related services</h2>
        <div className="chips dark-chips">
          {g.items.filter((i) => i.slug !== item.slug).map((i) => <Link key={i.slug} href={`/services/${i.slug}`}>{i.name}</Link>)}
        </div>
        <p><Link className="btn" href={`/contact?service=${encodeURIComponent(item.name)}`}>Get a quote</Link></p>
      </section>
    </>
  );
}
