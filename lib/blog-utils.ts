import { slugify } from '@/lib/util';

// Browser-safe blog helpers (no database imports), shared by server and client components.
export type Post = {
  slug: string; title: string; excerpt: string; category: string; date: string; image: string; featured: boolean; body: string;
  imageAlt?: string; format?: 'html' | 'md'; author?: string; tags?: string[]; metaTitle?: string; metaDescription?: string; canonical?: string; noindex?: boolean;
};

export const stripHtml = (h: string) => h.replace(/<[^>]+>/g, ' ').replace(/&nbsp;/g, ' ').replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/\s+/g, ' ').trim();
export const wordCount = (body: string, html = false) => ((html ? stripHtml(body) : body).match(/\S+/g) ?? []).length;
export const readTime = (body: string, html = false) => Math.max(1, Math.round(wordCount(body, html) / 220));

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

// ---- conversions between the two body formats --------------------------------------------------
const e = (t: string) => t.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
const inline = (t: string) => e(t)
  .replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>').replace(/(^|[^*])\*([^*\s][^*]*)\*/g, '$1<em>$2</em>').replace(/`([^`]+)`/g, '<code>$1</code>')
  .replace(/\[([^\]]+)\]\(([^)\s]+)\)/g, '<a href="$2">$1</a>');

/** Old Markdown post bodies, converted so they open in the visual editor. */
export function markdownToHtml(md: string): string {
  return parseBody(md).map((b) => {
    switch (b.type) {
      case 'h2': case 'h3': return `<${b.type}>${inline(b.text)}</${b.type}>`;
      case 'ul': case 'ol': return `<${b.type}>${b.items.map((i) => `<li>${inline(i)}</li>`).join('')}</${b.type}>`;
      case 'quote': return `<blockquote>${inline(b.text)}</blockquote>`;
      case 'img': return `<p><img src="${b.src}" alt="${e(b.alt)}"/></p>`;
      case 'hr': return '<hr/>';
      default: return `<p>${inline(b.text)}</p>`;
    }
  }).join('\n');
}

/** HTML reduced to the Markdown-ish shape the SEO checks understand (## headings, links, images). */
export function htmlToPseudoMd(html: string): string {
  return html
    .replace(/<h([1-6])[^>]*>([\s\S]*?)<\/h\1>/g, (_, l, t) => `\n${'#'.repeat(Math.max(2, +l))} ${stripHtml(t)}\n`)
    .replace(/<a\b[^>]*href="([^"]*)"[^>]*>([\s\S]*?)<\/a>/g, (_, h, t) => `[${stripHtml(t)}](${h})`)
    .replace(/<img\b[^>]*src="([^"]*)"[^>]*?(?:alt="([^"]*)")?[^>]*>/g, (_, s, a) => `![${a ?? ''}](${s})`)
    .replace(/<\/(p|li|div|blockquote)>/g, '\n').replace(/<(?!!\[)[^>]+>/g, ' ').replace(/&nbsp;/g, ' ').replace(/&amp;/g, '&');
}
