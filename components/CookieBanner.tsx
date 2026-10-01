'use client';

import Link from 'next/link';
import { useEffect, useState } from 'react';

// Cookie consent (UK GDPR / PECR). Non-essential cookies stay off until the visitor opts in.
// Choice is stored in localStorage + a first-party cookie and pushed to Google Consent Mode.
type Consent = { analytics: boolean; marketing: boolean; date: string };
const KEY = 'gtech_consent';

declare global {
  interface Window { gtag?: (...args: unknown[]) => void; dataLayer?: unknown[] }
}

function apply(c: Consent) {
  const g = (v: boolean) => (v ? 'granted' : 'denied');
  window.gtag?.('consent', 'update', {
    analytics_storage: g(c.analytics), ad_storage: g(c.marketing), ad_user_data: g(c.marketing), ad_personalization: g(c.marketing),
  });
  window.dispatchEvent(new CustomEvent('gtech-consent', { detail: c }));
}

export function openCookieSettings() {
  window.dispatchEvent(new Event('gtech-cookie-settings'));
}

export default function CookieBanner() {
  const [show, setShow] = useState(false);
  const [manage, setManage] = useState(false);
  const [analytics, setAnalytics] = useState(false);
  const [marketing, setMarketing] = useState(false);

  useEffect(() => {
    let saved: Consent | null = null;
    try { saved = JSON.parse(localStorage.getItem(KEY) ?? 'null'); } catch { /* storage blocked */ }
    if (saved) { setAnalytics(saved.analytics); setMarketing(saved.marketing); apply(saved); } else setShow(true);
    const open = () => { setManage(true); setShow(true); };
    window.addEventListener('gtech-cookie-settings', open);
    return () => window.removeEventListener('gtech-cookie-settings', open);
  }, []);

  const save = (a: boolean, m: boolean) => {
    const c: Consent = { analytics: a, marketing: m, date: new Date().toISOString() };
    try { localStorage.setItem(KEY, JSON.stringify(c)); } catch { /* storage blocked */ }
    document.cookie = `${KEY}=${a ? 1 : 0}${m ? 1 : 0}; max-age=${60 * 60 * 24 * 365}; path=/; SameSite=Lax`;
    setAnalytics(a); setMarketing(m); apply(c); setShow(false); setManage(false);
  };

  if (!show) return null;
  return (
    <div className="ck" role="dialog" aria-modal="false" aria-labelledby="ck-title">
      <p id="ck-title" className="ck-title">We value your privacy</p>
      <p className="ck-text">
        We use essential cookies to run this site and, with your permission, analytics and marketing cookies to improve it.
        See our <Link href="/cookie-policy">Cookie Policy</Link>.
      </p>
      {manage && (
        <div className="ck-prefs">
          <label className="ck-row"><span><b>Strictly necessary</b><small>Needed for the site to work. Always on.</small></span><input type="checkbox" checked disabled /></label>
          <label className="ck-row"><span><b>Analytics</b><small>Helps us understand how the site is used.</small></span><input type="checkbox" checked={analytics} onChange={(e) => setAnalytics(e.target.checked)} /></label>
          <label className="ck-row"><span><b>Marketing</b><small>Measures and improves our advertising.</small></span><input type="checkbox" checked={marketing} onChange={(e) => setMarketing(e.target.checked)} /></label>
        </div>
      )}
      <div className="ck-btns">
        {manage
          ? <button className="ck-primary" onClick={() => save(analytics, marketing)}>Save preferences</button>
          : <button className="ck-primary" onClick={() => save(true, true)}>Accept all</button>}
        <button className="ck-ghost" onClick={() => save(false, false)}>Reject non-essential</button>
        {!manage && <button className="ck-link" onClick={() => setManage(true)}>Manage preferences</button>}
      </div>
    </div>
  );
}
