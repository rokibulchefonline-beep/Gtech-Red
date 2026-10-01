import Image from 'next/image';
import Link from 'next/link';
import Icon from '@/components/Icon';
import { services, site } from '@/lib/data';

export default function Footer() {
  return (
    <footer className="ftr">
      <div className="wrap ftr-grid">
        <div className="ftr-brand">
          <Link className="logo" href="/"><Image src="/logo.png" alt={site.name} width={140} height={46} /></Link>
          <p>Digital marketing, websites and software for UK businesses, from one team.</p>
          <div className="ftr-social">
            {site.socials.map((s) => <a key={s.name} href={s.url} target="_blank" rel="noopener noreferrer" aria-label={`${site.name} on ${s.name}`}><Icon name={s.icon} size={16} /></a>)}
          </div>
        </div>
        <nav aria-label="Services"><h5>Services</h5>
          {services.map((g) => <Link key={g.slug} href={`/services/${g.slug}`}>{g.title}</Link>)}
          <Link href="/services">All Services</Link>
        </nav>
        <nav aria-label="Company"><h5>Company</h5>
          <Link href="/about">About Us</Link><Link href="/case-studies">Case Studies</Link>
          <Link href="/blogs">Blog</Link><Link href="/contact">Contact</Link>
        </nav>
        <div><h5>Get in touch</h5>
          <a href={`mailto:${site.email}`}>{site.email}</a>
          <a href={`tel:${site.phone.replace(/\s/g, '')}`}>{site.phone}</a>
          <Link className="ftr-cta" href="/contact">Get a Free Audit</Link>
        </div>
      </div>
      <div className="wrap ftr-bottom">
        <span>&copy; {new Date().getFullYear()} {site.name}. All rights reserved.</span>
        <nav aria-label="Legal"><Link href="/privacy-policy">Privacy</Link><Link href="/terms">Terms</Link><Link href="/cookie-policy">Cookies</Link></nav>
      </div>
    </footer>
  );
}
