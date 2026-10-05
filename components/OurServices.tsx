import Link from 'next/link';
import Icon from '@/components/Icon';
import Hl from '@/components/Hl';
import { Rt } from '@/components/Rt';
import { homeSection } from '@/lib/content';
import { services } from '@/lib/data';
import { groupIcons, uiIcons } from '@/lib/icons';

// Compact services overview for the home page: one card per service category.
export default async function OurServices() {
  const h = await homeSection('services');
  return (
    <section className="ourservices">
      <div className="wrap">
        <h2><Hl>{h.heading}</Hl></h2>
        <Rt as="p" className="os-sub" html={(h as { text?: string }).text ?? ''} />
        <div className="sg-grid">
          {services.map((g) => (
            <Link key={g.slug} href={`/services/${g.slug}`} className="sg-card">
              <span className="sg-ico"><Icon name={groupIcons[g.slug]} size={24} /></span>
              <h3>{g.title}</h3>
              <p>{g.items.slice(0, 3).map((it) => it.name).join(' · ')}</p>
              <span className="sg-more">View {g.items.length} services <Icon name={uiIcons.arrowRight} size={16} /></span>
            </Link>
          ))}
          <Link href="/services" className="sg-card sg-all">
            <h3>All Services</h3>
            <p>Marketing, web and software from one UK team.</p>
            <span className="sg-more">Explore everything <Icon name={uiIcons.arrowRight} size={16} /></span>
          </Link>
        </div>
      </div>
    </section>
  );
}
