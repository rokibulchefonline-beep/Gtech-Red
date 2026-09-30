'use client';

import Icon from '@/components/Icon';
import { useInView } from '@/components/useInView';
import { uiIcons } from '@/lib/icons';

const YEAR = new Date().getFullYear();
const years = [YEAR - 4, YEAR - 3, YEAR - 2, YEAR - 1, YEAR];

function Axis({ ticks }: { ticks: string[] }) {
  return (
    <>
      {ticks.map((t, i) => (
        <g key={t}>
          <line x1="46" x2="392" y1={20 + i * 34} y2={20 + i * 34} stroke="#ececf0" />
          <text x="0" y={24 + i * 34} className="ax">{t}</text>
        </g>
      ))}
    </>
  );
}

const XLabels = () => (
  <>{years.map((y, i) => <text key={y} x={66 + i * 74} y="176" className="ax" textAnchor="middle">{y}</text>)}</>
);

function Bars({ vals }: { vals: number[] }) {
  return (
    <>
      {vals.map((v, i) => {
        const h = 132 * v;
        return (
          <rect key={i} className="bar" style={{ transitionDelay: `${i * 0.12}s` }} x={44 + i * 74} y={156 - h}
            width="44" height={h} rx="4" fill={i === vals.length - 1 ? '#e8202f' : '#ffd0d5'} />
        );
      })}
    </>
  );
}

export default function Results() {
  const [ref, seen] = useInView<HTMLElement>(0.2);
  const line = [0.08, 0.3, 0.38, 0.5, 0.62, 0.9];
  const pts = line.map((v, i) => [46 + (346 * i) / (line.length - 1), 156 - v * 132]);
  const d = pts.map(([x, y], i) => `${i ? 'L' : 'M'}${x} ${y}`).join(' ');

  return (
    <section className={`results ${seen ? 'in' : ''}`} ref={ref}>
      <div className="wrap">
        <h2>Tired of Marketing Agencies That Explain Poor Results <span className="red">Instead of Fixing Them?</span></h2>
        <p className="results-sub"><strong>See what better growth looks like</strong> with Gtech</p>

        <div className="results-grid">
          <article className="rcard">
            <h3><span className="red">312%</span> average return on ad spend</h3>
            <p>Data-driven campaigns and clear tracking turn your budget into measurable revenue.</p>
            <div className="rchart">
              <div className="rlabel"><b>Average ROAS</b><i>Last 5 years</i></div>
              <svg viewBox="0 0 400 190" role="img" aria-label="Return on ad spend rising over five years">
                <Axis ticks={['400%', '300%', '200%', '100%', '0']} />
                <XLabels />
                <path d={`${d} L392 156 L46 156 Z`} fill="#e8202f" className="area" />
                <path d={d} pathLength={1} className="draw" fill="none" stroke="#e8202f" strokeWidth="4" strokeLinecap="round" strokeLinejoin="round" />
                <circle cx={pts[5][0]} cy={pts[5][1]} r="7" className="dot" fill="#fff" stroke="#e8202f" strokeWidth="4" />
              </svg>
            </div>
          </article>

          <article className="rcard">
            <h3><span className="red">3x</span> more qualified leads</h3>
            <p>Sharper targeting and better landing pages bring buyers, not just clicks.</p>
            <div className="rchart">
              <div className="rlabel"><b>Lead growth</b><i>Last 5 years</i></div>
              <svg viewBox="0 0 400 190" role="img" aria-label="Qualified leads growing each year">
                <Axis ticks={['300', '200', '100', '50', '0']} />
                <XLabels />
                <Bars vals={[0.16, 0.26, 0.38, 0.52, 0.92]} />
              </svg>
            </div>
          </article>

          <article className="rcard">
            <h3><span className="red">185%</span> traffic growth</h3>
            <p>SEO and content that compound month after month, so visibility keeps rising.</p>
            <div className="rchart">
              <div className="rlabel"><b>Website traffic</b><i>Last 5 years</i></div>
              <svg viewBox="0 0 400 190" role="img" aria-label="Website traffic climbing">
                <defs>
                  <marker id="arw" viewBox="0 0 10 10" refX="6" refY="5" markerWidth="5" markerHeight="5" orient="auto-start-reverse">
                    <path d="M0 0 L10 5 L0 10 Z" fill="#111" />
                  </marker>
                </defs>
                <Axis ticks={['20K', '15K', '10K', '5K', '0']} />
                <XLabels />
                <Bars vals={[0.12, 0.18, 0.26, 0.38, 0.95]} />
                <path d="M30 150 C 130 148, 250 120, 372 26" pathLength={1} className="draw arrow" fill="none" stroke="#111" strokeWidth="6" strokeLinecap="round" markerEnd="url(#arw)" />
              </svg>
            </div>
          </article>

          <article className="rcard">
            <h3><span className="red">96%</span> client retention</h3>
            <p>Clients stay because we deliver results and report on them honestly, every month.</p>
            <div className="rchart ring-wrap">
              <svg viewBox="0 0 140 140" className="ring" role="img" aria-label="96 percent client retention">
                <circle cx="70" cy="70" r="56" fill="none" stroke="#ffe0e4" strokeWidth="14" />
                <circle cx="70" cy="70" r="56" fill="none" stroke="#e8202f" strokeWidth="14" strokeLinecap="round"
                  pathLength={100} className="ring-arc" transform="rotate(-90 70 70)" />
                <text x="70" y="80" textAnchor="middle" className="ring-num">96%</text>
              </svg>
              <div className="ring-side">
                <div className="stars">{[0, 1, 2, 3, 4].map((n) => <Icon key={n} name={uiIcons.star} size={22} />)}</div>
                <b>4.9 / 5</b>
                <span>average client rating</span>
                <div className="avatars">
                  {['#e8202f', '#b0122c', '#161616', '#ff7b89'].map((c, n) => <i key={c} style={{ background: c, zIndex: 4 - n }}>{'GTCH'[n]}</i>)}
                  <em>150+ brands trust us</em>
                </div>
              </div>
            </div>
          </article>
        </div>
      </div>
    </section>
  );
}
