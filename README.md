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

## Intro video (Who We Are section)
`public/videos/intro.mp4` (30 s, 1280x720) and its poster are drawn in code by `scripts/build-intro-video.mjs`.
Needs an ffmpeg with libx264: `FFMPEG=/path/to/ffmpeg npm run intro-video` (about 1 minute).
Edit the scenes and text in that script (numbers live in `scripts/video-data.mjs`), or replace the two files with your own video and poster.

## Admin panel

Open `/admin`. On a fresh database it sends you to `/admin/setup` to create the first **super admin** (set `SETUP_KEY` first on a public site).

Required environment variables: `MONGODB_URI`, `MONGODB_DB`, `AUTH_SECRET`.

| Area | What it does |
| --- | --- |
| Page content | Edit hero text, section copy, bullets and FAQs of every service and industry page (only changed fields are stored as overrides; Reset restores the original). |
| Blog posts | Markdown editor with toolbar, live preview, autosave, image upload, scheduling, categories, tags and an SEO score. |
| Case studies | Banner, headline numbers (shown on card hover), challenge, solution, results, quote and linked services. |
| Partner badges / Client logos | Manage the partner strip, certified-partner row and the scrolling brands strip. |
| SEO manager | Per-URL title, description, canonical, social image and noindex, with a SERP preview and checks. |
| Leads | Every form submission: status pipeline, notes, assignee, value, CSV export. |
| Users and roles | Super admin only. Roles: Super admin, Admin, Editor, Sales. |
| Settings | Site details, tracking IDs, SEO defaults, SMTP email (encrypted password, test send) and the deploy hook. |

The public site is pre-rendered. After editing content, click **Publish site** to rebuild it (needs a deploy hook, see Settings > Publishing). Leads, login and the admin itself are always live.

Without `MONGODB_URI`, development (`npm run dev`) uses an in-memory store so you can try the admin; production does not.
