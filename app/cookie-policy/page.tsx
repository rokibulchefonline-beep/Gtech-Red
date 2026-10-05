import type { Metadata } from 'next';
import { seoFor } from '@/lib/seo';
import LegalPage from '@/components/LegalPage';
import doc from '@/content/legal/cookies';

const baseMeta: Metadata = {
  title: 'Cookie Policy',
  description: doc.intro,
  alternates: { canonical: '/cookie-policy' },
};
export const generateMetadata = () => seoFor('/cookie-policy', baseMeta);

export default function CookiePolicy() {
  return <LegalPage doc={doc} path="/cookie-policy" />;
}
