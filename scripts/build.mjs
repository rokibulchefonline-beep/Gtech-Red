// `npm run build` entry point.
// On Cloudflare Workers Builds (WORKERS_CI=1) it produces the OpenNext bundle that
// `wrangler deploy` needs; everywhere else (local, Vercel) it is a normal `next build`.
// Force the Cloudflare build anywhere with OPENNEXT=1.
import { execSync } from 'node:child_process';

const cloudflare = process.env.WORKERS_CI === '1' || process.env.OPENNEXT === '1';
const cmd = cloudflare ? 'npx opennextjs-cloudflare build' : 'npx next build';
console.log(`> ${cmd}${cloudflare ? '  (Cloudflare Workers build)' : ''}`);
execSync(cmd, { stdio: 'inherit' });
