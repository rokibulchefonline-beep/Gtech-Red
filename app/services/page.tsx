import type { Metadata } from 'next';
import Link from 'next/link';
import Icon from '@/components/Icon';
import InquirySection from '@/components/InquirySection';
import FaqSection from '@/components/service/FaqSection';
import { Tick } from '@/components/service/ServicePage';
import { services } from '@/lib/data';
import { groupIcons, uiIcons } from '@/lib/icons';

// Services hub. Targets the brand + "services" query (GTech Digital services), not the
// home page's main agency keyword.
const base = 'https://www.gtechdigital.co.uk';

export const metadata: Metadata = {
  title: { absolute: 'GTech Digital Services | Marketing, Web & Software Solutions' },
  description: 'Explore every GTech Digital service: SEO, Google Ads, social media, web design, custom software and branding, delivered by one UK team.',
  alternates: { canonical: '/services' },
};

const groupInfo: Record<string, { image: string; title: string; points: string[] }> = {
  'digital-marketing': { image: '/services/dm.webp', title: 'Digital Marketing', points: ['SEO, local SEO and AI search', 'Google Ads and paid media', 'Content, links and reviews'] },
  'social-media-marketing': { image: '/services/social.webp', title: 'Social Media Marketing', points: ['Content and community management', 'Paid social campaigns', 'Creators and social commerce'] },
  'web-design-development': { image: '/services/web.webp', title: 'Web Design & Development', points: ['UX and UI design', 'WordPress, Laravel and ecommerce', 'Speed, SEO and maintenance'] },
  'custom-software-development': { image: '/services/software.webp', title: 'Custom Software Development', points: ['Web and mobile apps', 'CRM, ERP and integrations', 'SaaS products and MVPs'] },
  'branding-strategy': { image: '/services/branding.webp', title: 'Branding & Strategy', points: ['Brand identity and guidelines', 'Marketing advisory', 'Conversion rate optimisation'] },
};

const faqs = [
  { q: 'What services does GTech Digital offer?', a: 'We offer digital marketing (SEO, local and ecommerce SEO, Google Ads, paid media, content and reputation), social media marketing, web design and development, custom software and apps, and branding and strategy.' },
  { q: 'Can I use more than one service?', a: 'Yes. Most clients combine services, for example a new website with SEO and Google Ads, all managed by one team and one plan.' },
  { q: 'How do I know which service I need?', a: 'Book a free audit. We review your website, marketing and goals, then recommend the services that will make the biggest difference.' },
  { q: 'Do you work with businesses across the UK?', a: 'Yes. We work with businesses throughout the UK, meeting in person or online.' },
  { q: 'Are your services on contracts?', a: 'Ongoing services run on rolling monthly terms. Projects such as websites and software have a fixed, agreed price.' },
];

export default function ServicesHub() {
  const jsonLd = [
    { '@context': 'https://schema.org', '@type': 'CollectionPage', name: 'GTech Digital Services', url: `${base}/services`,
      hasPart: services.map((g) => ({ '@type': 'ItemList', name: g.title, itemListElement: g.items.map((it, i) => ({ '@type': 'ListItem', position: i + 1, name: it.name, url: `${base}/services/${it.slug}` })) })) },
    { '@context': 'https://schema.org', '@type': 'BreadcrumbList', itemListElement: [
      { '@type': 'ListItem', position: 1, name: 'Home', item: `${base}/` },
      { '@type': 'ListItem', position: 2, name: 'Services', item: `${base}/services` }] },
  ];

  return (
    <>
      <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: JSON.stringify(jsonLd) }} />

      <section className="sp-hero compact">
        <div className="wrap sp-hero-in">
          <nav className="sp-crumbs" aria-label="Breadcrumb"><Link href="/">Home</Link><span>/</span><b>Services</b></nav>
          <p className="sp-hero-eyebrow">What we do</p>
          <h1>GTech Digital <span className="red">Services</span></h1>
          <p className="sp-lead">Marketing, websites and software from one UK team. Choose a service below, or let us recommend the right mix for your goals.</p>
          <nav className="sv-jump" aria-label="Service categories">
            {services.map((g) => <a key={g.slug} href={`#${g.slug}`}><Icon name={groupIcons[g.slug]} size={18} />{groupInfo[g.slug]?.title ?? g.title}</a>)}
          </nav>
        </div>
      </section>

      {services.map((g, n) => {
        const info = groupInfo[g.slug];
        return (
          <section key={g.slug} id={g.slug} className={`sv-group ${n % 2 ? 'alt' : ''}`}><div className="wrap">
            <div className="sv-head">
              <p className="sv-eyebrow">{info?.title ?? g.title}</p>
              <h2>{info?.title ?? g.title} Services</h2>
              <p>{g.intro}</p>
            </div>
            <div className={`sv-grid ${n % 2 ? 'flip' : ''}`}>
              <article className="sv-main">
                <div className="sv-main-img">
                  {/* eslint-disable-next-line @next/next/no-img-element */}
                  <img src={info?.image} alt={`${g.title} results dashboard`} loading="lazy" width={800} height={600} />
                </div>
                <div className="sv-main-body">
                  <p className="sv-label">Core service</p>
                  <h3>{info?.title ?? g.title}</h3>
                  <ul>{info?.points.map((p) => <li key={p}><Tick />{p}</li>)}</ul>
                  <Link className="sv-btn" href={`/services/${g.slug}`}>View Service <Icon name={uiIcons.arrowRight} size={16} /></Link>
                </div>
              </article>
              <div className={`sv-items ${g.items.length > 4 ? 'two' : ''}`}>
                {g.items.map((it) => (
                  <Link key={it.slug} href={`/services/${it.slug}`} className="sv-item">
                    <h3>{it.name}</h3>
                    <p>{it.blurb}</p>
                    <span>Learn more <Icon name={uiIcons.arrowRight} size={14} /></span>
                  </Link>
                ))}
              </div>
            </div>
          </div></section>
        );
      })}

      <FaqSection title="GTech Digital Services, Explained" faqs={faqs} schema />
      <InquirySection />
    </>
  );
}
