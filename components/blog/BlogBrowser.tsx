'use client';

import Link from 'next/link';
import { useSearchParams } from 'next/navigation';
import Icon from '@/components/Icon';
import PostCard from '@/components/blog/PostCard';
import type { Post } from '@/lib/blog-utils';
import { slugify } from '@/lib/util';

// Search, category filters and the post list read ?q= and ?category= in the browser,
// so the blog page itself can be served as static HTML.

export function BlogToolsView({ cats, q = '', category = '' }: { cats: string[]; q?: string; category?: string }) {
  return (
    <>
      <form className="bl-search" action="/blogs" role="search">
        {category && <input type="hidden" name="category" value={category} />}
        <Icon name="lucide:search" size={18} />
        <label className="sr-only" htmlFor="bl-q">Search articles</label>
        <input id="bl-q" name="q" key={q} defaultValue={q} placeholder="Search articles..." />
        <button type="submit">Search</button>
      </form>
      <nav className="bl-cats" aria-label="Categories">
        <Link href="/blogs" className={!category ? 'on' : ''}>All Posts</Link>
        {cats.map((c) => <Link key={c} href={`/blogs?category=${slugify(c)}`} className={category === slugify(c) ? 'on' : ''}>{c}</Link>)}
      </nav>
    </>
  );
}

export function BlogListView({ posts, cats, q = '', category = '' }: { posts: Post[]; cats: string[]; q?: string; category?: string }) {
  const term = q.trim().toLowerCase();
  const list = posts.filter((p) => (!category || slugify(p.category) === category) && (!term || `${p.title} ${p.excerpt} ${p.category}`.toLowerCase().includes(term)));
  const filtered = Boolean(term || category);
  const featured = filtered ? undefined : list.find((p) => p.featured) ?? list[0];
  const rest = list.filter((p) => p !== featured);
  const catName = cats.find((c) => slugify(c) === category);
  return (
    <div className="bl-list">
      {filtered && (
        <p className="bl-results">
          {list.length} {list.length === 1 ? 'article' : 'articles'}{catName ? ` in ${catName}` : ''}{term ? ` for “${q}”` : ''} · <Link href="/blogs">Clear</Link>
        </p>
      )}
      {featured && <PostCard p={featured} wide />}
      {rest.length > 0 && <div className="bl-grid">{rest.map((p) => <PostCard key={p.slug} p={p} />)}</div>}
      {!list.length && <p className="bl-empty">No articles found. Try another search or browse all posts.</p>}
    </div>
  );
}

const useParams = () => { const sp = useSearchParams(); return { q: sp.get('q') ?? '', category: sp.get('category') ?? '' }; };
export function BlogTools({ cats }: { cats: string[] }) { return <BlogToolsView cats={cats} {...useParams()} />; }
export function BlogList({ posts, cats }: { posts: Post[]; cats: string[] }) { return <BlogListView posts={posts} cats={cats} {...useParams()} />; }
