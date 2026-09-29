import Icon from '@/components/Icon';
import { groupIcons, industryIcons, serviceIcons } from '@/lib/icons';
import type { Metadata } from 'next';
import Link from 'next/link';
import { notFound } from 'next/navigation';
import PageHead from '@/components/PageHead';
import { industries, services } from '@/lib/data';

type Props = { params: Promise<{ slug: string }> };

export const dynamicParams = false;

export function generateStaticParams() {
  return industries.map((i) => ({ slug: i.slug }));
}

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const { slug } = await params;
  const ind = industries.find((i) => i.slug === slug);
  return { title: ind ? `${ind.name} Marketing & Software` : undefined };
}

export default async function Industry({ params }: Props) {
  const { slug } = await params;
  const ind = industries.find((i) => i.slug === slug);
  if (!ind) notFound();
  return (
    <>
      <PageHead icon={industryIcons[ind.slug]} title={ind.name} sub={`Marketing, websites and software for ${ind.name.toLowerCase()} businesses.`} />
      <section className="wrap block prose" style={{ maxWidth: 'none' }}>
        <h2>What we do for {ind.name}</h2>
        <p>Replace with industry-specific copy, challenges and results.</p>
        <div className="cards">
          {services.map((g) => (
            <Link key={g.slug} className="card" href={`/services/${g.slug}`}><Icon className="card-ico" name={groupIcons[g.slug]} size={28} /><h3>{g.title}</h3><p>{g.intro}</p></Link>
          ))}
        </div>
      </section>
    </>
  );
}
