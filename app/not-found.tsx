import Link from 'next/link';
import PageHead from '@/components/PageHead';

export default function NotFound() {
  return (
    <>
      <PageHead title="Page not found" />
      <section className="wrap block"><Link className="btn" href="/">Back home</Link></section>
    </>
  );
}
