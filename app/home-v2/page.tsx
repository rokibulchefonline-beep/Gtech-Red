import type { Metadata } from 'next';
import Link from 'next/link';
import Icon from '@/components/Icon';
import { getClients, getPartners, getStaticPage } from '@/lib/content';
import { industries, services, stats, testimonials } from '@/lib/data';
import { listDocs } from '@/lib/mongo';
import { plainHeading } from '@/lib/hl';
import { industryIcons, uiIcons } from '@/lib/icons';
import './v2.css';

// A redesign preview of the home page: Golos Text at one weight, teal palette, mint accent. Not indexed.
export const metadata: Metadata = { title: { absolute: 'GTech Digital | Digital Marketing Agency UK (preview)' }, robots: { index: false, follow: false }, alternates: { canonical: '/' } };

const Arrow = () => <i><Icon name={uiIcons.arrowRight} size={18} /></i>;
const Btn = ({ href, children }: { href: string; children: React.ReactNode }) => <Link className="v2-btn" href={href}>{children}<Arrow /></Link>;
const pad = (n: number) => String(n + 1).padStart(2, '0');

export default async function HomeV2() {
  const home = await getStaticPage('home');
  const section = (id: string) => home.sections.find((s) => s.id === id) as unknown as { heading: string; paras?: string[]; bullets?: string[]; text?: string; steps?: { title: string; text: string }[] };
  const who = section('who'), how = section('how'), howSteps = section('how-steps'), svc = section('services'), ind = section('industries');
  const [cases, clients] = await Promise.all([listDocs('case_studies', 3), getClients()]);
  const partners = (await getPartners()).slice(0, 8);
  const strip = (s: string) => s.replace(/<[^>]+>/g, '');
  const h1 = plainHeading(home.hero.h1 ?? '').split('|').join(' ');

  return (
    <div className="v2">
      <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Golos+Text:wght@400&display=swap" precedence="default" />

      <section className="v2-hero v2-dark">
        <div className="v2-wrap v2-hero-in">
          <span className="v2-eyebrow">UK digital agency</span>
          <h1 className="v2-d1">{h1}</h1>
          <p className="v2-lead" dangerouslySetInnerHTML={{ __html: home.hero.lead }} />
          <div className="v2-row">
            <Btn href="/contact">Let&apos;s Talk</Btn>
            <Link className="v2-link" href="/services">Our services <Icon name={uiIcons.arrowRight} size={18} /></Link>
          </div>
          <div className="v2-hero-cards">
            {stats.slice(0, 4).map((s) => <div className="v2-gcard" key={s.label}><b>{s.value}{s.suffix}</b><span>{s.label}</span></div>)}
          </div>
        </div>
      </section>

      <section className="v2-sec v2-off v2-after-hero">
        <div className="v2-wrap">
          <p className="v2-cap v2-logos-t">Certified partners and brands we have worked with</p>
          <div className="v2-logos">
            {[...partners.slice(0, 4), ...clients.slice(0, 4)].map((b) => /* eslint-disable-next-line @next/next/no-img-element */ <img key={b.name} src={b.logo} alt={b.name} loading="lazy" />)}
          </div>
        </div>
      </section>

      <section className="v2-sec v2-light" id="services">
        <div className="v2-wrap">
          <div className="v2-head">
            <div><span className="v2-eyebrow">What we do</span><h2 className="v2-h2l">{plainHeading(svc.heading)}</h2></div>
            <p className="v2-xl">{strip(svc.text ?? '')}</p>
          </div>
          <div className="v2-svc">
            {services.map((g, n) => (
              <Link key={g.slug} href={`/services/${g.slug}`} className={`v2-card${n === 0 ? ' feat' : ''}`}>
                <span className="n">{pad(n)}</span>
                <h3 className="v2-h4">{g.title}</h3>
                <p className="v2-s">{g.intro}</p>
                <ul className="v2-sm">{g.items.slice(0, 3).map((it) => <li key={it.slug}>{it.name}</li>)}</ul>
                <span className="more v2-s">View {g.items.length} services <Icon name={uiIcons.arrowRight} size={16} /></span>
              </Link>
            ))}
          </div>
        </div>
      </section>

      <section className="v2-sec v2-dark" id="approach">
        <div className="v2-wrap v2-split">
          <div>
            <span className="v2-eyebrow">Who we are</span>
            <h2 className="v2-h2l">{plainHeading(who.heading)}</h2>
            <p className="v2-xl" style={{ marginTop: 24, opacity: .75 }}>{strip(who.paras?.[0] ?? '')}</p>
            <ul className="v2-ticks v2-l">{(who.bullets ?? []).map((b) => <li key={b}>{strip(b)}</li>)}</ul>
            <div className="v2-row"><Btn href="/about">More about us</Btn><Link className="v2-link" href="/contact">Contact us <Icon name={uiIcons.arrowRight} size={18} /></Link></div>
          </div>
          <div>
            <p className="v2-eyebrow">{plainHeading(how.heading)}</p>
            <div className="v2-steps">
              {(howSteps.steps ?? []).map((s, n) => (
                <div className="v2-step v2-gcard" key={s.title}><span className="n">{pad(n)}</span><div><h3 className="v2-h4">{s.title}</h3><p className="v2-s">{strip(s.text)}</p></div></div>
              ))}
            </div>
          </div>
        </div>
      </section>

      <section className="v2-sec v2-off" id="results">
        <div className="v2-wrap">
          <div className="v2-head">
            <div><span className="v2-eyebrow">Case studies</span><h2 className="v2-h2l">Digital marketing case studies</h2></div>
            <p className="v2-xl">See how we help brands grow with results you can measure.</p>
          </div>
          <div className="v2-cases">
            {cases.map((c) => (
              <Link key={c.slug} href={`/case-studies/${c.slug}`} className="v2-case">
                <div className="v2-case-img">{c.image && /* eslint-disable-next-line @next/next/no-img-element */ <img src={c.image} alt={c.imageAlt || c.title} loading="lazy" />}</div>
                <div className="v2-case-body">
                  <h3 className="v2-h4">{c.client || c.title}</h3>
                  {c.excerpt && <p className="v2-s">{c.excerpt}</p>}
                  <div className="v2-chips">{(c.metrics ?? []).slice(0, 3).map((m) => <i key={m.label}>{m.value} {m.label}</i>)}</div>
                </div>
              </Link>
            ))}
          </div>
          <p style={{ marginTop: 32 }}><Link className="v2-link" href="/case-studies">View all case studies <Icon name={uiIcons.arrowRight} size={18} /></Link></p>
        </div>
      </section>

      <section className="v2-sec v2-teal" id="reviews">
        <div className="v2-wrap">
          <div className="v2-head"><div><span className="v2-eyebrow">Reviews</span><h2 className="v2-h2l">What our clients say</h2></div></div>
          <div className="v2-quotes">
            {testimonials.slice(0, 3).map((t) => <figure key={t.name} className="v2-quote"><h3 className="v2-h4">{t.title}</h3><q>{t.text}</q><cite>{t.name}</cite></figure>)}
          </div>
        </div>
      </section>

      <section className="v2-sec v2-tint" id="industries">
        <div className="v2-wrap">
          <div className="v2-head">
            <div><span className="v2-eyebrow">Industries</span><h2 className="v2-h2l">{plainHeading(ind.heading)}</h2></div>
            <p className="v2-xl">{strip(ind.paras?.[0] ?? '').split('. ')[0]}.</p>
          </div>
          <div className="v2-ind">
            {industries.map((i) => <Link key={i.slug} href={`/industries/${i.slug}`}>{i.name}<Icon name={industryIcons[i.slug] ?? uiIcons.arrowRight} size={20} /></Link>)}
          </div>
        </div>
      </section>

      <section className="v2-sec v2-deep v2-cta">
        <div className="v2-wrap">
          <span className="v2-eyebrow">Free proposal</span>
          <h2 className="v2-d2">Ready to grow your business?</h2>
          <p className="v2-xl">Tell us about your goals and we will send a clear plan and fixed quote within 24 hours.</p>
          <Btn href="/contact">Request a free proposal</Btn>
        </div>
      </section>
    </div>
  );
}
