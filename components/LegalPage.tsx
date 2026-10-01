import Link from 'next/link';
import Toc from '@/components/blog/Toc';
import { slugify } from '@/lib/util';
import type { LegalDoc } from '@/content/legal/types';

export default function LegalPage({ doc }: { doc: LegalDoc }) {
  const items = doc.sections.map((s) => ({ id: slugify(s.h), text: s.h }));
  return (
    <>
      <section className="sp-hero compact">
        <div className="wrap sp-hero-in">
          <nav className="sp-crumbs" aria-label="Breadcrumb"><Link href="/">Home</Link><span>/</span><b>{doc.title}</b></nav>
          <h1>{doc.title}</h1>
          <p className="sp-lead">{doc.intro}</p>
          <p className="lg-updated">Last updated: {doc.updated}</p>
        </div>
      </section>
      <div className="wrap lg-layout">
        <aside><div className="bp-sticky"><Toc items={items} />
          <div className="bp-cta"><p className="bl-side-title light">Questions?</p><p>Contact us about this policy or your data at any time.</p><Link className="bp-cta-btn" href="/contact">Contact Us</Link></div>
        </div></aside>
        <article className="bp-body lg-body">
          {doc.sections.map((s) => (
            <section key={s.h}>
              <h2 id={slugify(s.h)}>{s.h}</h2>
              {s.p?.map((t) => <p key={t.slice(0, 40)}>{t}</p>)}
              {s.ul && <ul>{s.ul.map((t) => <li key={t}>{t}</li>)}</ul>}
              {s.after?.map((t) => <p key={t.slice(0, 40)}>{t}</p>)}
            </section>
          ))}
        </article>
      </div>
    </>
  );
}
