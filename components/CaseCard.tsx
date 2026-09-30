import Link from 'next/link';
import type { Doc } from '@/lib/mongo';

// Fallback backgrounds when a case study has no cover image.
const tints = [
  'linear-gradient(135deg,#1d0509,#8c1226)', 'linear-gradient(135deg,#0f0f14,#5c1a2a)',
  'linear-gradient(135deg,#25070c,#b0122c)', 'linear-gradient(135deg,#141018,#7a1f34)',
  'linear-gradient(135deg,#1a0a0d,#c4152d)', 'linear-gradient(135deg,#0c0c10,#6e1224)',
];

export default function CaseCard({ doc, index = 0 }: { doc: Doc; index?: number }) {
  const href = `/case-studies/${doc.slug}`;
  return (
    <article className="case-card">
      <Link href={href} className="case-img" style={{ background: doc.image ? undefined : tints[index % tints.length] }} aria-label={doc.title}>
        {/* eslint-disable-next-line @next/next/no-img-element */}
        {doc.image && <img src={doc.image} alt="" loading="lazy" />}
        <span className="case-shade" />
        {doc.logo
          // eslint-disable-next-line @next/next/no-img-element
          ? <img className="case-logo" src={doc.logo} alt={doc.title} loading="lazy" />
          : <span className="case-word">{doc.title}</span>}
      </Link>
      <Link href={href} className="case-btn">See Case Study</Link>
    </article>
  );
}
