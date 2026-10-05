import type { Block } from '@/lib/blog-utils';

// Inline Markdown: **bold**, *italic*, `code` and [text](url). Safe: output is React elements only.
export function Rich({ t }: { t: string }) {
  const parts = t.split(/(\*\*[^*]+\*\*|\*[^*\s][^*]*\*|`[^`]+`|\[[^\]]+\]\([^)\s]+\))/g);
  return <>{parts.map((s, i) => {
    let m: RegExpMatchArray | null;
    if (s.startsWith('**') && s.endsWith('**') && s.length > 4) return <strong key={i}>{s.slice(2, -2)}</strong>;
    if (s.startsWith('`') && s.endsWith('`') && s.length > 2) return <code key={i}>{s.slice(1, -1)}</code>;
    if (s.startsWith('*') && s.endsWith('*') && s.length > 2) return <em key={i}>{s.slice(1, -1)}</em>;
    if ((m = s.match(/^\[([^\]]+)\]\(([^)\s]+)\)$/))) {
      const href = /^(https?:\/\/|\/|mailto:|#)/i.test(m[2]) ? m[2] : '#';
      const ext = /^https?:\/\//i.test(href);
      return <a key={i} href={href} {...(ext ? { target: '_blank', rel: 'noopener noreferrer' } : {})}>{m[1]}</a>;
    }
    return s;
  })}</>;
}

/** Renders parsed post blocks. Shared by the public post page and the admin live preview. */
export default function PostBody({ blocks }: { blocks: Block[] }) {
  return <>{blocks.map((b, i) => {
    switch (b.type) {
      case 'h2': return <h2 key={i} id={b.id}>{b.text}</h2>;
      case 'h3': return <h3 key={i} id={b.id}>{b.text}</h3>;
      case 'ul': return <ul key={i}>{b.items.map((it, n) => <li key={n}><Rich t={it} /></li>)}</ul>;
      case 'ol': return <ol key={i}>{b.items.map((it, n) => <li key={n}><Rich t={it} /></li>)}</ol>;
      case 'quote': return <blockquote key={i}><Rich t={b.text} /></blockquote>;
      case 'img': return <figure key={i}>{/* eslint-disable-next-line @next/next/no-img-element */}<img src={b.src} alt={b.alt} loading="lazy" />{b.alt && <figcaption>{b.alt}</figcaption>}</figure>;
      case 'hr': return <hr key={i} />;
      default: return <p key={i}><Rich t={b.text} /></p>;
    }
  })}</>;
}
