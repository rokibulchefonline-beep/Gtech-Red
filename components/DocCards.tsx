import Link from 'next/link';
import type { Doc } from '@/lib/mongo';

export default function DocCards({ docs, base }: { docs: Doc[]; base: string }) {
  if (!docs.length) return <p className="muted">Nothing published yet. Check back soon.</p>;
  return (
    <div className="cards">
      {docs.map((d) => (
        <Link key={d.slug} className="card" href={`${base}/${d.slug}`}>
          <h3>{d.title}</h3>
          <p>{d.excerpt}</p>
        </Link>
      ))}
    </div>
  );
}
