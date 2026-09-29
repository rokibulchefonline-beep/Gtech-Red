'use client';

import { useState } from 'react';
import { budgets, services } from '@/lib/data';

export default function ContactForm({ service = '' }: { service?: string }) {
  const [msg, setMsg] = useState<{ ok: boolean; text: string } | null>(null);
  const [busy, setBusy] = useState(false);

  async function submit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    const form = e.currentTarget;
    setBusy(true);
    try {
      const res = await fetch('/api/contact', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(Object.fromEntries(new FormData(form))),
      });
      const data = await res.json();
      if (data.ok) {
        setMsg({ ok: true, text: 'Thanks. We will send your proposal within 24 hours.' });
        form.reset();
      } else {
        setMsg({ ok: false, text: data.error || 'Something went wrong.' });
      }
    } catch {
      setMsg({ ok: false, text: 'Network error. Try again.' });
    }
    setBusy(false);
  }

  return (
    <form onSubmit={submit} className="form">
      {msg && <div className={`alert ${msg.ok ? 'ok' : 'err'}`}>{msg.text}</div>}
      <input type="text" name="website" className="hp" tabIndex={-1} autoComplete="off" />
      <label>Name * <input name="name" required placeholder="John Smith" /></label>
      <label>Business Name * <input name="business" required placeholder="Your Company Ltd" /></label>
      <label>Email * <input type="email" name="email" required placeholder="john@company.com" /></label>
      <label>Phone * <input type="tel" name="phone" required placeholder="07123 456789" /></label>
      <label>What do you need? *
        <select name="service" required defaultValue={service}>
          <option value="">Select required service...</option>
          {services.map((g) => (
            <optgroup key={g.slug} label={g.title}>
              {g.items.map((i) => <option key={i.slug}>{i.name}</option>)}
            </optgroup>
          ))}
        </select>
      </label>
      <label>Monthly budget *
        <select name="budget" required defaultValue="">
          <option value="">Select estimated monthly budget...</option>
          {budgets.map((b) => <option key={b}>{b}</option>)}
        </select>
      </label>
      <label>Message <textarea name="message" rows={5} /></label>
      <button className="btn" type="submit" disabled={busy}>{busy ? 'Sending...' : 'Request Proposal'}</button>
    </form>
  );
}
