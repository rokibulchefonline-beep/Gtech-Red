import type { Metadata } from 'next';
import LegalPage from '@/components/LegalPage';
import doc from '@/content/legal/privacy';

export const metadata: Metadata = {
  title: 'Privacy Policy',
  description: doc.intro,
  alternates: { canonical: '/privacy-policy' },
};

export default function PrivacyPolicy() {
  return <LegalPage doc={doc} />;
}
