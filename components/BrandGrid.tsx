import { brandLogos } from '@/lib/data';

// Client logos in a slow, continuous auto-scrolling strip. Repeats are hidden from assistive tech
// so each brand is announced once. Pauses on hover and for reduced motion.
export default function BrandGrid() {
  // Two copies of the list make one loop wide enough for large screens; the track holds it twice.
  const set = [...brandLogos, ...brandLogos];
  return (
    <section className="brands">
      <div className="wrap">
        <h2>
          Experience Working with Industry
          <br />
          <span className="hl">Leading Brands.</span>
        </h2>
      </div>
      <div className="brand-marquee">
        <div className="brand-track">
          {[...set, ...set].map((b, i) => (
            <div className="brand-cell" key={`${b.name}-${i}`} aria-hidden={i >= brandLogos.length || undefined}>
              {/* eslint-disable-next-line @next/next/no-img-element */}
              <img src={b.logo} alt={i >= brandLogos.length ? '' : b.name} loading="lazy" />
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}
