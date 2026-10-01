'use client';

import { useRef, useState } from 'react';
import Icon from '@/components/Icon';

export default function IntroVideo() {
  const ref = useRef<HTMLVideoElement>(null);
  const [playing, setPlaying] = useState(true);

  const toggle = () => {
    const v = ref.current;
    if (!v) return;
    if (v.paused) { v.play(); setPlaying(true); } else { v.pause(); setPlaying(false); }
  };

  return (
    <div className="who-video">
      <video ref={ref} src="/videos/intro.mp4" poster="/videos/intro-poster.webp" autoPlay muted loop playsInline preload="metadata"
        aria-label="GTech Digital company introduction video" />
      <button className="who-play" onClick={toggle} aria-label={playing ? 'Pause video' : 'Play video'}>
        <Icon name={playing ? 'lucide:pause' : 'lucide:play'} size={18} />
      </button>
    </div>
  );
}
