import Image from 'next/image';
import Link from 'next/link';
import CookieSettingsLink from '@/components/CookieSettingsLink';
import Icon from '@/components/Icon';
import { industries, services, site as base } from '@/lib/data';
import { getPublicSettings } from '@/lib/settings';

export default async function Footer() {
  const st = await getPublicSettings();
  const site = { name: st.general.siteName, email: st.contact.email, phone: st.contact.phone,
    socials: st.socials.filter((s) => s.url).map((s) => ({ ...s, icon: base.socials.find((b) => b.name === s.name)?.icon ?? 'lucide:link' })) };
  return (
    <footer className="ft">
      <div className="wrap">
        <div className="ft-grid">
          <div className="ft-brand">
            <Link className="ft-logo" href="/"><Image src="/logo.png" alt={site.name} width={140} height={46} /></Link>
            <p>A UK digital agency for marketing, websites and software. One team, clear reporting and results you can measure.</p>
            <div className="ft-social">
              {site.socials.map((s) => <a key={s.name} href={s.url} target="_blank" rel="noopener noreferrer" aria-label={`${site.name} on ${s.name}`}><Icon name={s.icon} size={16} /></a>)}
            </div>
          </div>
          <nav className="ft-col" aria-label="Services">
            <h3>Services</h3>
            {services.map((g) => <Link key={g.slug} href={`/services/${g.slug}`}>{g.title}</Link>)}
            <Link className="ft-more" href="/services">All services <Icon name="lucide:arrow-right" size={14} /></Link>
          </nav>
          <nav className="ft-col" aria-label="Industries">
            <h3>Industries</h3>
            {industries.slice(0, 5).map((i) => <Link key={i.slug} href={`/industries/${i.slug}`}>{i.name}</Link>)}
            <Link className="ft-more" href="/industries">All industries <Icon name="lucide:arrow-right" size={14} /></Link>
          </nav>
          <nav className="ft-col" aria-label="Company">
            <h3>Company</h3>
            <Link href="/about">About Us</Link>
            <Link href="/case-studies">Case Studies</Link>
            <Link href="/blogs">Blog</Link>
            <Link href="/contact" data-page="">Contact</Link>
          </nav>
          <div className="ft-col ft-contact">
            <h3>Get in Touch</h3>
            <a href={`mailto:${site.email}`}><Icon name="lucide:mail" size={16} />{site.email}</a>
            <a href={`tel:${site.phone.replace(/\s/g, '')}`}><Icon name="lucide:phone" size={16} />{site.phone}</a>
            <span><Icon name="lucide:clock" size={16} />Mon to Fri, 9am to 6pm</span>
            <span><Icon name="lucide:map-pin" size={16} />Serving businesses across the UK</span>
          </div>
        </div>

        <div className="ft-bottom">
          <span>&copy; {new Date().getFullYear()} {site.name}. All rights reserved.</span>
          <nav aria-label="Legal"><Link href="/privacy-policy">Privacy Policy</Link><Link href="/terms">Terms</Link><Link href="/cookie-policy">Cookies</Link><CookieSettingsLink /></nav>
        </div>
      </div>
    </footer>
  );
}
