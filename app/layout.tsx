import Icon from '@/components/Icon';
import { uiIcons } from '@/lib/icons';
import type { Metadata } from 'next';
import Link from 'next/link';
import Header from '@/components/Header';
import Footer from '@/components/Footer';
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
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet" />
      </head>
      <body>
        <Header />
        <main>{children}</main>
        <Footer />
        <Link className="float-talk" href="/contact"><Icon name={uiIcons.chat} size={18} /> Let&apos;s Talk</Link>
      </body>
    </html>
  );
}
