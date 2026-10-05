'use client';

import { useEffect, useRef, useState } from 'react';
import Icon from '@/components/Icon';

type Item = { id: string; label: string; icon?: string };

// Sticky in-page navigation. Highlights the section currently in view as the page scrolls
// (scroll-spy) and slides the bar sideways so the active tab is always visible on small screens.
export default function TocBar({ items, label, className = '' }: { items: Item[]; label: string; className?: string }) {
  const [active, setActive] = useState(items[0]?.id ?? '');
  const bar = useRef<HTMLDivElement>(null);
  const lock = useRef(0);

  useEffect(() => {
    let raf = 0;
    const update = () => {
      raf = 0;
      if (Date.now() < lock.current) return; // a tab was just clicked: let its smooth scroll finish
      const line = 70 + (bar.current?.offsetHeight ?? 56) + 40; // header + this bar + breathing room
      let cur = items[0]?.id ?? '';
      for (const it of items) {
        const el = document.getElementById(it.id);
        if (el && el.getBoundingClientRect().top <= line) cur = it.id;
      }
      if (window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 4) cur = items[items.length - 1]?.id ?? cur;
      setActive(cur);
    };
    const on = () => { if (!raf) raf = requestAnimationFrame(update); };
    update();
    window.addEventListener('scroll', on, { passive: true });
    window.addEventListener('resize', on);
    return () => { window.removeEventListener('scroll', on); window.removeEventListener('resize', on); cancelAnimationFrame(raf); };
  }, [items]);

  // Keep the active tab centred inside the bar (scrolls the bar only, never the page).
  useEffect(() => {
    const wrap = bar.current?.querySelector<HTMLElement>('.wrap');
    const a = wrap?.querySelector<HTMLElement>('a.on');
    if (wrap && a) wrap.scrollTo({ left: a.offsetLeft - (wrap.clientWidth - a.offsetWidth) / 2, behavior: 'smooth' });
  }, [active]);

  return (
    <nav className={`sp-toc ${className}`} aria-label={label} ref={bar}><div className="wrap">
      {items.map((it) => (
        <a key={it.id} href={`#${it.id}`} className={active === it.id ? 'on' : undefined} aria-current={active === it.id ? 'true' : undefined}
          onClick={() => { setActive(it.id); lock.current = Date.now() + 900; }}>
          {it.icon && <Icon name={it.icon} size={16} />}{it.label}
        </a>
      ))}
    </div></nav>
  );
}
