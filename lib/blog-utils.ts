import { slugify } from '@/lib/util';

// Browser-safe blog helpers (no database imports), shared by server and client components.
export type Post = {
  slug: string; title: string; excerpt: string; category: string; date: string; image: string; featured: boolean; body: string;
  imageAlt?: string; author?: string; tags?: string[]; metaTitle?: string; metaDescription?: string; canonical?: string; noindex?: boolean;
};

export const readTime = (body: string) => Math.max(1, Math.round(body.split(/\s+/).length / 220));

export const formatDate = (iso: string) =>
  new Date(iso).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });

export type Block =
  | { type: 'h2' | 'h3'; text: string; id: string }
  | { type: 'p'; text: string }
  | { type: 'ul' | 'ol'; items: string[] }
  | { type: 'quote'; text: string }
  | { type: 'img'; src: string; alt: string }
  | { type: 'hr' };

/** Minimal Markdown parser for post bodies: ## / ### headings, bullet and numbered lists, > quotes, images, --- and paragraphs. */
export function parseBody(body: string): Block[] {
  const blocks: Block[] = [];
  let para: string[] = [];
  const flush = () => { if (para.length) blocks.push({ type: 'p', text: para.join(' ') }); para = []; };
  const list = (type: 'ul' | 'ol', item: string) => {
    flush();
    const last = blocks[blocks.length - 1];
    if (last?.type === type) last.items.push(item); else blocks.push({ type, items: [item] });
  };
  for (const raw of body.replace(/\r/g, '').split('\n')) {
    const l = raw.trim();
    let m: RegExpMatchArray | null;
    if (!l) flush();
    else if (l.startsWith('### ')) { flush(); blocks.push({ type: 'h3', text: l.slice(4), id: slugify(l.slice(4)) }); }
    else if (l.startsWith('## ')) { flush(); blocks.push({ type: 'h2', text: l.slice(3), id: slugify(l.slice(3)) }); }
    else if (/^(-{3,}|\*{3,})$/.test(l)) { flush(); blocks.push({ type: 'hr' }); }
    else if ((m = l.match(/^!\[([^\]]*)\]\(([^)\s]+)\)$/))) { flush(); blocks.push({ type: 'img', alt: m[1], src: m[2] }); }
    else if (l.startsWith('> ')) { flush(); const last = blocks[blocks.length - 1]; if (last?.type === 'quote') last.text += ' ' + l.slice(2); else blocks.push({ type: 'quote', text: l.slice(2) }); }
    else if (/^[-*] /.test(l)) list('ul', l.slice(2));
    else if ((m = l.match(/^\d+\. (.*)$/))) list('ol', m[1]);
    else para.push(l);
  }
  flush();
  return blocks;
}
