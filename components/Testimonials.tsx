'use client';

import { useEffect, useState } from 'react';
import Icon from '@/components/Icon';
import { testimonials } from '@/lib/data';
import { uiIcons } from '@/lib/icons';

export default function Testimonials() {
  const [i, setI] = useState(0);
  const [paused, setPaused] = useState(false);

  useEffect(() => {
    if (paused) return;
    const t = setInterval(() => setI((n) => (n + 1) % testimonials.length), 9000);
    return () => clearInterval(t);
  }, [paused]);

  return (
    <section className="testi" onMouseEnter={() => setPaused(true)} onMouseLeave={() => setPaused(false)}>
      <div className="wrap">
        <h2>What Clients Say About GTech Digital</h2>
        <Icon className="testi-ico" name={uiIcons.quote} size={44} />
        <div className="testi-stage">
          {testimonials.map((t, n) => (
            <blockquote key={t.name} className={n === i ? 'on' : ''} aria-hidden={n !== i}>
              <h3>{t.title}</h3>
              <p>{t.text}</p>
              <cite>{t.name}</cite>
            </blockquote>
          ))}
        </div>
        <div className="testi-dots" role="tablist" aria-label="Choose testimonial">
          {testimonials.map((t, n) => (
            <button key={t.name} role="tab" aria-selected={n === i} aria-label={`Testimonial ${n + 1}`}
              className={n === i ? 'on' : ''} onClick={() => setI(n)} />
          ))}
        </div>
      </div>
    </section>
  );
}
