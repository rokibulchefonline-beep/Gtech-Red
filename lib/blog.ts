import { demoPosts } from '@/content/posts';
import { getDb } from '@/lib/mongo';
import { slugify } from '@/lib/util';

export { formatDate, readTime } from '@/lib/blog-utils';

// Blog posts come from MongoDB (collection `posts`) when available, otherwise the demo posts.
// Fields: slug, title, excerpt, body (Markdown), category, image, date or created_at, featured.

export type { Post } from '@/lib/blog-utils';
import type { Post } from '@/lib/blog-utils';

export const author = {
  name: 'GTech Editorial Team',
  bio: 'The GTech Digital editorial team shares practical advice on SEO, paid media, social media, web design and software, written by the specialists who deliver it for UK businesses every day.',
};

const toPost = (r: Record<string, any>): Post => ({ // eslint-disable-line @typescript-eslint/no-explicit-any
  slug: r.slug, title: r.title, excerpt: r.excerpt ?? '', category: r.category ?? 'Insights',
  date: new Date(r.date ?? r.created_at ?? Date.now()).toISOString().slice(0, 10),
  image: r.image ?? '/posts/default.webp', featured: Boolean(r.featured), body: r.body ?? '',
});

export async function getPosts(): Promise<Post[]> {
  try {
    const db = await getDb();
    const rows = await db.collection('posts').find({}).sort({ created_at: -1 }).limit(200).toArray();
    if (rows.length) return rows.map(toPost);
  } catch {
    // fall back to demo posts
  }
  return demoPosts.map((p) => ({ ...p, featured: Boolean(p.featured) }));
}

export async function getPost(slug: string): Promise<Post | null> {
  return (await getPosts()).find((p) => p.slug === slug) ?? null;
}



export type Block =
  | { type: 'h2' | 'h3'; text: string; id: string }
  | { type: 'p'; text: string }
  | { type: 'ul'; items: string[] };

/** Minimal Markdown parser for post bodies: ## / ### headings, "- " bullets and paragraphs. */
export function parseBody(body: string): Block[] {
  const blocks: Block[] = [];
  for (const chunk of body.replace(/\r/g, '').split(/\n\s*\n/)) {
    let para: string[] = [];
    const flush = () => { if (para.length) blocks.push({ type: 'p', text: para.join(' ') }); para = []; };
    for (const l of chunk.split('\n').map((x) => x.trim()).filter(Boolean)) {
      if (l.startsWith('### ')) { flush(); blocks.push({ type: 'h3', text: l.slice(4), id: slugify(l.slice(4)) }); }
      else if (l.startsWith('## ')) { flush(); blocks.push({ type: 'h2', text: l.slice(3), id: slugify(l.slice(3)) }); }
      else if (l.startsWith('- ')) {
        flush();
        const last = blocks[blocks.length - 1];
        if (last?.type === 'ul') last.items.push(l.slice(2)); else blocks.push({ type: 'ul', items: [l.slice(2)] });
      } else para.push(l);
    }
    flush();
  }
  return blocks;
}
