import type { Metadata } from 'next';
import Link from 'next/link';
import Icon from '@/components/Icon';
import { getPosts } from '@/lib/blog';
import { formatDate } from '@/lib/blog-utils';
import { getStaticPage } from '@/lib/content';
import { industries, services, stats, testimonials } from '@/lib/data';
import { plainHeading } from '@/lib/hl';
import { groupIcons, industryIcons, uiIcons } from '@/lib/icons';
import { listDocs } from '@/lib/mongo';
import { BarsArt, CodeArt, Dashboard, NodesArt, OrbsArt } from './art';
import Motion from './Motion';
import './ax.css';

// Redesign preview of the home page in the Agentix design system, recoloured to the GTech red. Not indexed.
export const metadata: Metadata = { title: { absolute: 'GTech Digital | Digital Marketing Agency UK (preview)' }, robots: { index: false, follow: false }, alternates: { canonical: '/' } };

const strip = (s = '') => s.replace(/<[^>]+>/g, '').replace(/\[\[|\]\]/g, '');
const SMALL = new Set(['and', 'for', 'the', 'with', 'our', 'you', 'your', 'from']);
const title = (s: string) => s.replace(/\b([a-z])([a-z]+)/g, (m, a, b, off) => (off > 0 && SMALL.has(m) ? m : a.toUpperCase() + b));
const Arrow = () => <Icon name={uiIcons.arrowRight} size={16} />;
type Sec = { heading: string; paras?: string[]; bullets?: string[]; text?: string; steps?: { title: string; text: string }[] };

export default async function HomeV2() {
  const home = await getStaticPage('home');
  const hub = await getStaticPage('services-hub');
  const sec = (id: string) => home.sections.find((s) => s.id === id) as unknown as Sec;
  const who = sec('who'), how = sec('how'), steps = sec('how-steps'), ind = sec('industries');
  const [cases, posts] = await Promise.all([listDocs('case_studies', 3), getPosts().catch(() => [])]);
  const h1 = title(plainHeading(home.hero.h1 ?? '').split('|').join(' '));
  const lead = strip(home.hero.lead);
  const quotes = [...testimonials, ...testimonials];
  const art = [<BarsArt key="b" />, <CodeArt key="c" />, <OrbsArt key="o" />, <NodesArt key="n" />, <BarsArt key="b2" />];

  return (
    <div className="ax">
      <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Geist:wght@500&family=Inter:wght@400;500;600&display=swap" precedence="default" />
      <Motion />

      {/* Hero */}
      <section className="ax-hero">
        <div className="ax-wrap">
          <span className="ax-pill"><b>NEW</b> Free marketing audit for UK businesses</span>
          <h1 className="ax-h1">{h1}</h1>
          <p className="lead">{lead}</p>
          <div className="ax-btns">
            <Link className="ax-btn" href="/contact">Let&apos;s Talk <Arrow /></Link>
            <Link className="ax-btn alt" href="/services">Our Services</Link>
          </div>
          <Dashboard />
        </div>
      </section>

      {/* Testimonial ticker */}
      <div className="ax-ticker" aria-label="Client reviews">
        <div className="ax-track">
          {quotes.map((t, i) => <q key={i} aria-hidden={i >= testimonials.length || undefined}>{t.title}<small>{t.name}, verified client</small></q>)}
        </div>
      </div>

      {/* Three feature cards */}
      <section className="ax-sec" id="services">
        <div className="ax-wrap">
          <div className="ax-head" data-rv><h2 className="ax-h2">{title(plainHeading(sec('services').heading))}</h2><p>{strip(sec('services').text)}</p></div>
          <div className="ax-grid3">
            {services.slice(0, 3).map((g, i) => (
              <Link key={g.slug} href={`/services/${g.slug}`} className="ax-card" data-rv style={{ transitionDelay: `${i * 80}ms` }}>
                <div className="ax-panel">{art[i]}</div>
                <h3 className="ax-title">{g.title}</h3>
                <p>{g.intro}</p>
                <span className="ax-link">Explore {g.items.length} services <Arrow /></span>
              </Link>
            ))}
          </div>
        </div>
      </section>

      {/* Deep dives */}
      <section className="ax-sec ax-s1" id="about">
        <div className="ax-wrap">
          <div className="ax-dive" data-rv>
            <div>
              <h2 className="ax-h3">{title(plainHeading(who.heading))}</h2>
              <p>{strip(who.paras?.[0])}</p>
              <ul className="ax-ticks">{(who.bullets ?? []).map((b) => <li key={b}>{strip(b)}</li>)}</ul>
              <div className="ax-stats">{stats.slice(0, 3).map((s) => <div key={s.label}><b>{s.value}{s.suffix}</b><span>{s.label}</span></div>)}</div>
            </div>
            <div className="ax-dive-img">{/* eslint-disable-next-line @next/next/no-img-element */}<img src="/pages/about/story.webp" alt="GTech Digital journey from SEO specialists to a full marketing, web and software agency" loading="lazy" /></div>
          </div>
          <div className="ax-dive flip" data-rv>
            <div>
              <h2 className="ax-h3">One Team, Every Skill You Need</h2>
              <p>Search, paid media, content, design and engineering sit in one team with one plan, so your marketing, website and systems pull in the same direction.</p>
              <ul className="ax-ticks">{['One dedicated account lead', 'Fixed, clear pricing', 'Plain-English monthly reports'].map((b) => <li key={b}>{b}</li>)}</ul>
              <div className="ax-btns" style={{ justifyContent: 'flex-start', marginTop: 28 }}><Link className="ax-btn" href="/about">More About Us <Arrow /></Link></div>
            </div>
            <div className="ax-dive-img">{/* eslint-disable-next-line @next/next/no-img-element */}<img src="/pages/about/team.webp" alt="GTech Digital specialists in search, paid media, content, design, development and strategy" loading="lazy" /></div>
          </div>
        </div>
      </section>

      {/* Services on a gradient wash */}
      <section className="ax-sec ax-wash">
        <div className="ax-wrap">
          <div className="ax-head" data-rv><h2 className="ax-h2">Everything Your Business Needs To Grow</h2><p>Five disciplines, measured on leads and revenue.</p></div>
          <div className="ax-feat">
            {services.map((g, i) => (
              <Link key={g.slug} href={`/services/${g.slug}`} data-rv style={{ transitionDelay: `${i * 60}ms` }}>
                <span className="ico"><Icon name={groupIcons[g.slug]} size={22} /></span>
                <h3 className="ax-title">{g.title}</h3>
                <p>{g.items.slice(0, 3).map((it) => it.name).join(', ')} and more.</p>
              </Link>
            ))}
            <Link href="/industries" data-rv><span className="ico"><Icon name="lucide:layout-grid" size={22} /></span><h3 className="ax-title">Industry Specialists</h3><p>Sector-specific strategy for {industries.length} industries.</p></Link>
          </div>
        </div>
      </section>

      {/* Social proof */}
      <section className="ax-sec ax-s3">
        <div className="ax-wrap">
          <div className="ax-head" data-rv><h2 className="ax-h2">Results You Can Measure</h2><p>Demo figures shown. Replace with your real client data.</p></div>
          <div className="ax-proof">
            <div data-rv><span className="big">{stats[1].value}{stats[1].suffix}</span><q>{testimonials[1].text}</q><cite>{testimonials[1].name}</cite></div>
            <div className="dark" data-rv><span className="big">{stats[4].value}{stats[4].suffix}</span><q>{testimonials[4].text}</q><cite>{testimonials[4].name}</cite></div>
          </div>
        </div>
      </section>

      {/* Case studies */}
      <section className="ax-sec" id="results">
        <div className="ax-wrap">
          <div className="ax-head" data-rv><h2 className="ax-h2">Digital Marketing Case Studies</h2><p>See how we help brands grow with results you can measure.</p></div>
          <div className="ax-cards">
            {cases.map((c, i) => (
              <Link key={c.slug} href={`/case-studies/${c.slug}`} className="ax-case" data-rv style={{ transitionDelay: `${i * 80}ms` }}>
                <div className="ax-case-img">{c.image && /* eslint-disable-next-line @next/next/no-img-element */ <img src={c.image} alt={c.imageAlt || c.title} loading="lazy" />}</div>
                <div className="ax-case-body">
                  <h3 className="ax-title">{c.client || c.title}</h3>
                  {c.excerpt && <p>{c.excerpt}</p>}
                  <div className="ax-chips">{(c.metrics ?? []).slice(0, 3).map((m) => <i key={m.label}>{m.value} {m.label}</i>)}</div>
                </div>
              </Link>
            ))}
          </div>
          <div className="ax-btns" style={{ marginTop: 40 }}><Link className="ax-btn alt" href="/case-studies">View All Case Studies</Link></div>
        </div>
      </section>

      {/* How it works */}
      <section className="ax-sec ax-s1">
        <div className="ax-wrap">
          <div className="ax-head" data-rv><h2 className="ax-h2">{title(plainHeading(how.heading))}</h2><p>{strip(how.text)}</p></div>
          <div className="ax-steps">
            {(steps.steps ?? []).map((s, i) => (
              <div key={s.title} className="ax-step" data-rv style={{ transitionDelay: `${i * 80}ms` }}>
                <span className="n">{i + 1}</span><h3 className="ax-title">{s.title}</h3><p>{strip(s.text)}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Industries */}
      <section className="ax-sec" id="industries">
        <div className="ax-wrap">
          <div className="ax-head" data-rv><h2 className="ax-h2">{title(plainHeading(ind.heading))}</h2><p>{strip(ind.paras?.[0]).split('. ')[0]}.</p></div>
          <div className="ax-ind">
            {industries.map((i, n) => <Link key={i.slug} href={`/industries/${i.slug}`} data-rv style={{ transitionDelay: `${n * 40}ms` }}><span className="ico"><Icon name={industryIcons[i.slug]} size={20} /></span>{i.name}</Link>)}
          </div>
        </div>
      </section>

      {/* FAQ */}
      <section className="ax-sec ax-s3" id="faq">
        <div className="ax-wrap">
          <div className="ax-head" data-rv><h2 className="ax-h2">Frequently Asked Questions</h2></div>
          <div className="ax-faq" data-rv>
            {hub.faqs.map((f, n) => <details key={f.q} open={n === 0}><summary>{f.q}</summary><p>{strip(f.a)}</p></details>)}
          </div>
        </div>
      </section>

      {/* Blog */}
      {posts.length > 0 && (
        <section className="ax-sec">
          <div className="ax-wrap">
            <div className="ax-head" data-rv><h2 className="ax-h2">Latest Insights</h2><p>Practical advice on SEO, ads, websites and software.</p></div>
            <div className="ax-cards">
              {posts.slice(0, 3).map((p, i) => (
                <Link key={p.slug} href={`/blogs/${p.slug}`} className="ax-case" data-rv style={{ transitionDelay: `${i * 80}ms` }}>
                  <div className="ax-case-img">{/* eslint-disable-next-line @next/next/no-img-element */}<img src={p.image} alt={p.imageAlt || p.title} loading="lazy" /></div>
                  <div className="ax-case-body"><span className="ax-meta">{p.category} · {formatDate(p.date)}</span><h3 className="ax-title">{p.title}</h3><p>{p.excerpt}</p></div>
                </Link>
              ))}
            </div>
          </div>
        </section>
      )}

      {/* CTA */}
      <section className="ax-cta" data-rv>
        <h2 className="ax-h2">Ready To Grow Your Business?</h2>
        <p>Tell us about your goals and we will send a clear plan and fixed quote within 24 hours.</p>
        <div className="ax-btns"><Link className="ax-btn on-dark" href="/contact">Request A Free Proposal <Arrow /></Link></div>
      </section>
      <div className="ax-after-cta" />
    </div>
  );
}
