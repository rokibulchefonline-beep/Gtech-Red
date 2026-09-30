import Link from 'next/link';
import Icon from '@/components/Icon';
import { partners, stats } from '@/lib/data';
import { uiIcons } from '@/lib/icons';

const points = [
  'Strategy, design and engineering under one roof',
  'Every campaign tracked to leads and revenue',
  'Plain-English reporting, no jargon',
  'A dedicated team that acts like part of yours',
];

export default function WhoWeAre() {
  return (
    <section className="who">
      <div className="wrap who-grid">
        <div className="who-media">
          {/* eslint-disable-next-line @next/next/no-img-element */}
          <img src="/about-tall.webp" alt="Gtech team, strategy and results" loading="lazy" />
          <div className="who-badge">
            <strong>{stats[0].value}{stats[0].suffix}</strong>
            <span>{stats[0].label}</span>
          </div>
        </div>

        <div className="who-body">
          <p className="who-eyebrow">Who We Are</p>
          <h2>A Digital Marketing Agency Built for Growth</h2>
          <p className="who-text">
            Gtech is a full-service digital marketing agency specializing in search marketing, advertising,
            branding, and high-performing websites and software for growth-focused businesses. We turn
            strategy into measurable revenue.
          </p>
          <ul className="who-points">
            {points.map((p) => (
              <li key={p}><span className="tick"><Icon name={uiIcons.check} size={13} /></span>{p}</li>
            ))}
          </ul>
          <Link className="btn" href="/about">More about us</Link>

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
