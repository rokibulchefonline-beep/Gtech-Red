import Hl from '@/components/Hl';
import BrandGrid from '@/components/BrandGrid';
import { getPartners } from '@/lib/content';

// Platform partner badges: managed in Admin > Partner badges (built-in files in public/partners/ until then).
export default async function PartnerStrip({ withClients = false }: { withClients?: boolean }) {
  const partners = await getPartners();
  return (
    <section className="pstrip" aria-label="Our partners">
      <div className="wrap">
        <h2><Hl>Our Platform Partners and Certifications</Hl></h2>
        <div className="pstrip-row">
          {partners.map((p) => {
            // eslint-disable-next-line @next/next/no-img-element
            const img = <img src={p.logo} alt={p.name} loading="lazy" />;
            return <div key={p.name} className="pstrip-tile">{p.url ? <a href={p.url} target="_blank" rel="noopener noreferrer" aria-label={p.name}>{img}</a> : img}</div>;
          })}
        </div>
      </div>
      {withClients && <BrandGrid inline />}
    </section>
  );
}
