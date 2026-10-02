import Link from 'next/link';
import { formatDate, readTime, type Post } from '@/lib/blog-utils';

export default function PostCard({ p, wide = false }: { p: Post; wide?: boolean }) {
  return (
    <article className={wide ? 'bl-card wide' : 'bl-card'}>
      <Link href={`/blogs/${p.slug}`} className="bl-card-img" tabIndex={-1} aria-hidden="true">
        {/* eslint-disable-next-line @next/next/no-img-element */}
        <img src={p.image} alt="" loading="lazy" width={1200} height={675} />
      </Link>
      <div className="bl-card-body">
        <div className="bl-tags">{wide && <span className="bl-tag star">Featured</span>}<span className="bl-tag">{p.category}</span></div>
        <h3><Link href={`/blogs/${p.slug}`}>{p.title}</Link></h3>
        {wide && <p>{p.excerpt}</p>}
        <p className="bl-meta">GTech Editorial Team · {formatDate(p.date)} · {readTime(p.body)} min read</p>
        {wide && <Link className="bl-read" href={`/blogs/${p.slug}`}>Read Article</Link>}
      </div>
    </article>
  );
}
