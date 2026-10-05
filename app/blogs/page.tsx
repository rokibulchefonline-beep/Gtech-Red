import type { Metadata } from 'next';
import Link from 'next/link';
import { Suspense } from 'react';
import Newsletter from '@/components/blog/Newsletter';
import { BlogList, BlogListView, BlogTools, BlogToolsView } from '@/components/blog/BlogBrowser';
import { getPosts } from '@/lib/blog';
import { slugify } from '@/lib/util';

export const metadata: Metadata = {
  title: { absolute: 'GTech Digital Blog | SEO, Marketing, Web & Software Insights' },
  description: 'Practical guides on SEO, Google Ads, social media, web design and software from the GTech Digital team, written for UK businesses.',
  alternates: { canonical: '/blogs' },
};

export default async function Blog() {
  const all = await getPosts();
  const cats = [...new Set(all.map((p) => p.category))];

  return (
    <>
      <section className="bl-hero-wrap"><div className="wrap">
        <div className="bl-hero">
          <h1>Digital Marketing Blog of <span className="hl">GTech Digital</span></h1>
          <p>The GTech Digital blog shares practical guides on SEO, AI search, Google Ads, social media marketing, web design and custom software, written by our UK specialists.</p>
          <Suspense fallback={<BlogToolsView cats={cats} />}><BlogTools cats={cats} /></Suspense>
        </div>
      </div></section>

      <section className="wrap bl-main">
        <Suspense fallback={<BlogListView posts={all} cats={cats} />}><BlogList posts={all} cats={cats} /></Suspense>

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
