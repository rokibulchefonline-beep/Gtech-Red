import { brandLogos } from '@/lib/data';

export default function BrandGrid() {
  return (
    <section className="brands">
      <div className="wrap">
        <h2>
          Experience Working with Industry
          <br />
          <span className="red">Leading Brands.</span>
        </h2>
        <div className="brand-grid">
          {brandLogos.map((b) => (
            <div className="brand-cell" key={b.name}>
              {/* eslint-disable-next-line @next/next/no-img-element */}
              <img src={b.logo} alt={b.name} loading="lazy" />
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}
