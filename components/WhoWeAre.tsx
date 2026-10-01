import Link from 'next/link';
import Icon from '@/components/Icon';
import IntroVideo from '@/components/IntroVideo';
import { partners } from '@/lib/data';
import { uiIcons } from '@/lib/icons';

const points = [
  'Strategy, design and engineering under one roof',
  'Every campaign tracked to leads and revenue',
  'Plain-English reporting, no jargon',
];

export default function WhoWeAre() {
  return (
    <section className="who">
      <div className="wrap who-grid">
        <IntroVideo />

        <div className="who-body">
          <p className="who-eyebrow">Who We Are</p>
          <h2>A <span className="red">Digital Marketing Agency</span> Built for Growth</h2>
          <p className="who-text">
            GTech Digital is a full-service digital marketing agency specialising in search marketing, advertising,
            branding, and high-performing websites and software for growth-focused businesses. We turn
            strategy into measurable revenue.
          </p>
          <ul className="who-points">
            {points.map((p) => (
              <li key={p}><span className="tick"><Icon name={uiIcons.check} size={13} /></span>{p}</li>
            ))}
          </ul>
          <div className="who-btns">
            <Link className="btn" href="/about">More about us</Link>
            <Link className="btn-line" href="/contact">Contact us</Link>
          </div>

          <div className="who-partners">
            <p>Certified partners</p>
            <div className="who-logos">
              {partners.map((b) => (
                // eslint-disable-next-line @next/next/no-img-element
                <img key={b.name} src={b.logo} alt={b.name} loading="lazy" />
              ))}
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}
