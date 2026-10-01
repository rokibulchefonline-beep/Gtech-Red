'use client';

import { openCookieSettings } from '@/components/CookieBanner';

export default function CookieSettingsLink() {
  return <button type="button" className="ftr-ck" onClick={openCookieSettings}>Cookie settings</button>;
}
