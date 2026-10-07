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
| **Leads** | Form enquiries with owner, follow-up date, timeline and source; newsletter subscribers (CSV export) |
| **Settings** | Site settings (contact details, tracking IDs, SMTP email with a test button, deploy hook), users and roles |
| **Dashboard** | Stats, latest leads and the **Publish site** button |

**Users and roles** (Settings → Users, Settings → Roles). Each role is a set of permissions per section (view,
create, edit, publish, delete, export…), editable in the panel. Built-in roles:

| Role | Can do |
|---|---|
| super admin | everything (cannot be edited) |
| admin | everything except users and roles |
| editor | all content, blog and SEO, including publishing |
| author | write blog drafts and edit only their own posts; media |
| SEO manager | SEO overrides, redirects, page copy, post SEO fields, analytics |
| sales manager | all leads and subscribers, exports, analytics |
| sales | view and work the leads |
| viewer | read-only access to every section |

- **Invites:** add a user with *Send an invitation* on; they get an email link (valid 24 hours) to set their own
  password. *Resend invitation* is on the user's row.
- **Sign-in protection:** 8 wrong passwords lock that account for 15 minutes. Every sign-in, failure and lock is
  logged and shown on the user's page with their last sign-in.
- **Two-factor (2FA):** everyone can turn it on in **My account** (avatar menu), with any authenticator app.
  Site settings → Security can require it for managers or for everyone; they are asked to set it up at next sign-in.
  My account also changes name, email and password and can sign out other browsers.

**Analytics** (Analytics in the menu): first-party and cookieless, so no consent banner is needed. It records every
visit to the Blade website and shows where it came from: search engines (Google, Bing, Yahoo, DuckDuckGo…), AI
assistants (ChatGPT, Perplexity, Claude, Gemini, Copilot, Grok…), social, email, referral sites, paid campaigns
(`utm_*`, `gclid`, `fbclid`…) and direct. Pages, landing pages, referrers, campaigns, devices, countries and leads per
source are reported; AI crawler hits are counted separately, and staff browsers are excluded once they sign in.
It only collects data once the Blade site is live.

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

## Switching the website to Blade

1. Point the website's domain at this Laravel app (document root `backend/public`). Keep `APP_URL` on the address
   the panel should use.
2. In `.env` set `GTECH_BLADE_LIVE=true`. This removes the `noindex` header and opens `robots.txt` with the sitemap.
3. Run `php artisan optimize`.
4. Check a few pages, then submit `https://www.gtechdigital.co.uk/sitemap.xml` in Google Search Console.
5. The Cloudflare (Next.js) project and its "Publish site" deploy hook are no longer needed. Content edits show on the
   site straight away.

All page addresses stay the same, so no redirects are needed beyond the old ones already in place.

## Speed and caching

- **Page cache.** Public pages are stored as finished HTML and served without touching the database. Saving
  anything in the panel (a page, menu, post, case study, setting or SEO entry) refreshes every page on its next
  visit, so edits show straight away. Pages are also refreshed every 12 hours, and when a scheduled post goes live.
  Search results (`/blogs?q=`) are always rendered fresh.
- **Settings** (`.env`): `GTECH_PAGE_CACHE=false` turns the cache off, `GTECH_PAGE_CACHE_TTL` sets the refresh time
  in seconds, `GTECH_PAGE_CACHE_STORE` picks a cache store. Use `CACHE_STORE=redis` or `file` in production;
  the `database` store works but adds a query per page.
- **Changed the database directly** (not through the panel)? Run `php artisan cache:clear`.
- **No cookies.** Public pages start no session and set no cookies, so a CDN such as Cloudflare can cache them and
  bots never fill the sessions table.
- **Browsers** keep pages but check back on each visit; an unchanged page costs a `304 Not Modified` with no body.
- **Static files.** `public/.htaccess` compresses text files and lets browsers keep `site.css` and `site.js` for a
  year (they are linked with a version number) and images and videos for a week. On nginx:

  ```nginx
  gzip on; gzip_types text/css application/javascript application/json image/svg+xml text/xml application/xml;
  location ~* \.(css|js)$ { expires 1y; add_header Cache-Control "public, immutable"; try_files $uri /index.php?$query_string; }
  location ~* \.(webp|png|jpe?g|gif|svg|ico|mp4|webm|woff2?)$ { expires 7d; try_files $uri /index.php?$query_string; }
  ```
- **On every deploy** run `php artisan optimize` (config, routes, views and events are cached) and keep PHP OPcache
  on. Run `php artisan optimize:clear` before editing `.env` on the server.

## Admin panel notes

- **Pages (page builder):**
  - *Service, industry and landing pages* are built from sections: **Add a section** picks from the library (text,
    image and text, cards, features, steps, table, results in numbers, impact, reviews, case studies, industries,
    client logos); sections can be moved, copied and removed. The home, about, contact, hub and legal pages keep their
    designed layout, so you edit their words and images.
  - *New landing pages*: **New landing page** at any free address (e.g. `/free-seo-audit`), blank or copied from an
    existing page. They start unpublished, appear in the sitemap once published, and can be hidden from search engines.
  - *Drafts*: **Save draft** keeps changes off the website; **Preview** shows the unsaved page at desktop, tablet and
    phone width; **Publish changes** puts them live, or **Schedule** publishes them at a set time. People without the
    "Publish" permission can only save drafts.
  - *Version history* (pages, blog posts, case studies): every published version is kept (the last 60). Compare any
    version with the current one, word by word, and restore it (a page is restored into its draft).
- **Scheduled tasks:** scheduled pages are also published by the scheduler (`php artisan schedule:run` every minute,
  see Leads below), and on the first visit after their time even without it.

- **Leads (CRM):**
  - *Assigned to*: the owner gets an email with the lead. People without "See everyone's leads" (the Sales role)
    see only their own leads, everywhere in the panel.
  - *Timeline*: **Log activity** records a call, email, meeting or note, and can set the next follow-up in the same
    step. Logging a call, email or meeting moves a "New" lead to "Contacted" and records the first-response time.
    Status, owner, follow-up and deal value changes are added automatically.
  - *Follow-ups*: the dashboard's **My follow-ups** lists what is due today or overdue, new leads assigned to you and
    new leads nobody has taken. The leads list filters by owner, follow-up and source.
  - *Source*: each lead records where the visitor came from (Google, ChatGPT, a campaign...), the landing page and
    the page the form was on.
  - **Site settings → Leads**: assign new enquiries automatically (taking turns), post them to a Slack, Google Chat
    or Teams channel, and turn the morning reminder email on or off.
  - *Pipeline* (Leads → Pipeline): the leads as a board, one column per stage; drag a card to move it (on a phone, use
    "Move to"). Stages are renamed, added or reordered in **Site settings → Leads**; New, Won and Lost always exist.
  - *Send email* on a lead: write an email or start from a template (Leads → Email templates, with placeholders such as
    `{first_name}`). It goes out through the SMTP account, replies come back to the sender, and it is logged on the timeline.
  - *Contacts*: one per email address, so someone who enquires again joins their earlier enquiries. Duplicates under
    another address can be merged.
  - *Data protection (UK GDPR)*: each form shows a short privacy notice (Site settings → Forms), saved with the lead.
    On a contact, **Download their data** gives a file for a subject access request and **Erase their data** deletes
    their enquiries, timeline and newsletter subscription (a record without personal details is kept). Lost leads
    can be deleted automatically after 6 months to 3 years (Site settings → Leads).
  - *Spam protection*: honeypot field, at most 5 enquiries an hour per network, and optional Cloudflare Turnstile
    (Site settings → Forms: paste the site and secret keys from Cloudflare → Turnstile).
  - The morning reminder (weekdays at 8am UK time) needs Laravel's scheduler: add a cron job (in Plesk: Scheduled
    tasks) that runs every minute: `cd /path/to/backend && php artisan schedule:run`.

- **Emails** (password reset, lead alerts, auto-replies) are sent with the SMTP account in **Site settings → Email**
  and go out straight away (`QUEUE_CONNECTION=sync`). If you prefer a background queue, set `QUEUE_CONNECTION=database`
  and keep `php artisan queue:work` running (Supervisor, or a Plesk scheduled task).
- **Redirects** (Website content → Redirects): changing a blog post or case study address adds one automatically, so
  old links keep working. You can add your own, e.g. for retired pages. A redirect is only used when the address no
  longer exists, so it can never hide a live page.
- **Article editor:** click an image, then the image button, to change its alt text. Click inside a link, then the
  link button, to change or remove it.
- **Admin theme:** the compiled stylesheet is committed in `public/build`, so the server needs no Node.js. Only after
  changing `resources/css/filament/admin/*` run `npm install && npm run build` (on your computer) and commit the result.

## Commands

| Command | What it does |
|---|---|
| `php artisan gtech:import-mongo <folder> [--dry-run] [--auth-secret=...]` | Import a MongoDB export (safe to repeat) |
| `php artisan gtech:seed-content [--force] [--no-demo]` | Load all website content (safe to repeat) |
| `php artisan gtech:create-admin <email>` | Create a super admin, or reset an existing user's password |
| `php artisan test` | Run the backend tests |

## Blade front end

The website has been rebuilt in Blade with the same HTML, CSS and behaviour as the Next.js version. It is ready to
replace the Next.js site; see **Switching the website to Blade** below.

| Phase | Status |
|---|---|
| 1. All content in MySQL | Done |
| 2. Layout and shared parts: header and mega menu, footer, contact popup, cookie banner, back-to-top, "Let's Talk", scroll motion, forms | Done. Preview at `/blade-preview` |
| 3. Page templates: every page of the website (home, about, contact, both hubs, 35 services, 10 industries, case studies, blog, legal pages, 404) and their behaviour (in-page tabs, sliders, testimonials, count-ups, charts, videos, share and newsletter) | Done. Every page's HTML matches the Next.js build |
| 4. Behaviour check: 35 interaction scenarios (scrolling, sliders, rotation, count-ups, videos, share, newsletter, search, forms, popup, menus, cookie banner) give the same result as the website; no JS errors on any page | Done. Tools in `scripts/parity/` |
| 5. SEO: titles, descriptions, canonicals, robots, Open Graph and Twitter tags, the JSON-LD graph on every page, the panel's SEO overrides, `sitemap.xml`, `robots.txt` and the old-address redirects | Done. Same output as the website on all 67 pages, with the fixes below |
| 6. Caching: full-page cache that refreshes itself on every save, no cookies or sessions on public pages, 304 responses for unchanged pages, compression and long browser caching for CSS, JS and images | Done |
| 7. Full visual check: all 67 pages at 1352, 900 and 390 px, full page including header and footer, compared pixel by pixel with the website | Done. Same page heights everywhere; at most 0.012% of pixels differ (text edges and the resized logo) |

SEO fixes compared with the Next.js output:

- Canonical links and share images use full `https://www.gtechdigital.co.uk/...` addresses (the website printed relative
  canonicals and `localhost` image addresses).
- Every page has a canonical link and Open Graph and Twitter tags, so shared links show a title, description and image.
- The sitemap leaves out pages set to noindex and gives each page a `lastmod` date from its last edit.

The Blade pages are served at the same addresses as the website (`/`, `/services/local-seo`, `/blogs`...). Until
`GTECH_BLADE_LIVE=true` they are sent with `X-Robots-Tag: noindex`, so search engines ignore them while the
Next.js website is still live, and `robots.txt` blocks all crawlers. The blog search (`?q=`) and topic filter (`?category=`) run on the server.

- **Styles:** `public/css/site.css` is linked to the website's `app/globals.css`, so both front ends share one stylesheet.
- **Images and videos:** linked from the website's `public/` folder. Run `php artisan gtech:link-assets` after cloning or deploying.
- **Behaviour:** `public/js/site.js` holds plain JavaScript ports of the React components (menus, popup, forms, cookie consent, back-to-top, scroll motion).
- **Blade helpers:**
  - `@icon('lucide:check', 16)` renders the same SVG icons.
  - `@hl($heading)` renders the two-tone headings, matching the website's highlighter on all 1,010 headings in the content.
  - `@rt($html)` cleans rich text, matching the website's sanitiser on 1,618 samples.
- **Updating icons:** after adding icons to the website, run `npx tsx scripts/export-icons.ts` from the repository root.
