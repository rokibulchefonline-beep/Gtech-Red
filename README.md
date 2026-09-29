# Gtech Red

Next.js (App Router, TypeScript) marketing site with MongoDB.

## Structure
- `/` home, `/about`, `/contact` growth-proposal form (saves to MongoDB `leads`), `/privacy-policy`, `/terms`
- `/services/{group}` and `/services/{item}` (e.g. `/services/search-engine-optimization`)
- `/industries/{industry}`
- `/blogs`, `/blogs/{slug}` (e.g. `/blogs/5-seo-tools`) – MongoDB collection `posts`
- `/case-studies`, `/case-studies/{slug}` (e.g. `/case-studies/chefonline`) – MongoDB collection `case_studies`
- `/api/health` pings MongoDB, `/api/contact` receives the form

`posts` and `case_studies` documents: `title`, `slug`, `excerpt`, `body`, `created_at`.
Menus, services, industries and budgets live in `lib/data.ts`.

## Deploy (no local setup)
1. Create a free MongoDB Atlas cluster, a database user, and allow network access (`0.0.0.0/0` for Vercel).
2. Import this repo on Vercel or Netlify. Framework: Next.js.
3. Set env vars `MONGODB_URI` and `MONGODB_DB` (see `.env.example`).
4. Open `/api/health`.

## Local
`npm install`, copy `.env.example` to `.env.local`, `npm run dev`.

## Icons
Icons come from Iconify sets (browse at https://icones.js.org): `lucide` and `simple-icons` (brands).
To add or change one: edit the id in `lib/icons.ts` (e.g. `lucide:rocket`), then run `npm run icons`.
That copies only the used icons into `lib/icon-data.ts`, so no full icon pack ships to the browser.
To use another pack: `npm i -D @iconify-json/<prefix>` and add it to `scripts/build-icons.mjs`.
