import type { Metadata } from 'next';
import PageHead from '@/components/PageHead';

export const metadata: Metadata = { title: 'Privacy Policy' };

export default function Page() {
  return (
    <>
      <PageHead title="Privacy Policy" />
      <section className="wrap block prose"><p>Replace with your privacy policy text.</p></section>
    </>
  );
}
