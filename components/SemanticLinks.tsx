import Link from 'next/link';
import Hl from '@/components/Hl';
import Icon from '@/components/Icon';
import { semanticLinksFor } from '@/lib/link-graph';
import { uiIcons } from '@/lib/icons';

// Contextual, keyword-anchored links to closely related services, industries and pages.
// Driven by content/seo-map.ts so the same links feed the audit and the Service schema.
export default function SemanticLinks({ slug, name }: { slug: string; name: string }) {
  const links = semanticLinksFor(slug);
  if (!links.length) return null;
  return (
    <section className="sp-sec sem" aria-labelledby="sem-h"><div className="wrap">
      <div className="sp-head center"><h2 id="sem-h"><Hl>{`Explore Topics Related to ${name}`}</Hl></h2></div>
      <ul className="sem-grid">
        {links.map((l) => (
          <li key={l.href}><Link href={l.href}>
            <span className="sem-kind">{l.group === 'industry' ? 'Industry' : l.group === 'page' ? 'Guide' : 'Service'}</span>
            <b>{l.anchor}</b><span>{l.why}</span>
            <i><Icon name={uiIcons.arrowRight} size={16} /></i>
          </Link></li>
        ))}
      </ul>
    </div></section>
  );
}
