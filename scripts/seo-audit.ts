// Prints the SEO / AEO / GEO audit for every service and industry page.
// Run: npm run seo:audit   (add --fails to list failing checks, --page=<path> for one page)
import { auditSite } from '../lib/seo-audit';

async function main() {
const a = await auditSite();
const arg = (k: string) => process.argv.find((x) => x.startsWith(`--${k}=`))?.split('=')[1];
const only = arg('page');
console.log('SUMMARY', JSON.stringify(a.summary));
console.log('conflicts', JSON.stringify(a.conflicts), '\norphans', a.orphans.join(', ') || 'none', '\nbroken', a.broken.join(', ') || 'none');
for (const p of a.pages.sort((x, y) => x.scores.total - y.scores.total)) {
  if (only && p.path !== only) continue;
  console.log(`${String(p.scores.total).padStart(3)}  seo ${String(p.scores.seo).padStart(3)} aeo ${String(p.scores.aeo).padStart(3)} geo ${String(p.scores.geo).padStart(3)}  ${p.path}  [${p.keyword}]`);
  if (process.argv.includes('--fails') || only) for (const c of p.checks) if (c.level !== 'pass') console.log(`        ${c.level === 'fail' ? 'FAIL' : 'warn'} ${c.group} ${c.label}${c.detail ? ' | ' + c.detail : ''}`);
}
}
main();
