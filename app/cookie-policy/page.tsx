import type { Metadata } from 'next';
import LegalPage from '@/components/LegalPage';
import doc from '@/content/legal/cookies';

export const metadata: Metadata = {
  title: 'Cookie Policy',
  description: doc.intro,
  alternates: { canonical: '/cookie-policy' },
};

export default function CookiePolicy() {
  return <LegalPage doc={doc} />;
}
