import Link from 'next/link';
import Icon from '@/components/Icon';
import { uiIcons } from '@/lib/icons';

type Faq = { q: string; a: string };

// FAQ accordion with a dark aside. Pass `schema` to also output FAQPage JSON-LD.
export default function FaqSection({ title, faqs, schema = false }: { title: string; faqs: Faq[]; schema?: boolean }) {
  const ld = { '@context': 'https://schema.org', '@type': 'FAQPage', mainEntity: faqs.map((f) => ({ '@type': 'Question', name: f.q, acceptedAnswer: { '@type': 'Answer', text: f.a } })) };
  return (
    <section id="faq" className="sp-sec"><div className="wrap sp-faq-wrap">
      {schema && <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: JSON.stringify(ld) }} />}
      <aside className="sp-faq-aside">
        <p className="sp-eyebrow light">FAQs</p>
        <h2>{title}</h2>
        <p>Can not find what you are looking for? Our specialists are happy to help.</p>
        <Link className="btn light-btn" href="/contact">Ask an expert</Link>
      </aside>
      <div className="sp-faq">
        {faqs.map((f, n) => <details key={f.q} open={n === 0}><summary>{f.q}<Icon name={uiIcons.chevron} size={20} /></summary><p>{f.a}</p></details>)}
      </div>
    </div></section>
  );
}
