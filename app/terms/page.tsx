import type { Metadata } from 'next';
import PageHead from '@/components/PageHead';

export const metadata: Metadata = { title: 'Terms' };

export default function Page() {
  return (
    <>
      <PageHead title="Terms" />
      <section className="wrap block prose"><p>Replace with your terms text.</p></section>
    </>
  );
}
