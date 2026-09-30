'use client';

import { useState } from 'react';
import { services } from '@/lib/data';

const codes = [['+44', 'UK'], ['+971', 'UAE'], ['+1', 'US'], ['+880', 'BD'], ['+91', 'IN'], ['+61', 'AU']];
const sizes = ['1 - 10', '11 - 50', '51 - 200', '201 - 500', '500+'];

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
      <h3>Speak To An Expert</h3>
      <p className="iq-note">If you have any requirement, share it with us using the form below and we will get back to you within one working day.</p>
      {msg && <div className={`alert ${msg.ok ? 'ok' : 'err'}`}>{msg.text}</div>}
      <input type="text" name="website" className="hp" tabIndex={-1} autoComplete="off" />
      <div className="iq-row">
        <label>First Name<b>*</b><input name="firstName" required placeholder="Enter Name" /></label>
        <label>Last Name<b>*</b><input name="lastName" required placeholder="Enter Last Name" /></label>
      </div>
      <div className="iq-row">
        <label>Work Email<b>*</b><input type="email" name="email" required placeholder="Enter Email" /></label>
        <label>Phone<b>*</b>
          <span className="iq-phone">
            <select name="countryCode" aria-label="Country code" defaultValue="+44">{codes.map(([c, n]) => <option key={c} value={c}>{n} {c}</option>)}</select>
            <input type="tel" name="phone" required placeholder="Phone number" />
          </span>
        </label>
      </div>
      <div className="iq-row">
        <label>Designation<b>*</b><input name="designation" required placeholder="Enter Designation" /></label>
        <label>Company Name<b>*</b><input name="company" required placeholder="Enter Company Name" /></label>
      </div>
      <label>Company Size<b>*</b>
        <select name="size" required defaultValue=""><option value="" disabled>Please Select</option>{sizes.map((s) => <option key={s}>{s}</option>)}</select>
      </label>
      <label>What Solutions Do You Need For Your Business?<b>*</b>
        <select name="service" required defaultValue=""><option value="" disabled>Please Select</option>
          {services.map((g) => <optgroup key={g.slug} label={g.title}>{g.items.map((i) => <option key={i.slug}>{i.name}</option>)}</optgroup>)}
        </select>
      </label>
      <label>Message<textarea name="message" rows={2} placeholder="Enter Message" /></label>
      <button className="btn iq-submit" type="submit" disabled={busy}>{busy ? 'Sending...' : 'Submit'}</button>
    </form>
  );
}
