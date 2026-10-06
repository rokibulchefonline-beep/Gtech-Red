# GTech Digital: Laravel backend and admin panel

Laravel 12 + MySQL + Filament 3. This replaces the old Next.js admin and MongoDB. The public website stays on
Next.js (Cloudflare) and reads its content from this backend when it builds.

```
Visitors ──> Next.js website (Cloudflare) ──reads at build──> Laravel API (/api/v1/query)
                    │                                                │
                    └── contact / newsletter forms ──> Laravel ──> MySQL <── Admin panel (/admin)
                                                                        └── "Publish site" ─> Cloudflare deploy hook
```

## What's in the panel (/admin)

| Area | Covers |
|---|---|
| **Website content** | Pages (edit the copy of every service, industry and main page, with `[[red]]` heading highlights), case studies, SEO overrides, partner badges, client logos and the media library |
| **Blog** | Posts (rich-text editor, featured image, SEO fields, scheduling) and categories |
| **Leads** | Form enquiries (status, notes, deal value, CSV export) and newsletter subscribers (CSV export) |
| **Settings** | Site settings (contact details, tracking IDs, SMTP email with a test button, deploy hook), users and roles |
| **Dashboard** | Stats, latest leads and the **Publish site** button |

Roles match the old admin:

| Role | Can manage |
|---|---|
| super admin | everything |
| admin | content, leads, settings |
| editor | content |
| sales | leads |

## Requirements

- PHP 8.2+ with the extensions `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `gd` or `imagick`, `intl` and `zip`.
- MySQL 8+ or MariaDB 10.6+.
- Composer 2.
- Any PHP host works: a VPS, Laravel Forge, Cloudways, or cPanel hosting with SSH access.

## Install on the server

```bash
git clone <repo> && cd <repo>/backend
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
```

Edit `.env`:

- `APP_URL`: the panel's address, e.g. `https://admin.gtechdigital.co.uk`.
- `APP_ENV=production` and `APP_DEBUG=false`.
- `DB_*`: your MySQL database and user.
- `GTECH_API_TOKEN`: a long random string, for example the output of `php -r "echo bin2hex(random_bytes(32));"`.
- `GTECH_SITE_URL`: the public website address.

Then run:

```bash
php artisan migrate --force
php artisan storage:link
php artisan gtech:seed-content        # loads every page, menu, stat, testimonial and the keyword map
php artisan gtech:create-admin you@example.com --name="Your Name"   # only if you are not importing users
php artisan optimize
```

Point the web server's document root at `backend/public`.

Some image folders in `public/` share their name with a page (`public/services/` and `/services`). Let only real
files bypass Laravel: the included `public/.htaccess` does this on Apache. On nginx use
`try_files $uri /index.php?$query_string;` (without `$uri/`).

## Move the data from MongoDB

Follow **[docs/EXPORT-FROM-MONGODB.md](docs/EXPORT-FROM-MONGODB.md)**: export to JSON, then run `php artisan gtech:import-mongo`.

## Connect the website (Cloudflare)

1. In the Cloudflare project, open **Settings → Variables and Secrets** and add:
   - `LARAVEL_API_URL`: this backend's address, e.g. `https://admin.gtechdigital.co.uk`.
   - `LARAVEL_API_TOKEN`: the same value as `GTECH_API_TOKEN`. Add it as a **Secret**.

   Add both in **Build** and in **Runtime** variables.
2. Redeploy. The website now reads content from Laravel. `/admin` on the website redirects to the new panel, and forms save into Laravel.
3. In the panel, open **Site settings → Publishing** and paste the Cloudflare deploy hook. **Publish site** then rebuilds the website.
4. Once everything checks out, you can remove `MONGODB_URI` from Cloudflare and pause the Atlas cluster.

To roll back, remove the two `LARAVEL_*` variables and redeploy. The site goes back to MongoDB.

## Where content lives (Blade move, phase 1)

All website content is in MySQL and edited in the panel. The database is the single source of truth.

| Content | Table | Panel screen |
|---|---|---|
| Main pages, services, industries and legal pages | `pages` | Website content → Pages |
| Services menu (categories and services) | `service_groups`, `service_items` | Site structure → Services menu |
| Industries list | `industries` | Site structure → Industries list |
| Home service cards | `core_services` | Site structure → Home service cards |
| Company numbers | `stats` | Site structure → Company numbers |
| Testimonials | `testimonials` | Site structure → Testimonials |
| Keyword map and internal links | `seo_keywords` | Site structure → Keyword map |
| Budgets list and company legal details | `settings` (`forms`, `company`) | Site settings |

`gtech:seed-content` loads the original copy from `database/data/content.json`, which was exported from the old
code with `npm run export:content`. It only fills what is missing and never overwrites edits, unless you pass `--force`.
**Restore original** on a page puts that single page back to its launch copy.

The Next.js website still reads pages through the same API until the Blade front end replaces it.

## Commands

| Command | What it does |
|---|---|
| `php artisan gtech:import-mongo <folder> [--dry-run] [--auth-secret=...]` | Import a MongoDB export (safe to repeat) |
| `php artisan gtech:seed-content [--force] [--no-demo]` | Load all website content (safe to repeat) |
| `php artisan gtech:create-admin <email>` | Create a super admin, or reset an existing user's password |
| `php artisan test` | Run the backend tests |

## Blade front end (in progress)

The website is being rebuilt in Blade with the same HTML, CSS and behaviour as the Next.js version.

| Phase | Status |
|---|---|
| 1. All content in MySQL | Done |
| 2. Layout and shared parts: header and mega menu, footer, contact popup, cookie banner, back-to-top, "Let's Talk", scroll motion, forms | Done. Preview at `/blade-preview` |
| 3. Page templates: every page of the website (home, about, contact, both hubs, 35 services, 10 industries, case studies, blog, legal pages, 404) and their behaviour (in-page tabs, sliders, testimonials, count-ups, charts, videos, share and newsletter) | Done. Every page's HTML matches the Next.js build |
| 4. Remaining polish, 5. SEO tags, structured data and sitemap, 6. Caching, 7. Full visual check | Next |

The Blade pages are served at the same addresses as the website (`/`, `/services/local-seo`, `/blogs`...). Until
`GTECH_BLADE_LIVE=true` they are sent with `X-Robots-Tag: noindex`, so search engines ignore them while the
Next.js website is still live. The blog search (`?q=`) and topic filter (`?category=`) run on the server.

- **Styles:** `public/css/site.css` is linked to the website's `app/globals.css`, so both front ends share one stylesheet.
- **Images and videos:** linked from the website's `public/` folder. Run `php artisan gtech:link-assets` after cloning or deploying.
- **Behaviour:** `public/js/site.js` holds plain JavaScript ports of the React components (menus, popup, forms, cookie consent, back-to-top, scroll motion).
- **Blade helpers:**
  - `@icon('lucide:check', 16)` renders the same SVG icons.
  - `@hl($heading)` renders the two-tone headings, matching the website's highlighter on all 1,010 headings in the content.
  - `@rt($html)` cleans rich text, matching the website's sanitiser on 1,618 samples.
- **Updating icons:** after adding icons to the website, run `npx tsx scripts/export-icons.ts` from the repository root.
