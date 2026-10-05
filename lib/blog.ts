import { demoPosts } from '@/content/posts';
import { list } from '@/lib/store';

export { formatDate, parseBody, readTime } from '@/lib/blog-utils';

// Blog posts come from MongoDB (collection `posts`, managed in Admin > Blog) when any are published,
// otherwise the demo posts. Scheduled posts go live once their date has passed (at the next build).

export type { Post } from '@/lib/blog-utils';
import type { Post } from '@/lib/blog-utils';

export const author = {
  name: 'GTech Editorial Team',
  bio: 'The GTech Digital editorial team shares practical advice on SEO, paid media, social media, web design and software, written by the specialists who deliver it for UK businesses every day.',
};

const toPost = (r: Record<string, any>): Post => ({ // eslint-disable-line @typescript-eslint/no-explicit-any
  slug: r.slug, title: r.title, excerpt: r.excerpt ?? '', category: r.category ?? 'Insights',
  date: new Date(r.date || r.createdAt || Date.now()).toISOString().slice(0, 10),
  image: r.image || '/posts/default.webp', featured: Boolean(r.featured), body: r.body ?? '', format: r.format === 'html' ? 'html' : 'md',
  imageAlt: r.imageAlt, author: r.author, tags: r.tags, metaTitle: r.metaTitle, metaDescription: r.metaDescription, canonical: r.canonical, noindex: r.noindex,
});

export async function getPosts(): Promise<Post[]> {
  try {
    const rows = await list('posts', { filter: { status: { $in: ['published', 'scheduled'] } }, limit: 300 });
    const live = rows.filter((r) => r.visibility !== 'private' && (r.status === 'published' || new Date(r.date).getTime() <= Date.now())).map(toPost);
    if (live.length) return live.sort((a, b) => b.date.localeCompare(a.date));
  } catch {
    // fall back to demo posts
  }
  return demoPosts.map((p) => ({ ...p, featured: Boolean(p.featured) }));
}

export async function getPost(slug: string): Promise<Post | null> {
  return (await getPosts()).find((p) => p.slug === slug) ?? null;
}



