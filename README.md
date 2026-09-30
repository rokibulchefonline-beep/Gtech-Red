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

## Service visuals (animated WebP)
The six home-page service visuals are generated from code: `scripts/visuals-scenes.mjs` (one function per scene)
and `scripts/visuals-lib.mjs` (helpers). Edit a scene, then run `npm run visuals` (about 4 minutes) to re-render
`public/services/*.webp` (3.6 s loop, 20 fps). `node scripts/build-service-visuals.mjs --only=seo` renders one.
The numbers inside the visuals (for example +240%) are illustrative. Change or remove them before launch.

## Case study images
The demo case-study covers in `public/case/*.webp` are drawn in code by `scripts/build-case-images.mjs`
(`npm run case-images`, a few seconds). Real case studies in MongoDB can set `image` to any photo URL.

## Inquiry section background
`public/cta-bg.webp` (the dark laptop backdrop behind the home-page inquiry form) is drawn by
`scripts/build-cta-bg.mjs` (`npm run cta-bg`). Replace the file with any photo to use your own.

## Who We Are image
`public/about-tall.webp` (the tall image beside the Who We Are text) is drawn by `scripts/build-about-image.mjs`
(`npm run about-image`). Replace the file with a real team photo (portrait, about 3:4) when you have one.
