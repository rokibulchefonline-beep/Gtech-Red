import Link from 'next/link';
import Icon from '@/components/Icon';
import IntroVideo from '@/components/IntroVideo';
import { homeSection } from '@/lib/content';
import Hl from '@/components/Hl';
import { Rt } from '@/components/Rt';
import { uiIcons } from '@/lib/icons';

export default async function WhoWeAre() {
  const h = await homeSection('who');
  const points = h.bullets ?? [];
  return (
    <section className="who">
      <div className="wrap who-grid">
        <IntroVideo />

        <div className="who-body">
          <h2>{h.heading.includes('[[') ? <Hl>{h.heading}</Hl> : h.heading}</h2>
          <Rt as="p" className="who-text" html={h.paras?.[0] ?? ''} />
          <ul className="who-points">
            {points.map((p) => (
              <li key={p}><span className="tick"><Icon name={uiIcons.check} size={13} /></span><Rt html={p} /></li>
            ))}
          </ul>
          <div className="who-btns">
            <Link className="btn" href="/about">More about us</Link>
            <Link className="btn-line" href="/contact">Contact us</Link>
          </div>
        </div>
      </div>
    </section>
  );
}
