import Icon from '@/components/Icon';
import { groupIcons, industryIcons, serviceIcons } from '@/lib/icons';
import type { Metadata } from 'next';
import Link from 'next/link';
import { notFound } from 'next/navigation';
import PageHead from '@/components/PageHead';
import LongServicePage from '@/components/service/ServicePage';
import { serviceContent } from '@/content/services';
import { withOverrides } from '@/lib/content';
import { findGroup, findItem, services } from '@/lib/data';
import { seoFor } from '@/lib/seo';

type Props = { params: Promise<{ slug: string }> };

// Pre-rendered at build time and served as static HTML (no per-request rendering).
export const dynamicParams = false;

export function generateStaticParams() {
  return [
    ...services.map((g) => ({ slug: g.slug })),
    ...services.flatMap((g) => g.items.map((i) => ({ slug: i.slug }))),
  ];
}

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const { slug } = await params;
  const base = serviceContent[slug];
  if (base) {
    const rich = await withOverrides('service', base);
    return seoFor(`/services/${slug}`, { title: { absolute: rich.metaTitle }, description: rich.metaDescription, alternates: { canonical: `/services/${slug}` } });
  }
  const title = findGroup(slug)?.title ?? findItem(slug)?.item.name;
  return { title, description: findGroup(slug)?.intro ?? findItem(slug)?.item.blurb };
}

export default async function ServicePage({ params }: Props) {
  const { slug } = await params;

  // Long-form content (service or category page) wins over the simple layouts below.
  if (serviceContent[slug]) return <LongServicePage c={await withOverrides('service', serviceContent[slug])} />;

  const group = findGroup(slug);
  if (group) {
    return (
      <>
        <PageHead title={group.title} sub={group.intro} />
        <section className="wrap block">
          <div className="cards">
            {group.items.map((i) => (
              <Link key={i.slug} className="card" href={`/services/${i.slug}`}><Icon className="card-ico" name={serviceIcons[i.slug]} size={28} /><h3>{i.name}</h3><p>{i.blurb}</p></Link>
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
      <PageHead icon={serviceIcons[item.slug]} title={item.name} sub={item.blurb} back={{ href: `/services/${g.slug}`, label: g.title }} />
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
