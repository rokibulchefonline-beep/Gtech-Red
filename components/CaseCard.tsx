import Link from 'next/link';
import type { Doc } from '@/lib/mongo';

// Fallback backgrounds when a case study has no cover image.
const tints = [
  'linear-gradient(135deg,#1d0509,#8c1226)', 'linear-gradient(135deg,#0f0f14,#5c1a2a)',
  'linear-gradient(135deg,#25070c,#b0122c)', 'linear-gradient(135deg,#141018,#7a1f34)',
  'linear-gradient(135deg,#1a0a0d,#c4152d)', 'linear-gradient(135deg,#0c0c10,#6e1224)',
];

// Banner-only card. The project name and three headline numbers appear on hover or keyboard focus.
export default function CaseCard({ doc, index = 0 }: { doc: Doc; index?: number }) {
  return (
    <article className="case-card">
      <Link href={`/case-studies/${doc.slug}`} className="case-img" style={{ background: doc.image ? undefined : tints[index % tints.length] }} aria-label={`${doc.title} case study`}>
        {/* eslint-disable-next-line @next/next/no-img-element */}
        {doc.image && <img src={doc.image} alt="" loading="lazy" />}
        <span className="case-hover">
          <b>{doc.title}</b>
          {doc.metrics && doc.metrics.length > 0 && <ul>{doc.metrics.slice(0, 3).map((m) => <li key={m.label}><strong>{m.value}</strong> {m.label}</li>)}</ul>}
          <i>View case study</i>
        </span>
      </Link>
    </article>
  );
}
