import Icon from '@/components/Icon';
import { uiIcons } from '@/lib/icons';
import type { Metadata } from 'next';
import Link from 'next/link';
import Header from '@/components/Header';
import Footer from '@/components/Footer';
import ContactModal from '@/components/ContactModal';
import CookieBanner from '@/components/CookieBanner';
import { site } from '@/lib/data';
import './globals.css';

export const metadata: Metadata = {
  title: { default: `${site.name} | ${site.tagline}`, template: `%s | ${site.name}` },
  description: site.tagline,
};

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="en">
      <head>
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossOrigin="" />
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
        {/* Google Consent Mode v2 defaults: everything non-essential denied until the visitor opts in. */}
        <script dangerouslySetInnerHTML={{ __html: "window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}window.gtag=gtag;gtag('consent','default',{ad_storage:'denied',ad_user_data:'denied',ad_personalization:'denied',analytics_storage:'denied',wait_for_update:500});" }} />
      </head>
      <body>
        <Header />
        <main>{children}</main>
        <Footer />
        <ContactModal />
        <CookieBanner />
        <Link className="float-talk" href="/contact"><Icon name={uiIcons.chat} size={18} /> Let&apos;s Talk</Link>
      </body>
    </html>
  );
}
