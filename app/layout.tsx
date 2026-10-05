import Icon from '@/components/Icon';
import { uiIcons } from '@/lib/icons';
import type { Metadata } from 'next';
import Link from 'next/link';
import Header from '@/components/Header';
import Footer from '@/components/Footer';
import ContactModal from '@/components/ContactModal';
import CookieBanner from '@/components/CookieBanner';
import SiteChrome from '@/components/SiteChrome';
import { getPublicSettings } from '@/lib/settings';
import { site } from '@/lib/data';
import './globals.css';

export const metadata: Metadata = {
  title: { default: `${site.name} | ${site.tagline}`, template: `%s | ${site.name}` },
  description: site.tagline,
};

const safe = (v: string, re: RegExp) => (re.test(v) ? v : '');

export default async function RootLayout({ children }: { children: React.ReactNode }) {
  const st = await getPublicSettings();
  const gtm = safe(st.tracking.gtmId, /^GTM-[A-Z0-9]+$/), ga = safe(st.tracking.ga4Id, /^G-[A-Z0-9]+$/), px = safe(st.tracking.metaPixelId, /^\d{5,20}$/);
  const tag = [
    gtm && `(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s);j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','${gtm}');`,
    ga && `var g=document.createElement('script');g.async=true;g.src='https://www.googletagmanager.com/gtag/js?id=${ga}';document.head.appendChild(g);gtag('js',new Date());gtag('config','${ga}');`,
    px && `!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src='https://connect.facebook.net/en_US/fbevents.js';s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script');fbq('init','${px}');fbq('track','PageView');`,
  ].filter(Boolean).join('\n');
  return (
    <html lang="en">
      <head>
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossOrigin="" />
        <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
        {/* Google Consent Mode v2 defaults: everything non-essential denied until the visitor opts in. */}
        <script dangerouslySetInnerHTML={{ __html: "window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}window.gtag=gtag;gtag('consent','default',{ad_storage:'denied',ad_user_data:'denied',ad_personalization:'denied',analytics_storage:'denied',wait_for_update:500});" }} />
        {tag && <script dangerouslySetInnerHTML={{ __html: tag }} />}
      </head>
      <body>
        <SiteChrome
          header={<Header />}
          footer={<><Footer /><ContactModal phone={st.contact.phone} email={st.contact.email} /><CookieBanner /><Link className="float-talk" href="/contact"><Icon name={uiIcons.chat} size={18} /> Let&apos;s Talk</Link></>}
        >
          {children}
        </SiteChrome>
      </body>
    </html>
  );
}
