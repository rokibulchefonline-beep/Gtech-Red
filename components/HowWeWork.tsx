'use client';

import { iconData } from '@/lib/icon-data';
import { howIcons } from '@/lib/icons';
import { useInView } from '@/components/useInView';

const R = '#e8202f', D = '#1b1d24';

/** Inline Iconify icon inside an SVG illustration. */
function G({ id, x, y, s, c = '#fff', w = 2 }: { id: string; x: number; y: number; s: number; c?: string; w?: number }) {
  const d = iconData[id];
  if (!d) return null;
  return (
    <svg x={x} y={y} width={s} height={s} viewBox={`0 0 ${d.w} ${d.h}`} style={{ color: c }} fill="currentColor"
      dangerouslySetInnerHTML={{ __html: d.body.replace(/stroke-width="[^"]*"/, `stroke-width="${w}"`) }} />
  );
}

const shadow = <filter id="hw-sh" x="-20%" y="-20%" width="140%" height="160%"><feDropShadow dx="0" dy="8" stdDeviation="9" floodColor="#1b1d24" floodOpacity=".16" /></filter>;

function Discover() {
  return (
    <svg viewBox="0 0 260 180" aria-hidden="true">
      <defs>{shadow}</defs>
      <rect x="30" y="34" width="152" height="112" rx="14" fill="#fff" filter="url(#hw-sh)" />
      <rect x="46" y="50" width="70" height="9" rx="4.5" fill={D} />
      {[0, 1, 2].map((i) => (
        <g key={i}>
          <circle cx="54" cy={80 + i * 22} r="8" fill={i < 2 ? R : '#ffd0d5'} />
          {i < 2 && <G id={howIcons.check} x={49} y={75 + i * 22} s={10} w={3.4} />}
          <rect x="70" y={76 + i * 22} width={92 - i * 14} height="8" rx="4" fill="#e6e8ee" />
        </g>
      ))}
      <g className="bob"><circle cx="186" cy="52" r="24" fill={R} /><G id={howIcons.search} x={174} y={40} s={24} /></g>
      <g className="bob b2"><rect x="176" y="112" width="58" height="40" rx="12" fill="#fff" filter="url(#hw-sh)" /><G id={howIcons.target} x={192} y={120} s={26} c={R} /></g>
    </svg>
  );
}

function Build() {
  return (
    <svg viewBox="0 0 260 180" aria-hidden="true">
      <defs>{shadow}</defs>
      <rect x="34" y="34" width="176" height="118" rx="14" fill={D} filter="url(#hw-sh)" />
      {[0, 1, 2].map((i) => <circle key={i} cx={50 + i * 12} cy="50" r="3.5" fill={['#ff5f57', '#febc2e', '#28c840'][i]} />)}
      {[[46, 70, 70, R], [56, 84, 96, '#8a90a0'], [56, 98, 60, '#8a90a0'], [46, 112, 84, R], [56, 126, 50, '#8a90a0']].map(([x, y, w, c], i) => (
        <rect key={i} x={x as number} y={y as number} width={w as number} height="7" rx="3.5" fill={c as string} />
      ))}
      {[0, 1, 2, 3].map((i) => <rect key={i} className="grow" style={{ animationDelay: `${i * 0.15}s` }} x={158 + i * 12} y={140 - (14 + i * 12)} width="8" height={14 + i * 12} rx="2.5" fill={i === 3 ? R : '#ffb3bb'} />)}
      <g className="bob"><circle cx="216" cy="46" r="26" fill={R} /><G id={howIcons.rocket} x={203} y={33} s={26} /></g>
      <g className="bob b2"><rect x="14" y="120" width="72" height="30" rx="15" fill="#fff" filter="url(#hw-sh)" /><circle cx="32" cy="135" r="6" fill="#16a34a" /><rect x="44" y="131" width="30" height="8" rx="4" fill="#e6e8ee" /></g>
    </svg>
  );
}

function Grow() {
  return (
    <svg viewBox="0 0 260 180" aria-hidden="true">
      <defs>{shadow}</defs>
      <rect x="92" y="14" width="112" height="152" rx="18" fill={D} filter="url(#hw-sh)" />
      <rect x="106" y="30" width="44" height="7" rx="3.5" fill="#8a90a0" />
      <text x="106" y="66" fontSize="22" fontWeight="800" fill="#fff" fontFamily="Inter, Arial, sans-serif">2,480</text>
      <text x="106" y="82" fontSize="10" fill="#8a90a0" fontFamily="Inter, Arial, sans-serif">Leads this month</text>
      <path d="M106 138 L124 124 L140 130 L160 108 L176 112 L192 92" pathLength={1} className="line" fill="none" stroke={R} strokeWidth="4" strokeLinecap="round" strokeLinejoin="round" />
      <circle cx="192" cy="92" r="5" fill="#fff" stroke={R} strokeWidth="3" />
      <rect x="106" y="146" width="80" height="6" rx="3" fill="#2c2f3a" />
      <g className="bob"><rect x="20" y="86" width="86" height="32" rx="16" fill="#fff" filter="url(#hw-sh)" /><circle cx="38" cy="102" r="9" fill={R} /><G id={howIcons.trend} x={32} y={96} s={12} w={3} /><text x="54" y="106" fontSize="11" fontWeight="700" fill={D} fontFamily="Inter, Arial, sans-serif">+128%</text></g>
      <g className="bob b2"><circle cx="212" cy="40" r="20" fill={R} /><G id={howIcons.users} x={201} y={29} s={22} /></g>
    </svg>
  );
}

const steps = [
  { art: <Discover />, title: 'Discover & Plan', text: 'We audit your website, ads and competitors, then agree clear goals and a plan built around your numbers.' },
  { art: <Build />, title: 'Build & Launch', text: 'Our team designs, develops and launches your campaigns, website or software, with fast feedback at every stage.' },
  { art: <Grow />, title: 'Measure & Grow', text: 'We track every lead and sale, report in plain English and keep improving so results compound month after month.' },
];

export default function HowWeWork() {
  const [ref, seen] = useInView<HTMLElement>(0.25);
  return (
    <section className={`how ${seen ? 'in' : ''}`} ref={ref}>
      <div className="wrap">
        <h2>How We Work</h2>
        <p className="how-sub">A simple, transparent process that takes you from first conversation to measurable growth.</p>
        <div className="how-grid">
          <svg className="how-line" viewBox="0 0 1000 200" preserveAspectRatio="none" aria-hidden="true">
            <path d="M175 120 C 250 20, 330 20, 420 92 S 570 190, 660 100 S 740 40, 830 100" pathLength={1} fill="none" stroke="#c9ccd6" strokeWidth="2" strokeDasharray="0.012 0.014" />
          </svg>
          {steps.map((s) => (
            <div className="how-step" key={s.title}>
              <div className="how-art">{s.art}</div>
              <h3>{s.title}</h3>
              <p>{s.text}</p>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}
