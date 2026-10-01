'use client';

import { useCallback, useEffect, useRef, useState } from 'react';
import CaseCard from '@/components/CaseCard';
import Icon from '@/components/Icon';
import type { Doc } from '@/lib/mongo';

// Case study slider. Controls sit below the cards: a progress bar on the left,
// previous/next on the right. Hidden when every card already fits.
export default function CaseCarousel({ docs }: { docs: Doc[] }) {
  const track = useRef<HTMLDivElement>(null);
  const [state, setState] = useState({ start: true, end: false, page: 1, pages: 1 });

  const update = useCallback(() => {
    const el = track.current;
    if (!el) return;
    const pages = Math.max(1, Math.round(el.scrollWidth / el.clientWidth));
    const end = el.scrollLeft + el.clientWidth >= el.scrollWidth - 4;
    setState({ start: el.scrollLeft < 4, end, pages, page: end ? pages : Math.min(pages, Math.round(el.scrollLeft / el.clientWidth) + 1) });
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
      <div className="case-track" ref={track} onScroll={update}>
        {docs.map((d, i) => <CaseCard key={d.slug} doc={d} index={i} />)}
      </div>
      {state.pages > 1 && (
        <div className="case-ctrl">
          <div className="case-progress" aria-hidden="true">
            <span className="case-bar"><i style={{ width: `${(state.page / state.pages) * 100}%` }} /></span>
          </div>
          <div className="case-btns">
            <button className="case-arrow" aria-label="Previous case studies" onClick={() => move(-1)} disabled={state.start}>
              <Icon name="lucide:arrow-left" size={20} />
            </button>
            <button className="case-arrow" aria-label="Next case studies" onClick={() => move(1)} disabled={state.end}>
              <Icon name="lucide:arrow-right" size={20} />
            </button>
          </div>
        </div>
      )}
    </div>
  );
}
