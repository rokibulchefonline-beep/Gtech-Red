import type { Metadata } from 'next';
import { seoFor } from '@/lib/seo';
import LegalPage from '@/components/LegalPage';
import doc from '@/content/legal/terms';

const baseMeta: Metadata = {
  title: 'Terms and Conditions',
  description: doc.intro,
  alternates: { canonical: '/terms' },
};
export const generateMetadata = () => seoFor('/terms', baseMeta);

export default function Terms() {
  return <LegalPage doc={doc} path="/terms" />;
}
