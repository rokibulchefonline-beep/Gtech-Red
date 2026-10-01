import Link from 'next/link';
import Icon from '@/components/Icon';
import InquirySection from '@/components/InquirySection';
import type { Section, ServiceContent } from '@/content/types';
import { findItem, industries, site } from '@/lib/data';
import { industryIcons, serviceIcons, uiIcons } from '@/lib/icons';

const Tick = () => <span className="tick"><Icon name={uiIcons.check} size={13} /></span>;

function Head({ s }: { s: Section }) {
  return (
    <>
      {s.eyebrow && <p className="sp-eyebrow">{s.eyebrow}</p>}
      <h2>{s.heading}</h2>
    </>
  );
}

function Block({ s, i }: { s: Section; i: number }) {
  const tone = i % 2 ? 'sp-sec alt' : 'sp-sec';
  switch (s.type) {
    case 'text':
      return (
        <section id={s.id} className={tone}><div className="wrap sp-narrow">
          <Head s={s} />{s.paras.map((p) => <p key={p.slice(0, 24)}>{p}</p>)}
          {s.bullets && <ul className="sp-list">{s.bullets.map((b) => <li key={b}><Tick />{b}</li>)}</ul>}
        </div></section>
      );
    case 'media':
      return (
        <section id={s.id} className={tone}><div className={`wrap sp-media ${s.flip ? 'flip' : ''}`}>
          <div className="sp-media-copy">
            <Head s={s} />{s.paras.map((p) => <p key={p.slice(0, 24)}>{p}</p>)}
            {s.bullets && <ul className="sp-list">{s.bullets.map((b) => <li key={b}><Tick />{b}</li>)}</ul>}
          </div>
          {/* eslint-disable-next-line @next/next/no-img-element */}
          <img className="sp-media-img" src={s.image} alt={s.alt} loading="lazy" width={960} height={720} />
        </div></section>
      );
    case 'cards':
      return (
        <section id={s.id} className={tone}><div className="wrap">
          <div className="sp-center"><Head s={s} />{s.intro && <p className="sp-intro">{s.intro}</p>}</div>
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
    case 'steps':
      return (
        <section id={s.id} className={tone}><div className="wrap">
          <div className="sp-center"><Head s={s} />{s.intro && <p className="sp-intro">{s.intro}</p>}</div>
          <ol className="sp-steps">
            {s.steps.map((st, n) => (
              <li key={st.title}><span className="sp-step-n">{String(n + 1).padStart(2, '0')}</span><h3>{st.title}</h3><p>{st.text}</p></li>
            ))}
          </ol>
        </div></section>
      );
    case 'table':
      return (
        <section id={s.id} className={tone}><div className="wrap sp-narrow wide">
          <div className="sp-center"><Head s={s} />{s.intro && <p className="sp-intro">{s.intro}</p>}</div>
          <div className="sp-table-wrap"><table className="sp-table">
            <thead><tr>{s.columns.map((c) => <th key={c} scope="col">{c}</th>)}</tr></thead>
            <tbody>{s.rows.map((r) => <tr key={r[0]}>{r.map((c, k) => (k ? <td key={k}>{c}</td> : <th key={k} scope="row">{c}</th>))}</tr>)}</tbody>
          </table></div>
          {s.note && <p className="sp-note">{s.note}</p>}
        </div></section>
      );
    case 'metrics':
      return (
        <section id={s.id} className={`${tone} sp-dark`}><div className="wrap">
          <div className="sp-center"><Head s={s} />{s.intro && <p className="sp-intro">{s.intro}</p>}</div>
          <div className="sp-metrics">
            {s.metrics.map((m) => <div key={m.label} className="sp-metric"><span>{m.label}</span><b>{m.value}</b><p>{m.text}</p></div>)}
          </div>
        </div></section>
      );
  }
}

export default function ServicePage({ c }: { c: ServiceContent }) {
  const found = findItem(c.slug);
  const related = c.related.map((r) => findItem(r)).filter(Boolean);
  const inds = industries.filter((i) => c.industries.includes(i.slug));
  const url = `https://www.gtechdigital.co.uk/services/${c.slug}`;
  const jsonLd = [
    { '@context': 'https://schema.org', '@type': 'Service', name: found?.item.name, serviceType: found?.item.name, description: c.metaDescription, url,
      areaServed: { '@type': 'Country', name: 'United Kingdom' }, provider: { '@type': 'Organization', name: site.name, url: 'https://www.gtechdigital.co.uk' } },
    { '@context': 'https://schema.org', '@type': 'FAQPage', mainEntity: c.faqs.map((f) => ({ '@type': 'Question', name: f.q, acceptedAnswer: { '@type': 'Answer', text: f.a } })) },
    { '@context': 'https://schema.org', '@type': 'BreadcrumbList', itemListElement: [
      { '@type': 'ListItem', position: 1, name: 'Home', item: 'https://www.gtechdigital.co.uk/' },
      { '@type': 'ListItem', position: 2, name: found?.group.title, item: `https://www.gtechdigital.co.uk/services/${found?.group.slug}` },
      { '@type': 'ListItem', position: 3, name: found?.item.name, item: url }] },
  ];

  return (
    <>
      <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: JSON.stringify(jsonLd) }} />

      <section className="sp-hero">
        <div className="wrap sp-hero-grid">
          <div>
            <nav className="sp-crumbs" aria-label="Breadcrumb">
              <Link href="/">Home</Link><span>/</span>
              {found && <><Link href={`/services/${found.group.slug}`}>{found.group.title}</Link><span>/</span></>}
              <b>{found?.item.name}</b>
            </nav>
            <p className="sp-hero-eyebrow">{c.hero.eyebrow}</p>
            <h1>{c.hero.title} <span className="red">{c.hero.highlight}</span></h1>
            <p className="sp-lead">{c.hero.lead}</p>
            <div className="sp-hero-btns">
              <Link className="btn" href={`/contact?service=${encodeURIComponent(found?.item.name ?? '')}`}>Get a free audit</Link>
              <a className="btn-ghost" href="#process">See our process</a>
            </div>
            <ul className="sp-hero-points">{c.hero.points.map((p) => <li key={p}><Tick />{p}</li>)}</ul>
          </div>
          <div className="sp-hero-media">
            {/* eslint-disable-next-line @next/next/no-img-element */}
            <img src={c.hero.motion} alt={`${found?.item.name} results dashboard`} width={800} height={600} />
            <span className="sp-hero-badge"><Icon name={serviceIcons[c.slug]} size={22} />{found?.item.name}</span>
          </div>
        </div>
      </section>

      <nav className="sp-toc" aria-label="On this page"><div className="wrap">
        {c.sections.filter((s) => s.nav).map((s) => <a key={s.id} href={`#${s.id}`}>{s.nav}</a>)}
        <a href="#faq">FAQs</a>
      </div></nav>

      {c.sections.map((s, i) => <Block key={s.id} s={s} i={i} />)}

      <section className="sp-sec alt"><div className="wrap">
        <div className="sp-center"><p className="sp-eyebrow">Industries</p><h2>{found?.item.name} for Your Industry</h2></div>
        <div className="sp-inds">
          {inds.map((i) => <Link key={i.slug} href={`/industries/${i.slug}`} className="ind-card"><span className="ind-ico"><Icon name={industryIcons[i.slug]} size={24} /></span><b>{i.name}</b></Link>)}
        </div>
      </div></section>

      <section id="faq" className="sp-sec"><div className="wrap sp-narrow">
        <div className="sp-center"><p className="sp-eyebrow">FAQs</p><h2>{found?.item.name} Questions, Answered</h2></div>
        <div className="sp-faq">
          {c.faqs.map((f, n) => (
            <details key={f.q} open={n === 0}><summary>{f.q}<Icon name={uiIcons.chevron} size={20} /></summary><p>{f.a}</p></details>
          ))}
        </div>
      </div></section>

      <section className="sp-sec alt"><div className="wrap">
        <div className="sp-center"><p className="sp-eyebrow">Related services</p><h2>Services That Work Well With {found?.item.name}</h2></div>
        <div className="sp-related">
          {related.map((r) => r && (
            <Link key={r.item.slug} href={`/services/${r.item.slug}`} className="sp-rel">
              <span className="sp-card-ico"><Icon name={serviceIcons[r.item.slug]} size={22} /></span>
              <div><h3>{r.item.name}</h3><p>{r.item.blurb}</p></div>
              <Icon name={uiIcons.arrowRight} size={20} />
            </Link>
          ))}
        </div>
      </div></section>

      <InquirySection />
    </>
  );
}
