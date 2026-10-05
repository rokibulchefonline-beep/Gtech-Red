import Link from 'next/link';
import CaseCarousel from '@/components/CaseCarousel';
import Icon from '@/components/Icon';
import InquirySection from '@/components/InquirySection';
import FaqSection from '@/components/service/FaqSection';
import type { Section, ServiceContent } from '@/content/types';
import { findGroup, findItem, industries, site } from '@/lib/data';
import { groupIcons, industryIcons, serviceIcons, uiIcons } from '@/lib/icons';
import { caseStudiesFor } from '@/lib/mongo';
import { getClients } from '@/lib/content';
import TocBar from '@/components/TocBar';
import Hl from '@/components/Hl';

export const Tick = () => <span className="tick"><Icon name={uiIcons.check} size={13} /></span>;
const Paras = ({ p }: { p: string[] }) => <>{p.map((t) => <p key={t.slice(0, 30)}>{t}</p>)}</>;
const List = ({ b }: { b?: string[] }) => (b ? <ul className="sp-list">{b.map((x) => <li key={x}><Tick />{x}</li>)}</ul> : null);

export function Head({ s, center = true, intro }: { s: { heading: string }; center?: boolean; intro?: string }) {
  return (
    <div className={center ? 'sp-head center' : 'sp-head'}>
      <h2><Hl>{s.heading}</Hl></h2>
      {intro && <p className="sp-intro">{intro}</p>}
    </div>
  );
}

export async function Block({ s, slug, name }: { s: Section; slug: string; name: string }) {
  switch (s.type) {
    case 'logos':
      return (
        <section className="sp-logos" aria-label="Clients"><div className="wrap">
          <p>Trusted by growing UK brands</p>
          <div className="sp-logo-row">{/* eslint-disable-next-line @next/next/no-img-element */}
            {(await getClients()).slice(0, 6).map((b) => <img key={b.name} src={b.logo} alt={b.name} loading="lazy" />)}</div>
        </div></section>
      );
    case 'text':
      return (
        <section id={s.id} className="sp-sec"><div className="wrap sp-split">
          <div className="sp-split-head">
            <h2><Hl>{s.heading}</Hl></h2>
            <span className="sp-split-shape" aria-hidden="true" />
          </div>
          <div className="sp-split-body"><Paras p={s.paras} /><List b={s.bullets} /></div>
        </div></section>
      );
    case 'media':
      return (
        <section id={s.id} className={`sp-sec ${s.tone === 'grey' ? 'sp-grey' : ''} ${s.flip ? 'flip' : ''}`}><div className="wrap sp-media">
          <div className="sp-media-copy"><Head s={s} center={false} /><Paras p={s.paras} /><List b={s.bullets} /></div>
          <div className="sp-media-frame">
            {/* eslint-disable-next-line @next/next/no-img-element */}
            <img src={s.image} alt={s.alt} loading="lazy" width={960} height={720} />
          </div>
        </div></section>
      );
    case 'cards':
      return (
        <section id={s.id} className={`sp-sec ${s.cards.length > 4 ? 'sp-bg-dark' : ''}`}><div className="wrap">
          <Head s={s} intro={s.intro} />
          <div className={`sp-cards n${s.cards.length}`}>
            {s.cards.map((c) => (
              <article key={c.title} className="sp-card">
                <span className="sp-card-ico"><Icon name={c.icon} size={24} /></span>
                <h3>{c.title}</h3><p>{c.text}</p>
              </article>
            ))}
          </div>
        </div></section>
      );
    case 'features':
      return (
        <section id={s.id} className="sp-sec"><div className="wrap sp-feat">
          <div><Head s={s} center={false} intro={s.intro} /><Paras p={s.paras} />
            <Link className="btn" href="/contact">Talk to our team</Link></div>
          <div className="sp-feat-grid">
            {s.cards.map((c) => <article key={c.title} className="sp-feat-card"><span className="sp-card-ico solid"><Icon name={c.icon} size={22} /></span><h3>{c.title}</h3><p>{c.text}</p></article>)}
          </div>
        </div></section>
      );
    case 'steps':
      return (
        <section id={s.id} className="sp-sec sp-bg-mesh"><div className="wrap">
          <Head s={s} intro={s.intro} />
          <ol className="sp-steps">
            {s.steps.map((st, n) => (
              <li key={st.title}><span className="sp-step-n">{n + 1}</span><div><h3>{st.title}</h3><p>{st.text}</p></div></li>
            ))}
          </ol>
        </div></section>
      );
    case 'table':
      return (
        <section id={s.id} className="sp-sec sp-grey"><div className="wrap" style={{ maxWidth: 1040 }}>
          <Head s={s} intro={s.intro} />
          <div className="sp-table-wrap"><table className="sp-table">
            <thead><tr>{s.columns.map((c) => <th key={c} scope="col">{c}</th>)}</tr></thead>
            <tbody>{s.rows.map((r) => <tr key={r[0]}>{r.map((c, k) => (k ? <td key={k}>{c}</td> : <th key={k} scope="row">{c}</th>))}</tr>)}</tbody>
          </table></div>
          {s.note && <p className="sp-note"><Icon name="lucide:lightbulb" size={20} />{s.note}</p>}
        </div></section>
      );
    case 'metrics':
      return (
        <section id={s.id} className="sp-sec sp-bg-red"><div className="wrap">
          <Head s={s} intro={s.intro} />
          <div className="sp-metrics">
            {s.metrics.map((m) => <div key={m.label} className="sp-metric"><span>{m.label}</span><b>{m.value}</b><p>{m.text}</p></div>)}
          </div>
        </div></section>
      );
    case 'impact':
      return (
        <section id={s.id} className="sp-impact"><div className="wrap sp-impact-in">
          <div className="sp-impact-copy"><h2><Hl>{s.heading}</Hl></h2><p>{s.text}</p></div>
          <div className="sp-impact-stats">
            {s.stats.map((st) => <div key={st.label} className="sp-impact-stat"><b>{st.value}</b><span>{st.label}</span></div>)}
          </div>
        </div></section>
      );
    case 'cases': {
      const docs = await caseStudiesFor(slug, 6);
      if (!docs.length) return null;
      return (
        <section id={s.id} className="sp-sec cases"><div className="wrap">
          <Head s={s} intro={s.intro} />
          <CaseCarousel docs={docs} />
          <p className="cases-all"><Link className="btn-dark" href="/case-studies">View All Case Studies</Link></p>
        </div></section>
      );
    }
    case 'reviews':
      return (
        <section id={s.id} className="sp-sec sp-bg-dark"><div className="wrap">
          <Head s={s} intro={s.intro} />
          <div className="sp-reviews">
            {s.reviews.map((r) => (
              <figure key={r.name} className="sp-review">
                <div className="sp-stars">{[0, 1, 2, 3, 4].map((k) => <Icon key={k} name={uiIcons.star} size={18} />)}</div>
                <blockquote>“{r.text}”</blockquote>
                <figcaption><span className="sp-avatar">{r.name[0]}</span><span><b>{r.name}</b><small>{r.role}</small></span></figcaption>
              </figure>
            ))}
          </div>
        </div></section>
      );
    case 'industries':
      return (
        <section id={s.id} className="sp-sec sp-grey"><div className="wrap">
          <Head s={s} intro={s.intro} />
          <div className="sp-inds">
            {s.items.map((it) => {
              const ind = industries.find((i) => i.slug === it.slug);
              return ind && (
                <Link key={it.slug} href={`/industries/${ind.slug}`} className="sp-ind">
                  <span className="sp-ind-ico"><Icon name={industryIcons[ind.slug]} size={24} /></span>
                  <h3>{ind.name}</h3>
                  <p>{it.text}</p>
                  <span className="sp-ind-more">Explore {ind.name} <Icon name={uiIcons.arrowRight} size={16} /></span>
                </Link>
              );
            })}
          </div>
          <p className="sp-inds-all"><Link href="/industries">View all industries <Icon name={uiIcons.arrowRight} size={16} /></Link></p>
        </div></section>
      );
  }
}

export default function ServicePage({ c }: { c: ServiceContent }) {
  const found = findItem(c.slug);
  const groupPage = findGroup(c.slug); // category pages (e.g. Social Media Marketing) use the same template
  const name = found?.item.name ?? groupPage?.title ?? '';
  const short = c.short ?? name;
  const related = c.related.map((r) => findItem(r)).filter(Boolean);
  const base = 'https://www.gtechdigital.co.uk';
  const url = `${base}/services/${c.slug}`;
  const jsonLd = [
    { '@context': 'https://schema.org', '@type': 'Service', name, serviceType: name, description: c.metaDescription, url,
      areaServed: { '@type': 'Country', name: 'United Kingdom' }, provider: { '@type': 'Organization', name: site.name, url: base } },
    { '@context': 'https://schema.org', '@type': 'FAQPage', mainEntity: c.faqs.map((f) => ({ '@type': 'Question', name: f.q, acceptedAnswer: { '@type': 'Answer', text: f.a } })) },
    { '@context': 'https://schema.org', '@type': 'BreadcrumbList', itemListElement: [
      { '@type': 'ListItem', position: 1, name: 'Home', item: `${base}/` },
      ...(found ? [{ '@type': 'ListItem', position: 2, name: found.group.title, item: `${base}/services/${found.group.slug}` }] : []),
      { '@type': 'ListItem', position: found ? 3 : 2, name, item: url }] },
  ];

  return (
    <>
      <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: JSON.stringify(jsonLd) }} />

      <section className="sp-hero">
        <div className="wrap sp-hero-in">
          <nav className="sp-crumbs" aria-label="Breadcrumb">
            <Link href="/">Home</Link><span>/</span>
            {found && <><Link href={`/services/${found.group.slug}`}>{found.group.title}</Link><span>/</span></>}<b>{name}</b>
          </nav>
          <h1>{c.hero.keyword ?? name} Services of <span className="red">GTech Digital</span></h1>
          <p className="sp-lead">{c.hero.lead}</p>
          <div className="sp-hero-btns">
            <Link className="sp-btn-red" href={`/contact?service=${encodeURIComponent(name)}`}>Book a Free Audit</Link>
            <a className="sp-btn-line" href="#case-studies">View Case Studies</a>
          </div>
          <ul className="sp-hero-points">{c.hero.points.map((p) => <li key={p}><Tick />{p}</li>)}</ul>
          <div className="sp-hero-show">
            {/* eslint-disable-next-line @next/next/no-img-element */}
            <img src={c.hero.motion} alt={`${name} results dashboard`} width={800} height={600} />
            <span className="sp-float a"><Icon name={serviceIcons[c.slug] ?? groupIcons[c.slug]} size={20} />{name}</span>
            <span className="sp-float b"><Icon name="lucide:trending-up" size={20} />Revenue-focused</span>
          </div>
        </div>
      </section>

      <TocBar label="On this page" items={[...c.sections.filter((s) => 'nav' in s && s.nav).map((s) => ({ id: s.id, label: 'nav' in s ? s.nav ?? '' : '' })), { id: 'faq', label: 'FAQs' }]} />

      {c.sections.map((s) => <Block key={s.id} s={s} slug={c.slug} name={short} />)}

      <FaqSection title={`Frequently Asked Questions About ${short}`} faqs={c.faqs} />

      <section className="sp-sec sp-grey"><div className="wrap">
        <Head s={{ heading: `Services Related to ${short}` }} />
        <div className="sp-related">
          {related.map((r) => r && (
            <Link key={r.item.slug} href={`/services/${r.item.slug}`} className="sp-rel">
              <span className="sp-card-ico solid"><Icon name={serviceIcons[r.item.slug]} size={22} /></span>
              <h3>{r.item.name}</h3><p>{r.item.blurb}</p><span className="sp-more">Explore <Icon name={uiIcons.arrowRight} size={16} /></span>
            </Link>
          ))}
        </div>
      </div></section>

      <InquirySection />
    </>
  );
}
