'use client';

import Link from 'next/link';
import { useEffect, useRef, useState } from 'react';
import Icon from '@/components/Icon';
import { coreServices } from '@/lib/data';
import { groupIcons, serviceIcons } from '@/lib/icons';

export default function ServiceStack() {
  const refs = useRef<(HTMLDivElement | null)[]>([]);
  const [armed, setArmed] = useState(false);
  const [shown, setShown] = useState<boolean[]>(() => coreServices.map(() => false));

  useEffect(() => {
    setArmed(true);
    // A card is "in" once its top edge is above the middle of the screen, and goes
    // back "out" when scrolling up moves it below that line. Works in both directions.
    const io = new IntersectionObserver(
      (entries) => {
        setShown((prev) => {
          const next = [...prev];
          for (const e of entries) {
            const i = refs.current.indexOf(e.target as HTMLDivElement);
            if (i < 0 || !e.rootBounds) continue;
            next[i] = e.boundingClientRect.top < e.rootBounds.bottom;
          }
          return next;
        });
      },
      { rootMargin: '0px 0px -50% 0px', threshold: [0, 0.01, 1] },
    );
    refs.current.forEach((el) => el && io.observe(el));
    return () => io.disconnect();
  }, []);

  return (
    <div className={`svc-stack ${armed ? 'armed' : ''}`}>
      {coreServices.map((s, i) => (
        <div key={s.slug} ref={(el) => { refs.current[i] = el; }}>
        <article className={`svc-card ${shown[i] ? 'in' : ''}`}>
          <div className="svc-text">
            <span className="svc-num">0{i + 1}</span>
            <h3>{s.title}</h3>
            <p>{s.line}</p>
            <ul>{s.points.map((p) => <li key={p}>{p}</li>)}</ul>
            <Link className="svc-link" href={`/services/${s.slug}`}>Learn more &rarr;</Link>
          </div>
          <div className="svc-media">
            {s.image ? (
              // eslint-disable-next-line @next/next/no-img-element
              <img src={s.image} alt={s.title} loading="lazy" />
            ) : (
              <Icon name={serviceIcons[s.slug] ?? groupIcons[s.slug]} size={110} />
            )}
          </div>
        </article>
        </div>
      ))}
    </div>
  );
}
