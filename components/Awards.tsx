import { badges } from '@/lib/data';

export default function Awards() {
  return (
    <section className="awards">
      <div className="wrap">
        <p className="eyebrow">Recognition &amp; Partnerships</p>
        <h2>Trusted by brands. Recognised by the industry.</h2>
        <p className="awards-sub">
          Gtech is a certified partner of the platforms we run your campaigns on, and our results speak
          in the press and on review sites.
        </p>
        <div className="awards-grid">
          {badges.map((b) => (
            <div className="award" key={b.name}>
              <div className="award-img">
                {/* eslint-disable-next-line @next/next/no-img-element */}
                <img src={b.logo} alt={b.name} loading="lazy" />
              </div>
              <span>{b.label}</span>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}
