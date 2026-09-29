import type { Metadata } from 'next';
import ContactForm from '@/components/ContactForm';
import PageHead from '@/components/PageHead';
import { site } from '@/lib/data';

export const metadata: Metadata = { title: 'Request Your Growth Proposal' };

export default async function Contact({ searchParams }: { searchParams: Promise<{ service?: string }> }) {
  const { service } = await searchParams;
  return (
    <>
      <PageHead title="Request Your Growth Proposal" sub="Fill out the form below to receive a custom proposal within 24 hours." />
      <section className="wrap block contact-grid">
        <ContactForm service={service} />
        <aside className="prose">
          <h3>Get in touch</h3>
          <p><a href={`mailto:${site.email}`}>{site.email}</a><br />{site.phone}</p>
        </aside>
      </section>
    </>
  );
}
