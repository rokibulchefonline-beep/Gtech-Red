import type { Metadata } from 'next';
import Link from 'next/link';
import Icon from '@/components/Icon';
import InquirySection from '@/components/InquirySection';
import FaqSection from '@/components/service/FaqSection';
import { Block, Tick } from '@/components/service/ServicePage';
import { services } from '@/lib/data';
import { groupIcons, serviceIcons, uiIcons } from '@/lib/icons';
import TocBar from '@/components/TocBar';
import Hl from '@/components/Hl';

// Services hub. Targets the brand + "services" query (GTech Digital services), not the
// home page's main agency keyword.
const base = 'https://www.gtechdigital.co.uk';

export const metadata: Metadata = {
  title: { absolute: 'GTech Digital Services | Marketing, Web & Software Solutions' },
  description: 'Explore every GTech Digital service: SEO, Google Ads, social media, web design, custom software and branding, delivered by one UK team.',
  alternates: { canonical: '/services' },
};

const groupInfo: Record<string, { image: string; title: string; h2: string; line: string; points: string[]; cards: string[] }> = {
  'digital-marketing': { image: '/services/dm.webp', title: 'Digital Marketing', h2: 'Digital Marketing Services: SEO, Google Ads and Content', line: 'Be found on Google and in AI answers, and turn searches into customers.', points: ['SEO, local SEO and AI search', 'Google Ads and paid media', 'Content, links and reviews'],
    cards: ['search-engine-optimization', 'local-seo', 'ecommerce-seo', 'google-ads', 'content-marketing', 'reputation-management'] },
  'social-media-marketing': { image: '/services/social.webp', title: 'Social Media Marketing', h2: 'Social Media Marketing Services for Facebook, Instagram, LinkedIn and TikTok', line: 'Content, ads and creators that grow your audience and your sales.', points: ['Content and community management', 'Paid social campaigns', 'Creators and social commerce'],
    cards: ['facebook-marketing', 'instagram-marketing', 'linkedin-marketing', 'tiktok-marketing', 'pinterest-marketing', 'paid-media'] },
  'web-design-development': { image: '/services/web.webp', title: 'Web Design & Development', h2: 'Web Design and Development Services: WordPress, Ecommerce and Laravel', line: 'Fast, secure websites and stores designed to convert.', points: ['UX and UI design', 'WordPress, Laravel and ecommerce', 'Speed, SEO and maintenance'],
    cards: ['website-design', 'ecommerce-development', 'wordpress-development', 'laravel-development', 'cms-development', 'website-maintenance'] },
  'custom-software-development': { image: '/services/software.webp', title: 'Custom Software', h2: 'Custom Software Development Services: Apps, CRM and SaaS', line: 'Apps and systems built around how your business works.', points: ['Web and mobile apps', 'CRM, ERP and integrations', 'SaaS products and MVPs'],
    cards: ['web-application-development', 'mobile-app-development', 'api-system-integration', 'crm-erp-development', 'saas-product-development', 'mvp-development'] },
  'branding-strategy': { image: '/services/branding.webp', title: 'Branding & Strategy', h2: 'Branding and Strategy Services: Identity, Advisory and CRO', line: 'A clear brand and a clear plan to grow it.', points: ['Brand identity and guidelines', 'Marketing advisory', 'Conversion rate optimisation'],
    cards: ['branding', 'marketing-advisory', 'conversion-rate-optimization', 'website-design', 'content-marketing', 'reputation-management'] },
};

// Every service, so a category grid can include closely related services from other categories.
const allItems = Object.fromEntries(services.flatMap((g) => g.items.map((it) => [it.slug, it])));

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
          <h1>Marketing, Web and Software Services of <span className="red">GTech Digital</span></h1>
          <p className="sp-lead">GTech Digital is a UK digital agency offering {total}+ services across digital marketing, social media marketing, web design and development, custom software development and branding, all delivered by one joined-up team.</p>
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

      <TocBar className="sh-tabs" label="Service categories" items={services.map((g) => ({ id: g.slug, label: groupInfo[g.slug]?.title ?? g.title, icon: groupIcons[g.slug] }))} />

      {services.map((g, n) => {
        const info = groupInfo[g.slug];
        const title = info?.title ?? g.title;
        const shown = (info?.cards ?? g.items.map((it) => it.slug)).map((slug) => allItems[slug]).filter(Boolean).slice(0, 6);
        return (
          <section key={g.slug} id={g.slug} className={`sz ${n % 2 ? 'flip alt' : ''}`}><div className="wrap"><div className="sz-in">
              <div className="sz-media">
                {/* eslint-disable-next-line @next/next/no-img-element */}
                <img src={info?.image} alt={`${title} results dashboard`} loading="lazy" width={800} height={600} />
              </div>
              <div className="sz-copy">
                <span className="sz-ico"><Icon name={groupIcons[g.slug]} size={24} /></span>
                <h2><Hl>{info?.h2 ?? `${title} Services`}</Hl></h2>
                <p>{info?.line ?? g.intro}</p>
                <ul className="sz-points">{info?.points.map((p) => <li key={p}><Tick />{p}</li>)}</ul>
                <div className="sz-btns">
                  <Link className="sz-btn" href={`/services/${g.slug}`} aria-label={`View all ${title} services`}>View All {g.items.length} Services <Icon name={uiIcons.arrowRight} size={18} /></Link>
                </div>
              </div>
            </div>
            <div className="sz-grid">
              {shown.map((it) => (
                <Link key={it.slug} href={`/services/${it.slug}`} className="sz-card">
                  <span className="sz-card-ico"><Icon name={serviceIcons[it.slug]} size={22} /></span>
                  <h3>{it.name}</h3>
                  <p>{it.blurb}</p>
                  <span className="sz-card-more">Learn more <Icon name={uiIcons.arrowRight} size={16} /></span>
                </Link>
              ))}
            </div>
          </div></section>
        );
      })}

      <section className="sh-why"><div className="wrap">
        <div className="sp-head center"><h2><Hl>Why Businesses Choose Our Digital Agency</Hl></h2></div>
        <div className="sh-why-grid">
          {why.map((w) => <div key={w.title} className="sh-why-card"><span className="sp-card-ico solid"><Icon name={w.icon} size={22} /></span><h3>{w.title}</h3><p>{w.text}</p></div>)}
        </div>
      </div></section>

      <Block slug="services" name="GTech Digital" s={{
        type: 'steps', id: 'process', heading: 'How Our Services Work, Step by Step',
        steps: [
          { title: 'Discover', text: 'Your goals, market and customers.' },
          { title: 'Audit', text: 'A free review of what works today.' },
          { title: 'Recommend', text: 'The right services and budget.' },
          { title: 'Deliver', text: 'Specialists get to work.' },
          { title: 'Report', text: 'Clear monthly results.' },
          { title: 'Grow', text: 'Scale what works.' },
        ],
      }} />

      <FaqSection title="Frequently Asked Questions About Our Services" faqs={faqs} schema />
      <InquirySection />
    </>
  );
}
