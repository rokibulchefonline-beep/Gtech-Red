# Blade parity checks

These scripts check that the Laravel Blade front end matches the Next.js website.

They need Playwright with Chromium (`npm i -g playwright && npx playwright install chromium`). It is not a dependency
of the website, so it never affects the Cloudflare build.

## Setup

1. Run both sites with the same content:
   - Next.js: `npm run build && npx next start -p 3130`
   - Laravel: `cd backend && php artisan migrate:fresh --force && php artisan gtech:seed-content && php artisan serve --port=8090`
2. If they run elsewhere, set `NEXT_URL` and `BLADE_URL`.

## Checks

| Script | What it checks |
|---|---|
| `python3 scripts/parity/compare-html.py next.html blade.html` | The page content (`<main>`) of two saved pages is the same HTML, after normalising attribute order, whitespace and entities |
| `python3 scripts/parity/compare-seo.py next.html blade.html` | Title, meta tags, canonical and every JSON-LD node match (allowing the deliberate fixes: absolute URLs and extra Open Graph tags) |
| `node scripts/parity/behaviour.mjs [filter]` | The same interactions on both sites leave the same state: tabs and tables of contents while scrolling, slider controls, testimonials, count-ups, service stack, charts, videos, share, newsletter, blog search, forms, popup and FAQs. `scenarios.mjs` holds the scenarios |
| `node scripts/parity/visual.mjs urls.txt out-dir` | Full-page screenshots of every page at 1352, 900 and 390 px compared pixel by pixel (animated images, videos and logos hidden on both sides). Differing pages are saved to `out-dir` |
| `node scripts/parity/errors.mjs urls.txt` | Every page of the Blade site loads without JS errors or broken requests (`urls.txt`: one path per line) |
