import { partners } from '@/lib/data';

export default function WhoWeAre() {
  return (
    <section className="who">
      <div className="wrap">
        <p className="who-eyebrow">Who We Are</p>
        <h2>A Digital Marketing Agency Built for Growth</h2>
        <p className="who-text">
          Gtech is a full-service digital marketing agency specializing in search marketing, advertising,
          branding, and high-performing websites and software for growth-focused businesses. We turn
          strategy into measurable revenue.
        </p>
        <div className="who-logos">
          {partners.map((b) => (
            // eslint-disable-next-line @next/next/no-img-element
            <img key={b.name} src={b.logo} alt={b.name} loading="lazy" />
          ))}
        </div>
      </div>
    </section>
  );
}
