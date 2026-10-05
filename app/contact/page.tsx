import type { Metadata } from 'next';
import { seoFor } from '@/lib/seo';
import Link from 'next/link';
import ContactForm from '@/components/ContactForm';
import Icon from '@/components/Icon';
import PartnerStrip from '@/components/PartnerStrip';
import FaqSection from '@/components/service/FaqSection';
import { Tick } from '@/components/service/ServicePage';
import { getPublicSettings } from '@/lib/settings';
import Schema from '@/components/Schema';
import { breadcrumbNode, faqNode, ids, pageNode } from '@/lib/schema';

const base = 'https://www.gtechdigital.co.uk';

const baseMeta: Metadata = {
  title: { absolute: 'Contact GTech Digital | Get a Free Proposal | UK Digital Agency' },
  description:
    'Contact GTech Digital for a free audit and tailored proposal for SEO, Google Ads, social media, web design or custom software. We reply within one working day.',
  alternates: { canonical: '/contact' },
};
export const generateMetadata = () => seoFor('/contact', baseMeta);

const next = [
  { title: 'We review your enquiry', text: 'A specialist looks at your website and goals.' },
  { title: 'Free strategy call', text: 'A 30-minute call to understand your needs.' },
  { title: 'Your proposal', text: 'A clear plan and fixed quote within 24 hours.' },
];

const faqs = [
  { q: 'How quickly will you reply?', a: 'We reply to every enquiry within one working day, usually much sooner during UK business hours.' },
  { q: 'Is the audit really free?', a: 'Yes. The audit and proposal are free with no obligation. We review your website and marketing and show you the biggest opportunities.' },
  { q: 'Do I need to know which service I need?', a: 'No. Tell us your goals and we will recommend the right mix of services and budget.' },
  { q: 'Can we meet in person?', a: 'Yes. We meet clients in person or by video call, whichever suits you best.' },
  { q: 'Do you have minimum contract terms?', a: 'No. Most of our services run on rolling monthly terms, and projects are priced upfront.' },
  { q: 'What information should I include?', a: 'Your website, what you want to achieve and a rough monthly budget help us prepare a useful proposal.' },
];

export default async function Contact() {
  const st = await getPublicSettings();
  const site = { name: st.general.siteName, email: st.contact.email, phone: st.contact.phone };
  const nodes = [
    pageNode({ path: '/contact', type: 'ContactPage', name: 'Contact GTech Digital', description: baseMeta.description as string, mainEntity: ids.org }),
    breadcrumbNode('/contact', [['Contact', '/contact']]),
    faqNode('/contact', faqs),
  ];

  return (
    <>
      <Schema path="/contact" nodes={nodes} />

      <section className="sp-hero compact">
        <div className="wrap sp-hero-in">
          <nav className="sp-crumbs" aria-label="Breadcrumb"><Link href="/">Home</Link><span>/</span><b>Contact</b></nav>
          <h1>Contact <span className="red">GTech Digital</span> for a Free Marketing Audit and Proposal</h1>
          <p className="sp-lead">Contact GTech Digital, a UK digital marketing, web design and software agency, for a free audit and tailored proposal within 24 hours, with no obligation and no long contracts.</p>
          <ul className="sp-hero-points">{['Reply within one working day', 'Free audit and proposal', 'No long contracts'].map((p) => <li key={p}><Tick />{p}</li>)}</ul>
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
          <h3>What happens next</h3>
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
