'use client';

import { useEffect } from 'react';

/** Fade-and-rise on scroll for [data-rv] elements. Does nothing when the visitor prefers reduced motion. */
export default function Motion() {
  useEffect(() => {
    const els = Array.from(document.querySelectorAll<HTMLElement>('.ax [data-rv]'));
    if (matchMedia('(prefers-reduced-motion: reduce)').matches || !('IntersectionObserver' in window)) { els.forEach((e) => e.classList.add('in')); return; }
    const io = new IntersectionObserver((es) => es.forEach((e) => { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } }), { threshold: 0.12 });
    els.forEach((e) => io.observe(e));
    return () => io.disconnect();
  }, []);
  return null;
}
