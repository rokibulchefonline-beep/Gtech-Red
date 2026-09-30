'use client';

import Link from 'next/link';
import { useEffect, useRef, useState } from 'react';
import Icon from '@/components/Icon';
import { coreServices } from '@/lib/data';
import { groupIcons, serviceIcons, uiIcons } from '@/lib/icons';

/**
 * Cards pin under the header (CSS `position: sticky`) and the next card slides up over
 * the previous one, so they arrive one by one and stay stable on screen. Works both
 * scrolling down and back up.
 */
export default function ServiceStack() {
  const refs = useRef<(HTMLElement | null)[]>([]);
  const [played, setPlayed] = useState<boolean[]>(() => coreServices.map(() => false));

  useEffect(() => {
    // Re-mount each animated image the first time its card is on screen, so it starts from frame 0.
    const io = new IntersectionObserver(
      (entries) => {
        setPlayed((prev) => {
          const next = [...prev];
          for (const e of entries) {
            const i = refs.current.indexOf(e.target as HTMLElement);
            if (i >= 0 && e.isIntersecting) next[i] = true;
          }
          return next;
        });
      },
      { threshold: 0.55 },
    );
    refs.current.forEach((el) => el && io.observe(el));
    return () => io.disconnect();
  }, []);

  return (
    <div className="svc-stack">
      {coreServices.map((s, i) => (
        <article
          key={s.slug}
          ref={(el) => { refs.current[i] = el; }}
          className="svc-card"
          style={{ ['--i' as string]: i }}
        >
          <div className="svc-text">
            <h3>{s.title}</h3>
            <p>{s.line}</p>
            <ul>{s.points.map((p) => <li key={p}><span className="tick"><Icon name={uiIcons.check} size={13} /></span>{p}</li>)}</ul>
            <Link className="svc-link" href={`/services/${s.slug}`}>Learn more &rarr;</Link>
          </div>
          <div className="svc-media">
            {s.image ? (
              // eslint-disable-next-line @next/next/no-img-element
              <img key={played[i] ? 'play' : 'idle'} src={s.image} alt={s.title} loading="lazy" />
            ) : (
              <Icon name={serviceIcons[s.slug] ?? groupIcons[s.slug]} size={110} />
            )}
          </div>
        </article>
      ))}
    </div>
  );
}
