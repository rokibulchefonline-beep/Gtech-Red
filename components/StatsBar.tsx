'use client';

import { useEffect, useState } from 'react';
import { stats } from '@/lib/data';
import { useInView } from '@/components/useInView';

function Count({ to, decimals = 0, run }: { to: number; decimals?: number; run: boolean }) {
  const [v, setV] = useState(0);
  useEffect(() => {
    if (!run) return;
    let raf = 0;
    const t0 = performance.now(), dur = 2800;
    const tick = (t: number) => {
      const p = Math.min(1, (t - t0) / dur);
      setV(to * (1 - Math.pow(1 - p, 3)));
      if (p < 1) raf = requestAnimationFrame(tick);
    };
    raf = requestAnimationFrame(tick);
    return () => cancelAnimationFrame(raf);
  }, [run, to]);
  return <>{v.toFixed(decimals)}</>;
}

export default function StatsBar({ hero = false }: { hero?: boolean }) {
  const [ref, seen] = useInView<HTMLElement>(0.4);
  return (
    <section className={hero ? 'stats stats-hero' : 'stats'} ref={ref} aria-label="GTech Digital in numbers">
      <div className="wrap stats-grid">
        {stats.map((s) => (
          <div className="stat" key={s.label}>
            <strong>
              <Count to={s.value} decimals={s.decimals} run={seen} />
              {s.suffix}
            </strong>
            <span>{s.label}</span>
          </div>
        ))}
      </div>
    </section>
  );
}
