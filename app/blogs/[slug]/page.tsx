import type { Metadata } from 'next';
import Link from 'next/link';
import { notFound } from 'next/navigation';
import Icon from '@/components/Icon';
import InquirySection from '@/components/InquirySection';
import PostCard from '@/components/blog/PostCard';
import ShareButtons from '@/components/blog/ShareButtons';
import Toc from '@/components/blog/Toc';
import { author, formatDate, getPost, getPosts, parseBody, readTime } from '@/lib/blog';
import { services, site } from '@/lib/data';
import { groupIcons } from '@/lib/icons';
import { slugify } from '@/lib/util';

type Props = { params: Promise<{ slug: string }> };

// Pre-rendered at build time and served as static HTML (no per-request rendering).
export const dynamicParams = false;

export async function generateStaticParams() {
  return (await getPosts()).map((p) => ({ slug: p.slug }));
}

const base = 'https://www.gtechdigital.co.uk';

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const p = await getPost((await params).slug);
  if (!p) return {};
  return {
    title: p.title,
    description: p.excerpt,
    alternates: { canonical: `/blogs/${p.slug}` },
    openGraph: { type: 'article', title: p.title, description: p.excerpt, images: [p.image], publishedTime: p.date, authors: [author.name] },
  };
}

// **bold** support inside paragraphs and list items.
const Rich = ({ t }: { t: string }) => <>{t.split(/(\*\*[^*]+\*\*)/).map((s, i) => (s.startsWith('**') ? <strong key={i}>{s.slice(2, -2)}</strong> : s))}</>;

export default async function PostPage({ params }: Props) {
  const p = await getPost((await params).slug);
  if (!p) notFound();
  const all = await getPosts();
  const blocks = parseBody(p.body);
  const toc = blocks.filter((b) => b.type === 'h2').map((b) => ({ id: (b as { id: string }).id, text: (b as { text: string }).text }));
  const recent = all.filter((x) => x.slug !== p.slug).slice(0, 5);
  const more = all.filter((x) => x.slug !== p.slug && x.category === p.category).concat(all.filter((x) => x.slug !== p.slug && x.category !== p.category)).slice(0, 3);
  const url = `${base}/blogs/${p.slug}`;
  const mins = readTime(p.body);

  const jsonLd = [
    { '@context': 'https://schema.org', '@type': 'BlogPosting', headline: p.title, description: p.excerpt, image: `${base}${p.image}`, datePublished: p.date, dateModified: p.date,
      mainEntityOfPage: url, articleSection: p.category, wordCount: p.body.split(/\s+/).length,
      author: { '@type': 'Organization', name: author.name, url: `${base}/about` },
      publisher: { '@type': 'Organization', name: site.name, logo: { '@type': 'ImageObject', url: `${base}/logo.png` } } },
    { '@context': 'https://schema.org', '@type': 'BreadcrumbList', itemListElement: [
      { '@type': 'ListItem', position: 1, name: 'Home', item: `${base}/` },
      { '@type': 'ListItem', position: 2, name: 'Blog', item: `${base}/blogs` },
      { '@type': 'ListItem', position: 3, name: p.title, item: url }] },
  ];

  return (
    <>
      <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: JSON.stringify(jsonLd) }} />

      <header className="bp-top"><div className="wrap bp-top-in">
        <div className="bp-top-copy">
          <nav className="sp-crumbs left" aria-label="Breadcrumb"><Link href="/">Home</Link><span>/</span><Link href="/blogs">Blog</Link><span>/</span><b>{p.category}</b></nav>
          <Link className="bl-tag" href={`/blogs?category=${slugify(p.category)}`}>{p.category}</Link>
          <h1>{p.title}</h1>
          <p className="bp-excerpt">{p.excerpt}</p>
          <div className="bp-meta">
            <span className="bp-avatar" aria-hidden="true">G</span>
            <span><b>{author.name}</b><small><Icon name="lucide:calendar-days" size={14} />{formatDate(p.date)}<Icon name="lucide:clock" size={14} />{mins} min read</small></span>
          </div>
          <ShareButtons url={url} title={p.title} />
        </div>
        <div className="bp-top-img">
          {/* eslint-disable-next-line @next/next/no-img-element */}
          <img src={p.image} alt={p.title} width={1200} height={675} />
        </div>
      </div></header>

      <div className="wrap bp-layout">
        <aside className="bp-left"><div className="bp-sticky">
          {toc.length > 0 && <Toc items={toc} />}
          <div className="bp-cta">
            <p className="bl-side-title light">Free Growth Audit</p>
            <p>Find out what is holding your website and marketing back. Free, with no obligation.</p>
            <Link className="bp-cta-btn" href="/contact">Get My Free Audit</Link>
          </div>
        </div></aside>

        <article className="bp-body">
          {blocks.map((b, i) => {
            if (b.type === 'h2') return <h2 key={i} id={b.id}>{b.text}</h2>;
            if (b.type === 'h3') return <h3 key={i} id={b.id}>{b.text}</h3>;
            if (b.type === 'ul') return <ul key={i}>{b.items.map((it) => <li key={it}><Rich t={it} /></li>)}</ul>;
            return <p key={i}><Rich t={b.text} /></p>;
          })}

          <div className="bp-share-end"><ShareButtons url={url} title={p.title} /></div>

          <section className="bp-author" aria-label="About the author">
            <span className="bp-author-logo" aria-hidden="true">G</span>
            <div>
              <p className="bp-author-label">Written by</p>
              <h2>{author.name}</h2>
              <p>{author.bio}</p>
              <div className="bp-socials">
                {site.socials.map((s) => <a key={s.name} href={s.url} target="_blank" rel="noopener noreferrer" aria-label={`GTech Digital on ${s.name}`}><Icon name={s.icon} size={16} /></a>)}
              </div>
            </div>
          </section>
        </article>

        <aside className="bp-right"><div className="bp-sticky">
          <div className="bl-box">
            <p className="bl-side-title">Recent Posts</p>
            <ul className="bp-recent">
              {recent.map((r) => (
                <li key={r.slug}><Link href={`/blogs/${r.slug}`}>
                  {/* eslint-disable-next-line @next/next/no-img-element */}
                  <img src={r.image} alt="" width={96} height={54} loading="lazy" />
                  <span><b>{r.title}</b><small>{formatDate(r.date)}</small></span>
                </Link></li>
              ))}
            </ul>
          </div>
          <div className="bl-box">
            <p className="bl-side-title">Our Services</p>
            <ul className="bp-services">
              {services.map((g) => <li key={g.slug}><Link href={`/services/${g.slug}`}><Icon name={groupIcons[g.slug]} size={18} />{g.title}</Link></li>)}
            </ul>
          </div>
        </div></aside>
      </div>

      {more.length > 0 && (
        <section className="bp-more"><div className="wrap">
          <h2>More Digital Marketing Guides</h2>
          <div className="bl-grid three">{more.map((m) => <PostCard key={m.slug} p={m} />)}</div>
        </div></section>
      )}

      <InquirySection />
    </>
  );
}
