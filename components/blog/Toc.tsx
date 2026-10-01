'use client';

import { useEffect, useState } from 'react';

// Table of contents that highlights the section currently in view.
export default function Toc({ items }: { items: { id: string; text: string }[] }) {
  const [active, setActive] = useState(items[0]?.id);
  useEffect(() => {
    const els = items.map((i) => document.getElementById(i.id)).filter(Boolean) as HTMLElement[];
    const io = new IntersectionObserver((entries) => {
      const vis = entries.filter((e) => e.isIntersecting).sort((a, b) => a.boundingClientRect.top - b.boundingClientRect.top);
      if (vis[0]) setActive(vis[0].target.id);
    }, { rootMargin: '-100px 0px -65% 0px' });
    els.forEach((el) => io.observe(el));
    return () => io.disconnect();
  }, [items]);
  return (
    <nav className="bl-toc" aria-label="Table of contents">
      <p className="bl-side-title">Table of Contents</p>
      <ol>{items.map((i) => <li key={i.id} className={i.id === active ? 'on' : ''}><a href={`#${i.id}`}>{i.text}</a></li>)}</ol>
    </nav>
  );
}
