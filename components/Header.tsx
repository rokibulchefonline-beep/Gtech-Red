'use client';

import Icon from '@/components/Icon';
import { groupIcons, serviceIcons } from '@/lib/icons';
import Image from 'next/image';
import Link from 'next/link';
import { useState } from 'react';
import { services, site } from '@/lib/data';

export default function Header() {
  const [tab, setTab] = useState(services[0].slug);
  const [open, setOpen] = useState<string | null>(null); // mobile dropdown
  const [nav, setNav] = useState(false);
  const close = () => { setNav(false); setOpen(null); };
  const toggle = (k: string) => setOpen(open === k ? null : k);

  return (
    <header className="hdr">
      <div className="wrap hdr-in">
        <Link className="logo" href="/" onClick={close}><Image src="/logo.png" alt={site.name} width={140} height={46} priority /></Link>
        <button className="burger" aria-label="Menu" onClick={() => setNav(!nav)}>&#9776;</button>
        <nav className={`nav ${nav ? 'show' : ''}`}>
          <Link href="/" onClick={close}>Home</Link>
          <Link href="/about" onClick={close}>About Us</Link>

          <div className={`dd mega ${open === 'services' ? 'open' : ''}`}>
            <a href="#" className="dd-t" onClick={(e) => { e.preventDefault(); toggle('services'); }}>Services &#9662;</a>
            <div className="dd-panel mega-panel">
              <div className="mega-tabs">
                {services.map((g) => (
                  <Link key={g.slug} href={`/services/${g.slug}`} className={tab === g.slug ? 'on' : ''}
                    onMouseEnter={() => setTab(g.slug)} onClick={close}><Icon name={groupIcons[g.slug]} size={16} /> {g.title}</Link>
                ))}
              </div>
              <div className="mega-body">
                {services.filter((g) => g.slug === tab).map((g) => (
                  <div key={g.slug} className="mega-pane on">
                    <h4>{g.title}</h4>
                    <div className="mega-grid">
                      {g.items.map((i) => (
                        <Link key={i.slug} href={`/services/${i.slug}`} onClick={close}><i><Icon name={serviceIcons[i.slug]} size={16} /></i>{i.name}</Link>
                      ))}
                    </div>
                  </div>
                ))}
              </div>
            </div>
          </div>

          <Link href="/case-studies" onClick={close}>Case Studies</Link>
          <Link href="/blogs" onClick={close}>Blog</Link>
          <Link className="btn sm" href="/contact" onClick={close}>Contact Us</Link>
        </nav>
      </div>
    </header>
  );
}
