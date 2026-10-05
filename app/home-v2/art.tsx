// Illustrations for the Home v2 preview: flat red charts, node graphs and soft orbs, drawn as inline SVG.
const R = '#e8202f', D = '#b0122c', P = '#ffd0d6', I = '#1c1c1c';

export function BarsArt() {
  const h = [38, 62, 48, 84, 70, 110];
  return (
    <svg viewBox="0 0 280 160" role="img" aria-label="Bar chart rising over six months">
      <g stroke="#1c1c1c14">{[40, 80, 120].map((y) => <line key={y} x1="16" x2="264" y1={y} y2={y} />)}</g>
      {h.map((v, i) => <rect key={i} x={28 + i * 40} y={140 - v} width="24" height={v} fill={i > 3 ? R : P} rx="3" />)}
      <path d="M40 112 L80 92 L120 100 L160 62 L200 74 L248 30" fill="none" stroke={I} strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round" />
      <circle cx="248" cy="30" r="6" fill="#fff" stroke={R} strokeWidth="3" />
    </svg>
  );
}

export function NodesArt() {
  const n: [number, number, number][] = [[140, 80, 16], [52, 40, 9], [60, 124, 9], [226, 38, 10], [230, 120, 9], [140, 22, 7]];
  return (
    <svg viewBox="0 0 280 160" role="img" aria-label="Connected systems">
      <g stroke={R} strokeWidth="1.5" opacity=".55">{n.slice(1).map(([x, y], i) => <line key={i} x1="140" y1="80" x2={x} y2={y} />)}<line x1="52" y1="40" x2="140" y2="22" /><line x1="226" y1="38" x2="230" y2="120" /></g>
      {n.map(([x, y, r], i) => <circle key={i} cx={x} cy={y} r={r} fill={i === 0 ? R : '#fff'} stroke={R} strokeWidth="2" />)}
      <circle cx="140" cy="80" r="26" fill="none" stroke={R} strokeWidth="1" strokeDasharray="3 5" />
    </svg>
  );
}

export function OrbsArt() {
  return (
    <svg viewBox="0 0 280 160" role="img" aria-label="Brand shapes">
      <defs>
        <radialGradient id="o1" cx="35%" cy="30%"><stop offset="0" stopColor="#fff" /><stop offset=".45" stopColor="#ff7a86" /><stop offset="1" stopColor={D} /></radialGradient>
        <radialGradient id="o2" cx="35%" cy="30%"><stop offset="0" stopColor="#fff" /><stop offset="1" stopColor={P} /></radialGradient>
      </defs>
      <circle cx="104" cy="84" r="52" fill="url(#o1)" /><circle cx="186" cy="62" r="34" fill="url(#o2)" /><circle cx="214" cy="116" r="20" fill={R} opacity=".85" />
      <circle cx="52" cy="38" r="10" fill={P} />
    </svg>
  );
}

export function CodeArt() {
  return (
    <svg viewBox="0 0 280 160" role="img" aria-label="Software build">
      <rect x="28" y="22" width="224" height="116" rx="10" fill="#fff" stroke="#1c1c1c14" />
      {[0, 1, 2].map((i) => <circle key={i} cx={46 + i * 14} cy="40" r="4" fill={i === 0 ? R : '#e6e6e6'} />)}
      {[[44, 62, 90, R], [58, 78, 120, '#d9d9d9'], [58, 94, 70, '#d9d9d9'], [44, 110, 100, R]].map(([x, y, w, c], i) => <rect key={i} x={x as number} y={y as number} width={w as number} height="8" rx="4" fill={c as string} />)}
      <rect x="176" y="62" width="60" height="58" rx="8" fill={P} /><path d="M192 104 L206 86 L216 96 L228 76" fill="none" stroke={R} strokeWidth="3" strokeLinecap="round" strokeLinejoin="round" />
    </svg>
  );
}

/** Hero product mock-up: a browser card with stat tiles and a chart. */
export function Dashboard() {
  return (
    <div className="ax-dash" aria-hidden="true">
      <div className="ax-dash-bar"><i /><i /><i /><span>gtechdigital.co.uk / growth</span></div>
      <div className="ax-dash-body">
        <div className="ax-dash-tiles">
          {[['+150%', 'Organic traffic'], ['+90%', 'Leads'], ['£320k', 'Revenue influenced']].map(([v, l]) => <div key={l}><b>{v}</b><span>{l}</span></div>)}
        </div>
        <div className="ax-dash-chart"><BarsArt /></div>
      </div>
    </div>
  );
}
