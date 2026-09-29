import type { Metadata } from 'next';
import DocCards from '@/components/DocCards';
import PageHead from '@/components/PageHead';
import { listDocs } from '@/lib/mongo';

export const metadata: Metadata = { title: 'Blog' };
export const dynamic = 'force-dynamic';

export default async function Page() {
  const docs = await listDocs('posts');
  return (
    <>
      <PageHead title="Blog" />
      <section className="wrap block"><DocCards docs={docs} base="/blogs" /></section>
    </>
  );
}
