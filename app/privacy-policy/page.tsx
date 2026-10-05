import type { Metadata } from 'next';
import { seoFor } from '@/lib/seo';
import LegalPage from '@/components/LegalPage';
import doc from '@/content/legal/privacy';

const baseMeta: Metadata = {
  title: 'Privacy Policy',
  description: doc.intro,
  alternates: { canonical: '/privacy-policy' },
};
export const generateMetadata = () => seoFor('/privacy-policy', baseMeta);

export default function PrivacyPolicy() {
  return <LegalPage doc={doc} path="/privacy-policy" />;
}
