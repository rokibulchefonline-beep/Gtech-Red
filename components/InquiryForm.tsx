'use client';

import { useState } from 'react';
import Icon from '@/components/Icon';
import { services } from '@/lib/data';
import { formIcons } from '@/lib/icons';

export default function InquiryForm() {
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
        body: JSON.stringify({ ...Object.fromEntries(new FormData(form)), source: 'inquiry' }),
      });
      const data = await res.json();
      if (data.ok) { setMsg({ ok: true, text: 'Thank you. Our team will contact you shortly.' }); form.reset(); }
      else setMsg({ ok: false, text: data.error || 'Something went wrong.' });
    } catch {
      setMsg({ ok: false, text: 'Network error. Please try again.' });
    }
    setBusy(false);
  }

  return (
    <form className="iq-form" onSubmit={submit}>
      <h3>Partner with <span className="red">GTech Digital</span></h3>
      {msg && <div className={`alert ${msg.ok ? 'ok' : 'err'}`}>{msg.text}</div>}
      <input type="text" name="hp_field" className="hp" tabIndex={-1} autoComplete="off" aria-hidden="true" />

      <div className="iq-field"><Icon name={formIcons.business} size={18} />
        <input name="company" required placeholder="Business Name" aria-label="Business name" autoComplete="organization" /></div>
      <div className="iq-field"><Icon name={formIcons.person} size={18} />
        <input name="name" required placeholder="Contact Name" aria-label="Contact name" autoComplete="name" /></div>
      <div className="iq-field"><Icon name={formIcons.phone} size={18} />
        <input type="tel" name="phone" required placeholder="Phone Number" aria-label="Phone number" autoComplete="tel" /></div>
      <div className="iq-field"><Icon name={formIcons.mail} size={18} />
        <input type="email" name="email" required placeholder="Email Address" aria-label="Email address" autoComplete="email" /></div>
      <div className="iq-field"><Icon name={formIcons.pin} size={18} />
        <input name="postcode" placeholder="Post Code" aria-label="Post code" autoComplete="postal-code" /></div>
      <div className="iq-field"><Icon name={formIcons.service} size={18} />
        <select name="service" required defaultValue="" aria-label="Your required service">
          <option value="" disabled>Your required service*</option>
          {services.map((g) => <optgroup key={g.slug} label={g.title}>{g.items.map((i) => <option key={i.slug}>{i.name}</option>)}</optgroup>)}
        </select></div>

      <button className="btn iq-submit" type="submit" disabled={busy}>{busy ? 'Sending...' : 'Get my free proposal'}</button>
    </form>
  );
}
