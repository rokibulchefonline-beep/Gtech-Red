import type { Metadata } from 'next';
import Link from 'next/link';
import Icon from '@/components/Icon';
import Newsletter from '@/components/blog/Newsletter';
import PostCard from '@/components/blog/PostCard';
import { getPosts } from '@/lib/blog';
import { slugify } from '@/lib/util';

export const dynamic = 'force-dynamic';

export const metadata: Metadata = {
  title: { absolute: 'GTech Digital Blog | SEO, Marketing, Web & Software Insights' },
  description: 'Practical guides on SEO, Google Ads, social media, web design and software from the GTech Digital team, written for UK businesses.',
  alternates: { canonical: '/blogs' },
};

type Props = { searchParams: Promise<{ q?: string; category?: string }> };

export default async function Blog({ searchParams }: Props) {
  const { q = '', category = '' } = await searchParams;
  const all = await getPosts();
  const cats = [...new Set(all.map((p) => p.category))];
  const term = q.trim().toLowerCase();
  const posts = all.filter((p) => (!category || slugify(p.category) === category) && (!term || `${p.title} ${p.excerpt} ${p.category}`.toLowerCase().includes(term)));
  const filtered = Boolean(term || category);
  const featured = filtered ? undefined : posts.find((p) => p.featured) ?? posts[0];
  const rest = posts.filter((p) => p !== featured);
  const catName = cats.find((c) => slugify(c) === category);

  return (
    <>
      <section className="bl-hero-wrap"><div className="wrap">
        <div className="bl-hero">
          <p className="bl-pill">GTech Digital Blog</p>
          <h1>Growth Guides for UK Businesses</h1>
          <p>Practical advice on SEO, paid ads, social media, websites and software from the specialists who do the work every day.</p>
          <form className="bl-search" action="/blogs" role="search">
            {category && <input type="hidden" name="category" value={category} />}
            <Icon name="lucide:search" size={18} />
            <label className="sr-only" htmlFor="bl-q">Search articles</label>
            <input id="bl-q" name="q" defaultValue={q} placeholder="Search articles..." />
            <button type="submit">Search</button>
          </form>
          <nav className="bl-cats" aria-label="Categories">
            <Link href="/blogs" className={!category ? 'on' : ''}>All Posts</Link>
            {cats.map((c) => <Link key={c} href={`/blogs?category=${slugify(c)}`} className={category === slugify(c) ? 'on' : ''}>{c}</Link>)}
          </nav>
        </div>
      </div></section>

      <section className="wrap bl-main">
        <div className="bl-list">
          {filtered && (
            <p className="bl-results">
              {posts.length} {posts.length === 1 ? 'article' : 'articles'}{catName ? ` in ${catName}` : ''}{term ? ` for “${q}”` : ''} · <Link href="/blogs">Clear</Link>
            </p>
          )}
          {featured && <PostCard p={featured} wide />}
          {rest.length > 0 && <div className="bl-grid">{rest.map((p) => <PostCard key={p.slug} p={p} />)}</div>}
          {!posts.length && <p className="bl-empty">No articles found. Try another search or browse all posts.</p>}
        </div>

        <aside className="bl-side">
          <div className="bl-box">
            <p className="bl-side-title">Popular Posts</p>
            <ol className="bl-popular">
              {all.slice(0, 4).map((p, i) => <li key={p.slug}><span>{i + 1}</span><Link href={`/blogs/${p.slug}`}>{p.title}</Link></li>)}
            </ol>
          </div>
          <Newsletter />
          <div className="bl-box">
            <p className="bl-side-title">Browse Topics</p>
            <ul className="bl-topics">
              {cats.map((c) => <li key={c}><Link href={`/blogs?category=${slugify(c)}`}>{c}<span>{all.filter((p) => p.category === c).length}</span></Link></li>)}
            </ul>
          </div>
        </aside>
      </section>
    </>
  );
}
