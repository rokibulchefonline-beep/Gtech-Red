import type { Metadata } from 'next';
import Link from 'next/link';
import Icon from '@/components/Icon';
import InquirySection from '@/components/InquirySection';
import FaqSection from '@/components/service/FaqSection';
import { Block, Tick } from '@/components/service/ServicePage';
import { services } from '@/lib/data';
import { groupIcons, serviceIcons, uiIcons } from '@/lib/icons';

// Services hub. Targets the brand + "services" query (GTech Digital services), not the
// home page's main agency keyword.
const base = 'https://www.gtechdigital.co.uk';

export const metadata: Metadata = {
  title: { absolute: 'GTech Digital Services | Marketing, Web & Software Solutions' },
  description: 'Explore every GTech Digital service: SEO, Google Ads, social media, web design, custom software and branding, delivered by one UK team.',
  alternates: { canonical: '/services' },
};

const groupInfo: Record<string, { image: string; title: string; line: string; points: string[] }> = {
  'digital-marketing': { image: '/services/dm.webp', title: 'Digital Marketing', line: 'Be found on Google and in AI answers, and turn searches into customers.', points: ['SEO, local SEO and AI search', 'Google Ads and paid media', 'Content, links and reviews'] },
  'social-media-marketing': { image: '/services/social.webp', title: 'Social Media Marketing', line: 'Content, ads and creators that grow your audience and your sales.', points: ['Content and community management', 'Paid social campaigns', 'Creators and social commerce'] },
  'web-design-development': { image: '/services/web.webp', title: 'Web Design & Development', line: 'Fast, secure websites and stores designed to convert.', points: ['UX and UI design', 'WordPress, Laravel and ecommerce', 'Speed, SEO and maintenance'] },
  'custom-software-development': { image: '/services/software.webp', title: 'Custom Software', line: 'Apps and systems built around how your business works.', points: ['Web and mobile apps', 'CRM, ERP and integrations', 'SaaS products and MVPs'] },
  'branding-strategy': { image: '/services/branding.webp', title: 'Branding & Strategy', line: 'A clear brand and a clear plan to grow it.', points: ['Brand identity and guidelines', 'Marketing advisory', 'Conversion rate optimisation'] },
};

const why = [
  { icon: 'lucide:users', title: 'One Joined-Up Team', text: 'Marketing, web and software specialists working from one plan.' },
  { icon: 'lucide:user-check', title: 'Senior-Led', text: 'A dedicated lead who knows your business.' },
  { icon: 'lucide:eye', title: 'Transparent', text: 'Fixed prices, plain-English reports, no lock-in.' },
  { icon: 'lucide:trending-up', title: 'Results Tracked', text: 'Every channel measured against leads and revenue.' },
];

const faqs = [
  { q: 'What services does GTech Digital offer?', a: 'We offer digital marketing (SEO, local and ecommerce SEO, Google Ads, paid media, content and reputation), social media marketing, web design and development, custom software and apps, and branding and strategy.' },
  { q: 'Can I use more than one service?', a: 'Yes. Most clients combine services, for example a new website with SEO and Google Ads, all managed by one team and one plan.' },
  { q: 'How do I know which service I need?', a: 'Book a free audit. We review your website, marketing and goals, then recommend the services that will make the biggest difference.' },
  { q: 'Do you work with businesses across the UK?', a: 'Yes. We work with businesses throughout the UK, meeting in person or online.' },
  { q: 'Are your services on contracts?', a: 'Ongoing services run on rolling monthly terms. Projects such as websites and software have a fixed, agreed price.' },
];

export default function ServicesHub() {
  const total = services.reduce((n, g) => n + g.items.length, 0);
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

      <section className="sp-hero">
        <div className="wrap sp-hero-in">
          <nav className="sp-crumbs" aria-label="Breadcrumb"><Link href="/">Home</Link><span>/</span><b>Services</b></nav>
          <h1>GTech Digital <span className="red">Services</span></h1>
          <p className="sp-lead">Marketing, websites and software from one UK team. Pick a service, or let us recommend the right mix for your goals and budget.</p>
          <div className="sp-hero-btns">
            <Link className="sp-btn-red" href="/contact">Book a Free Audit</Link>
            <a className="sp-btn-line" href="#digital-marketing">Explore Services</a>
          </div>
          <ul className="sp-hero-points">{[`${total}+ specialist services`, `${services.length} disciplines`, 'One joined-up team'].map((p) => <li key={p}><Tick />{p}</li>)}</ul>
          <div className="sp-hero-show">
            {/* eslint-disable-next-line @next/next/no-img-element */}
            <img src="/services/hub.webp" alt="GTech Digital results across marketing, web and software" width={800} height={600} />
            <span className="sp-float a"><Icon name="lucide:layout-grid" size={20} />All Services</span>
            <span className="sp-float b"><Icon name="lucide:trending-up" size={20} />Revenue-focused</span>
          </div>
        </div>
      </section>

      <nav className="sp-toc sh-tabs" aria-label="Service categories"><div className="wrap">
        {services.map((g) => <a key={g.slug} href={`#${g.slug}`}><Icon name={groupIcons[g.slug]} size={16} />{groupInfo[g.slug]?.title ?? g.title}</a>)}
      </div></nav>

      {services.map((g, n) => {
        const info = groupInfo[g.slug];
        const title = info?.title ?? g.title;
        const shown = g.items.slice(0, 6);
        return (
          <section key={g.slug} id={g.slug} className={`sz ${n % 2 ? 'flip alt' : ''}`}><div className="wrap sz-in">
            <div className="sz-media">
              {/* eslint-disable-next-line @next/next/no-img-element */}
              <img src={info?.image} alt={`${title} results dashboard`} loading="lazy" width={800} height={600} />
            </div>
            <div className="sz-copy">
              <span className="sz-ico"><Icon name={groupIcons[g.slug]} size={24} /></span>
              <h2>{title} Services</h2>
              <p>{info?.line ?? g.intro}</p>
              <ul className="sz-list">
                {shown.map((it) => (
                  <li key={it.slug}><Link href={`/services/${it.slug}`}><Icon name={serviceIcons[it.slug]} size={18} /><span>{it.name}</span></Link></li>
                ))}
              </ul>
              <div className="sz-btns">
                <Link className="sz-btn" href={`/services/${g.slug}`} aria-label={`View all ${title} services`}>View All {g.items.length} Services <Icon name={uiIcons.arrowRight} size={18} /></Link>
              </div>
            </div>
          </div></section>
        );
      })}

      <section className="sh-why"><div className="wrap">
        <div className="sp-head center"><h2>Why Choose GTech Digital</h2></div>
        <div className="sh-why-grid">
          {why.map((w) => <div key={w.title} className="sh-why-card"><span className="sp-card-ico solid"><Icon name={w.icon} size={22} /></span><h3>{w.title}</h3><p>{w.text}</p></div>)}
        </div>
      </div></section>

      <Block slug="services" name="GTech Digital" s={{
        type: 'steps', id: 'process', heading: 'How GTech Digital Services Work',
        steps: [
          { title: 'Discover', text: 'Your goals, market and customers.' },
          { title: 'Audit', text: 'A free review of what works today.' },
          { title: 'Recommend', text: 'The right services and budget.' },
          { title: 'Deliver', text: 'Specialists get to work.' },
          { title: 'Report', text: 'Clear monthly results.' },
          { title: 'Grow', text: 'Scale what works.' },
        ],
      }} />

      <FaqSection title="GTech Digital Services FAQs" faqs={faqs} schema />
      <InquirySection />
    </>
  );
}
