import Hl from '@/components/Hl';
import { getClients, homeSection } from '@/lib/content';

type Logo = { name: string; logo: string };

// Fixed-seed shuffle so the two rows look mixed but render the same on every build.
function shuffle<T>(list: T[], seed = 7): T[] {
  const a = [...list];
  let s = seed;
  for (let i = a.length - 1; i > 0; i--) {
    s = (s * 1664525 + 1013904223) % 4294967296;
    const j = s % (i + 1);
    [a[i], a[j]] = [a[j], a[i]];
  }
  return a;
}

function Row({ logos, reverse }: { logos: Logo[]; reverse?: boolean }) {
  // Repeat the row until it is wide enough, then twice for a seamless loop. Repeats are hidden from assistive tech.
  const base = Array.from({ length: Math.max(1, Math.ceil(12 / logos.length)) }, () => logos).flat();
  const loop = [...base, ...base];
  return (
    <div className="brand-marquee">
      <div className={`brand-track${reverse ? ' rev' : ''}`}>
        {loop.map((b, i) => (
          <div className="brand-cell" key={`${b.name}-${i}`} aria-hidden={i >= logos.length || undefined}>
            {/* eslint-disable-next-line @next/next/no-img-element */}
            <img src={b.logo} alt={i >= logos.length ? '' : b.name} loading="lazy" />
          </div>
        ))}
      </div>
    </div>
  );
}

// Client logos in two auto-scrolling rows that move in opposite directions. Pauses on hover and for reduced motion.
export default async function BrandGrid() {
  const logos = shuffle(await getClients());
  const h = await homeSection('brands');
  const half = Math.ceil(logos.length / 2);
  const top = logos.slice(0, half), bottom = logos.slice(half).length ? logos.slice(half) : shuffle(logos, 11);
  return (
    <section className="brands">
      <div className="wrap"><h2>{h.heading.includes('[[') ? <Hl>{h.heading}</Hl> : h.heading}</h2></div>
      <Row logos={top} />
      <Row logos={bottom} reverse />
    </section>
  );
}
