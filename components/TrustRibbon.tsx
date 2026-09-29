import { clients } from '@/lib/data';

export default function TrustRibbon() {
  // Two identical sets so the marquee loops seamlessly.
  const set = [...clients, ...clients];
  return (
    <section className="trust" aria-label="Our clients">
      <p className="trust-t">Trusted by growing brands</p>
      <div className="trust-track">
        {[0, 1].map((n) => (
          <div className="trust-set" key={n} aria-hidden={n === 1}>
            {set.map((c, i) => (
              <div className="trust-item" key={i}>
                {/* eslint-disable-next-line @next/next/no-img-element */}
                <img src={c.logo} alt={n === 0 ? c.name : ''} loading="lazy" />
              </div>
            ))}
          </div>
        ))}
      </div>
    </section>
  );
}
