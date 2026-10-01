'use client';

import { useState } from 'react';
import Icon from '@/components/Icon';

export default function Newsletter() {
  const [msg, setMsg] = useState<{ ok: boolean; text: string } | null>(null);
  async function submit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    const form = e.currentTarget;
    try {
      const res = await fetch('/api/subscribe', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(Object.fromEntries(new FormData(form))) });
      const data = await res.json();
      setMsg(data.ok ? { ok: true, text: 'Thanks for subscribing.' } : { ok: false, text: data.error });
      if (data.ok) form.reset();
    } catch { setMsg({ ok: false, text: 'Network error. Try again.' }); }
  }
  return (
    <div className="bl-news">
      <p className="bl-side-title light"><Icon name="lucide:mail" size={18} /> Monthly Growth Tips</p>
      <p>Practical SEO, ads and web advice from our specialists, delivered once a month.</p>
      <form onSubmit={submit}>
        <input type="text" name="website" tabIndex={-1} autoComplete="off" className="hp" aria-hidden="true" />
        <label className="sr-only" htmlFor="nl-email">Email address</label>
        <input id="nl-email" name="email" type="email" required placeholder="your@email.com" />
        <button type="submit">Subscribe Free</button>
      </form>
      {msg && <p className={msg.ok ? 'bl-ok' : 'bl-err'}>{msg.text}</p>}
    </div>
  );
}
