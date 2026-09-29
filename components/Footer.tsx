import Link from 'next/link';
import { industries, services, site } from '@/lib/data';

export default function Footer() {
  return (
    <footer className="ftr">
      <div className="wrap ftr-grid">
        <div>
          <Link className="logo" href="/">{site.name}</Link>
          <p>{site.tagline}</p>
          <p><a href={`mailto:${site.email}`}>{site.email}</a><br />{site.phone}</p>
        </div>
        <div><h5>Services</h5>
          {services.map((g) => <Link key={g.slug} href={`/services/${g.slug}`}>{g.title}</Link>)}
        </div>
        <div><h5>Industries</h5>
          {industries.map((i) => <Link key={i.slug} href={`/industries/${i.slug}`}>{i.name}</Link>)}
        </div>
        <div><h5>Company</h5>
          <Link href="/about">About Us</Link><Link href="/case-studies">Case Studies</Link>
          <Link href="/blogs">Blog</Link><Link href="/contact">Contact Us</Link>
          <Link href="/privacy-policy">Privacy Policy</Link><Link href="/terms">Terms</Link>
        </div>
      </div>
      <div className="wrap copy">&copy; {new Date().getFullYear()} {site.name}. All rights reserved.</div>
    </footer>
  );
}
