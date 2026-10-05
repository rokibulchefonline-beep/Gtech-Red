import type { Metadata } from 'next';
import { seoFor } from '@/lib/seo';
import Link from 'next/link';
import ContactForm from '@/components/ContactForm';
import Icon from '@/components/Icon';
import PartnerStrip from '@/components/PartnerStrip';
import FaqSection from '@/components/service/FaqSection';
import { Tick } from '@/components/service/ServicePage';
import { getPublicSettings } from '@/lib/settings';
import { getStaticPage } from '@/lib/content';
import Hl from '@/components/Hl';
import { Rt } from '@/components/Rt';
import Schema from '@/components/Schema';
import { breadcrumbNode, faqNode, ids, pageNode } from '@/lib/schema';

const base = 'https://www.gtechdigital.co.uk';

const baseMeta: Metadata = {
  title: { absolute: 'Contact GTech Digital | Get a Free Proposal | UK Digital Agency' },
  description:
    'Contact GTech Digital for a free audit and tailored proposal for SEO, Google Ads, social media, web design or custom software. We reply within one working day.',
  alternates: { canonical: '/contact' },
};
export const generateMetadata = async () => { const c = await getStaticPage('contact'); return seoFor('/contact', { ...baseMeta, title: { absolute: c.metaTitle }, description: c.metaDescription }); };

export default async function Contact() {
  const c = await getStaticPage('contact');
  const faqs = c.faqs;
  const nextSec = c.sections.find((x) => x.id === 'next') as { heading: string; steps: { title: string; text: string }[] };
  const next = nextSec.steps;
  const st = await getPublicSettings();
  const site = { name: st.general.siteName, email: st.contact.email, phone: st.contact.phone };
  const nodes = [
    pageNode({ path: '/contact', type: 'ContactPage', name: 'Contact GTech Digital', description: c.metaDescription, mainEntity: ids.org }),
    breadcrumbNode('/contact', [['Contact', '/contact']]),
    faqNode('/contact', faqs),
  ];

  return (
    <>
      <Schema path="/contact" nodes={nodes} />

      <section className="sp-hero compact">
        <div className="wrap sp-hero-in">
          <nav className="sp-crumbs" aria-label="Breadcrumb"><Link href="/">Home</Link><span>/</span><b>Contact</b></nav>
          <h1><Hl>{c.hero.h1 ?? ''}</Hl></h1>
          <Rt as="p" className="sp-lead" html={c.hero.lead} />
          <ul className="sp-hero-points">{c.hero.points.map((p) => <li key={p}><Tick />{p}</li>)}</ul>
        </div>
      </section>

      <section id="form" className="sp-sec contact-sec"><div className="wrap contact-grid">
        <ContactForm />
        <aside className="contact-aside">
          <h3>Get in touch</h3>
          <ul className="contact-ways">
            <li><span className="sp-card-ico solid"><Icon name="lucide:phone" size={20} /></span><span><small>Call us</small><a href={`tel:${site.phone.replace(/\s/g, '')}`}>{site.phone}</a></span></li>
            <li><span className="sp-card-ico solid"><Icon name="lucide:mail" size={20} /></span><span><small>Email us</small><a href={`mailto:${site.email}`}>{site.email}</a></span></li>
            <li><span className="sp-card-ico solid"><Icon name="lucide:clock" size={20} /></span><span><small>Office hours</small>Mon to Fri, 9am to 6pm</span></li>
            <li><span className="sp-card-ico solid"><Icon name="lucide:map-pin" size={20} /></span><span><small>Where we work</small>Serving businesses across the UK</span></li>
          </ul>
          <h3>{nextSec.heading}</h3>
          <ol className="contact-next">
            {next.map((n, i) => <li key={n.title}><span>{i + 1}</span><div><b>{n.title}</b><p>{n.text}</p></div></li>)}
          </ol>
        </aside>
      </div></section>

      <PartnerStrip />

      <FaqSection title="Frequently Asked Questions About Getting in Touch" faqs={faqs} />
    </>
  );
}
