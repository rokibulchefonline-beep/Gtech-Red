import type { Metadata } from 'next';
import Link from 'next/link';
import PageHead from '@/components/PageHead';

export const metadata: Metadata = { title: 'About Us' };

export default function About() {
  return (
    <>
      <PageHead title="About Us" sub="Marketing, web and software specialists." />
      <section className="wrap block prose">
        <h2>Who we are</h2>
        <p>Replace this text with your company story, team and values.</p>
        <h2>What we believe</h2>
        <ul><li>Results over vanity metrics</li><li>Clear reporting</li><li>Long-term partnerships</li></ul>
        <Link className="btn" href="/contact">Work with us</Link>
      </section>
    </>
  );
}
