'use client';

import { useCallback, useEffect, useRef, useState } from 'react';
import CaseCard from '@/components/CaseCard';
import Icon from '@/components/Icon';
import type { Doc } from '@/lib/mongo';

export default function CaseCarousel({ docs }: { docs: Doc[] }) {
  const track = useRef<HTMLDivElement>(null);
  const [edge, setEdge] = useState({ start: true, end: false });

  const update = useCallback(() => {
    const el = track.current;
    if (!el) return;
    setEdge({ start: el.scrollLeft < 4, end: el.scrollLeft + el.clientWidth >= el.scrollWidth - 4 });
  }, []);

  useEffect(() => {
    update();
    window.addEventListener('resize', update);
    return () => window.removeEventListener('resize', update);
  }, [update]);

  const move = (dir: 1 | -1) => {
    const el = track.current;
    if (el) el.scrollBy({ left: dir * el.clientWidth, behavior: 'smooth' });
  };

  return (
    <div className="case-wrap">
      <button className="case-arrow left" aria-label="Previous case studies" onClick={() => move(-1)} disabled={edge.start}>
        <Icon name="lucide:chevron-left" size={22} />
      </button>
      <div className="case-track" ref={track} onScroll={update}>
        {docs.map((d, i) => <CaseCard key={d.slug} doc={d} index={i} />)}
      </div>
      <button className="case-arrow right" aria-label="Next case studies" onClick={() => move(1)} disabled={edge.end}>
        <Icon name="lucide:chevron-right" size={22} />
      </button>
    </div>
  );
}
