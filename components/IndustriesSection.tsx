import Link from 'next/link';
import Icon from '@/components/Icon';
import { industries } from '@/lib/data';
import { industryIcons } from '@/lib/icons';
import Hl from '@/components/Hl';

import { homeSection } from '@/lib/content';
import { Rt } from '@/components/Rt';

export default async function IndustriesSection() {
  const h = await homeSection('industries');
  return (
    <section className="ind">
      <div className="wrap ind-grid">
        <div className="ind-copy">
          <h2><Hl>{h.heading}</Hl></h2>
          <span className="ind-rule" />
          <Rt as="p" html={h.paras?.[0] ?? ''} />
          <Link className="btn" href="/contact">Speak to our experts</Link>
        </div>
        <div className="ind-cards">
          {industries.map((i) => (
            <Link key={i.slug} href={`/industries/${i.slug}`} className="ind-card">
              <span className="ind-ico"><Icon name={industryIcons[i.slug]} size={26} /></span>
              <b>{i.name}</b>
            </Link>
          ))}
        </div>
      </div>
    </section>
  );
}
