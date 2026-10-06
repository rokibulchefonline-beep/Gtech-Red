import type { Metadata } from 'next';
import Link from 'next/link';
import { notFound } from 'next/navigation';
import CaseCard from '@/components/CaseCard';
import Hl from '@/components/Hl';
import Icon from '@/components/Icon';
import InquirySection from '@/components/InquirySection';
import { findItem } from '@/lib/data';
import { serviceIcons } from '@/lib/icons';
import { getDoc, listDocs } from '@/lib/mongo';
import { seoFor } from '@/lib/seo';
import Schema from '@/components/Schema';
import { BASE, articleNode, breadcrumbNode, pageNode } from '@/lib/schema';

type Props = { params: Promise<{ slug: string }> };

// Pre-rendered at build time and served as static HTML (no per-request rendering).
export const dynamicParams = false;
const base = 'https://www.gtechdigital.co.uk';

export async function generateStaticParams() {
  return (await listDocs('case_studies', 200)).map((d) => ({ slug: d.slug }));
}

const headline = (d: { client?: string; title: string; metrics?: { value: string; label: string }[] }) =>
  `${d.client || d.title} Case Study${d.metrics?.length ? `: ${d.metrics.slice(0, 2).map((m) => `${m.value} ${m.label}`).join(' and ')}` : ''}`;

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const slug = (await params).slug;
  const d = await getDoc('case_studies', slug);
  if (!d) return {};
  return seoFor(`/case-studies/${slug}`, {
    title: { absolute: d.metaTitle || `${headline(d)} | GTech Digital` },
    description: d.metaDescription || d.excerpt,
    alternates: { canonical: `/case-studies/${slug}` },
    openGraph: { title: headline(d), description: d.excerpt, images: d.image ? [d.image] : undefined },
  });
}

export default async function Page({ params }: Props) {
  const { slug } = await params;
  const d = await getDoc('case_studies', slug);
  if (!d) notFound();
  const others = (await listDocs('case_studies', 12)).filter((x) => x.slug !== d.slug).slice(0, 3);
  const used = (d.services ?? []).map((s) => findItem(s)).filter((x): x is NonNullable<ReturnType<typeof findItem>> => !!x).slice(0, 6);
  const name = d.client || d.title;
  const metrics = d.metrics ?? [];

  const path = `/case-studies/${d.slug}`;
  const nodes = [
    pageNode({ path, name: headline(d), description: d.excerpt ?? '', mainEntity: `${BASE}${path}#article`, image: d.image, about: used.map((u) => u.item.name) }),
    breadcrumbNode(path, [['Case Studies', '/case-studies'], [name, path]]),
    articleNode({ path, headline: headline(d), description: d.metaDescription || d.excerpt || '', image: d.image, keywords: used.map((u) => u.item.name) }),
  ];

  return (
    <>
      <Schema path={path} nodes={nodes} />

      <section className="cs-hero">
        <div className={`wrap cs-hero-in${d.image ? ' has-img' : ''}`}>
          <div className="cs-hero-copy">
          <nav className="sp-crumbs left" aria-label="Breadcrumb"><Link href="/">Home</Link><span>/</span><Link href="/case-studies">Case Studies</Link><span>/</span><b>{name}</b></nav>
          {d.industry && <span className="cs-tag">{d.industry}</span>}
          <h1>{name} Case Study{metrics.length > 0 && <>: <span className="hl">{metrics.slice(0, 2).map((m) => `${m.value} ${m.label}`).join(' and ')}</span></>}</h1>
          {d.excerpt && <p className="cs-lead">{d.excerpt}</p>}
          <div className="sp-hero-btns left">
            <Link className="sp-btn-red" href={`/contact?service=${encodeURIComponent(used[0]?.item.name ?? '')}`}>Get Similar Results</Link>
            <a className="sp-btn-line light" href="#results">See the Results</a>
          </div>
          </div>
          {d.image && <div className="cs-hero-art">{/* eslint-disable-next-line @next/next/no-img-element */}<img src={d.image} alt={d.imageAlt || `${name} case study`} /></div>}
        </div>
      </section>

      {metrics.length > 0 && (
        <div id="results" className="wrap cs-metrics-wrap"><div className={`cs-metrics n${metrics.length}`}>
          {metrics.map((m) => <div key={m.label} className="cs-metric"><strong>{m.value}</strong><span>{m.label}</span></div>)}
        </div></div>
      )}

      <section className="sp-sec cs-body"><div className="wrap cs-grid">
        <aside className="cs-snap"><div className="cs-snap-in">
          {d.logo && <>{/* eslint-disable-next-line @next/next/no-img-element */}<img className="cs-snap-logo" src={d.logo} alt={name} /></>}
          <h2>Project Snapshot</h2>
          <dl>
            <div><dt>Client</dt><dd>{name}</dd></div>
            {d.industry && <div><dt>Industry</dt><dd>{d.industry}</dd></div>}
            {d.duration && <div><dt>Duration</dt><dd>{d.duration}</dd></div>}
            {d.website && <div><dt>Website</dt><dd><a href={d.website} target="_blank" rel="noopener noreferrer">{d.website.replace(/^https?:\/\//, '')}</a></dd></div>}
          </dl>
          {used.length > 0 && <>
            <h3>Services</h3>
            <ul className="cs-snap-svc">{used.map(({ item }) => <li key={item.slug}><Link href={`/services/${item.slug}`}><Icon name={serviceIcons[item.slug]} size={16} />{item.name}</Link></li>)}</ul>
          </>}
          <Link className="sp-btn-red wide" href={`/contact?service=${encodeURIComponent(used[0]?.item.name ?? '')}`}>Talk to Our Team</Link>
        </div></aside>

        <div className="cs-main">
          {d.challenge && <section id="challenge"><h2><Hl>{`The Challenge Facing ${name}`}</Hl></h2><p>{d.challenge}</p></section>}
          {d.solution && <section id="solution"><h2><Hl>{`Our Solution for ${name}`}</Hl></h2><p>{d.solution}</p></section>}
          {!d.challenge && !d.solution && d.body && <section><p style={{ whiteSpace: 'pre-line' }}>{d.body}</p></section>}
        </div>
      </div></section>

      {d.quote?.text && (
        <section className="sp-sec"><div className="wrap"><figure className="cs-quote">
          <Icon name="lucide:quote" size={34} />
          <blockquote>{d.quote.text}</blockquote>
          <figcaption><b>{d.quote.name}</b>{d.quote.role && <span>{d.quote.role}</span>}</figcaption>
        </figure></div></section>
      )}

      {others.length > 0 && (
        <section className="sp-sec"><div className="wrap">
          <div className="sp-head center"><h2><Hl>More Digital Marketing Case Studies</Hl></h2></div>
          <div className="case-grid">{others.map((o, i) => <CaseCard key={o.slug} doc={o} index={i} />)}</div>
        </div></section>
      )}

      <InquirySection />
    </>
  );
}
