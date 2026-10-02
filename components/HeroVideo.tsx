'use client';

import { useEffect, useState } from 'react';

// Home hero background: a muted, looping YouTube stock video (privacy-enhanced embed, no controls).
// The iframe is added after hydration so it never blocks first paint, and fades in once playing
// to hide YouTube's loading chrome. pointer-events are off so it behaves like a background.
const ID = '4YKT1KJbzuQ';
const src = `https://www.youtube-nocookie.com/embed/${ID}?autoplay=1&mute=1&loop=1&playlist=${ID}&controls=0&disablekb=1&fs=0&iv_load_policy=3&modestbranding=1&playsinline=1&rel=0`;

export default function HeroVideo() {
  const [mounted, setMounted] = useState(false);
  const [shown, setShown] = useState(false);
  useEffect(() => setMounted(true), []);
  return (
    <div className="hero-yt" aria-hidden="true">
      {mounted && (
        <iframe
          src={src}
          title="Background video"
          tabIndex={-1}
          allow="autoplay; encrypted-media; picture-in-picture"
          className={shown ? 'on' : ''}
          onLoad={() => setTimeout(() => setShown(true), 1800)}
        />
      )}
    </div>
  );
}
