'use client';

import { useCallback, useEffect, useRef, useState } from 'react';
import ContactForm from '@/components/ContactForm';
import Icon from '@/components/Icon';

// Site-wide contact popup. Any link to /contact opens this form instead of navigating
// (add data-page to a link to keep normal navigation). The /contact page still works directly.
export default function ContactModal({ phone, email }: { phone: string; email: string }) {
  const site = { phone, email };
  const [open, setOpen] = useState(false);
  const [service, setService] = useState('');
  const [key, setKey] = useState(0);
  const closeBtn = useRef<HTMLButtonElement>(null);
  const lastFocus = useRef<HTMLElement | null>(null);

  const close = useCallback(() => {
    setOpen(false);
    lastFocus.current?.focus();
  }, []);

  useEffect(() => {
    const onClick = (e: MouseEvent) => {
      if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
      const a = (e.target as HTMLElement).closest?.('a[href]') as HTMLAnchorElement | null;
      if (!a || a.hasAttribute('data-page') || a.target === '_blank') return;
      const url = new URL(a.href, window.location.href);
      if (url.origin !== window.location.origin || url.pathname !== '/contact') return;
      e.preventDefault();
      lastFocus.current = a;
      setService(url.searchParams.get('service') ?? '');
      setKey((k) => k + 1);
      setOpen(true);
    };
    document.addEventListener('click', onClick, true);
    return () => document.removeEventListener('click', onClick, true);
  }, []);

  useEffect(() => {
    if (!open) return;
    const onKey = (e: KeyboardEvent) => { if (e.key === 'Escape') close(); };
    window.addEventListener('keydown', onKey);
    const prev = document.body.style.overflow;
    document.body.style.overflow = 'hidden';
    closeBtn.current?.focus();
    return () => { window.removeEventListener('keydown', onKey); document.body.style.overflow = prev; };
  }, [open, close]);

  if (!open) return null;
  return (
    <div className="cm" role="dialog" aria-modal="true" aria-labelledby="cm-title" onMouseDown={(e) => { if (e.target === e.currentTarget) close(); }}>
      <div className="cm-box">
        <button ref={closeBtn} className="cm-close" onClick={close} aria-label="Close contact form"><Icon name="lucide:x" size={22} /></button>
        <aside className="cm-side">
          <h2 id="cm-title">Get Your Free Proposal</h2>
          <p>Tell us about your goals and we will send a tailored plan and quote within 24 hours.</p>
          <ul>
            {['Free audit, no obligation', 'Reply within one working day', 'No long contracts'].map((t) => (
              <li key={t}><span className="tick"><Icon name="lucide:check" size={13} /></span>{t}</li>
            ))}
          </ul>
          <div className="cm-ways">
            <a href={`tel:${site.phone.replace(/\s/g, '')}`}><Icon name="lucide:phone" size={18} />{site.phone}</a>
            <a href={`mailto:${site.email}`}><Icon name="lucide:mail" size={18} />{site.email}</a>
          </div>
        </aside>
        <div className="cm-form">
          <ContactForm key={key} service={service} />
        </div>
      </div>
    </div>
  );
}
