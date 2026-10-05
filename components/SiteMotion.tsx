'use client';

import { usePathname } from 'next/navigation';
import { useEffect } from 'react';

// Premium scroll motion for the public site: below-the-fold headings and cards fade and rise in with a
// short stagger, and a thin progress bar tracks the scroll. Skipped for visitors who prefer reduced motion.
const ITEMS = '.card,.rcard,.sz-card,.sz-in,.sh-why-card,.ih-card,.bl-card,.how-step,.sp-card,.sp-step,.sp-split,.sp-media,.pstrip-tile,.cs-metric,.who-body,.who-video,.iq-copy,.iq-form,.sp-faq details,.contact-aside,.ft-grid>div';
const HEADS = 'main section:not(.hero-video):not(.sp-hero):not(.cs-hero):not(.page-hd) h2';

export default function SiteMotion() {
  const path = usePathname();
  useEffect(() => {
    if (path.startsWith('/admin') || matchMedia('(prefers-reduced-motion: reduce)').matches || !('IntersectionObserver' in window)) return;
    const vh = innerHeight;
    const els = Array.from(document.querySelectorAll<HTMLElement>(`${HEADS},${ITEMS}`)).filter((e) => !e.closest('.sstack,.case-track,.testi-stage,.brand-track'));
    const seen = new Map<Element, number>();
    const io = new IntersectionObserver((es) => es.forEach((e) => { if (e.isIntersecting) { e.target.classList.add('rv-in'); io.unobserve(e.target); } }), { threshold: 0.1, rootMargin: '0px 0px -6% 0px' });
    for (const el of els) {
      if (el.getBoundingClientRect().top < vh * 0.92) continue; // already on screen: leave as is, no flicker
      const i = seen.get(el.parentElement!) ?? 0;
      seen.set(el.parentElement!, i + 1);
      el.setAttribute('data-rv', '');
      el.style.setProperty('--rv-d', `${Math.min(i, 5) * 80}ms`);
      io.observe(el);
    }
    const bar = document.getElementById('scroll-progress');
    let raf = 0;
    const onScroll = () => { cancelAnimationFrame(raf); raf = requestAnimationFrame(() => { const h = document.documentElement.scrollHeight - innerHeight; if (bar) bar.style.transform = `scaleX(${h > 0 ? Math.min(1, scrollY / h) : 0})`; }); };
    addEventListener('scroll', onScroll, { passive: true }); onScroll();
    return () => { io.disconnect(); removeEventListener('scroll', onScroll); cancelAnimationFrame(raf); els.forEach((e) => { e.removeAttribute('data-rv'); e.classList.remove('rv-in'); }); };
  }, [path]);
  return <div id="scroll-progress" aria-hidden="true" />;
}
