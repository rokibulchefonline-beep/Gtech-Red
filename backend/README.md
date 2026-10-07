# GTech Digital: website and admin panel

Laravel 12 + MySQL + Filament 3. One app serves the public website (Blade templates) and the admin panel at
`/admin`. It replaces the old Next.js site, its admin and MongoDB.

```
Visitors ──> (Cloudflare, optional) ──> Laravel ──> MySQL <── Admin panel (/admin)
                                          │
                                          └── forms, analytics, emails (SMTP), scheduled tasks (cron)
```

## What's in the panel (/admin)

| Area | Covers |
|---|---|
| **Website content** | Pages (edit the copy of every service, industry and main page, with `[[red]]` heading highlights), case studies, SEO overrides, partner badges, client logos and the media library |
| **Blog** | Posts (rich-text editor, featured image, SEO fields, scheduling) and categories |
| **Leads** | Form enquiries with owner, follow-up date, timeline and source; newsletter subscribers (CSV export) |
| **Settings** | Site settings (contact details, tracking IDs, SMTP email with a test button, leads, forms, security), users and roles |
| **Dashboard** | Stats, my follow-ups and the latest leads |

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
It only collects data on the live website.

## Requirements

- PHP 8.2+ with the extensions `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `gd` or `imagick`, `intl` and `zip`.
- MySQL 8+ or MariaDB 10.6+.
- Composer 2.
- Any PHP host works: a VPS, Laravel Forge, Cloudways, or cPanel hosting with SSH access.

## Try it on a Windows computer

1. Install **Laravel Herd for Windows** (herd.laravel.com) and open it once. It provides PHP and Composer.
2. Download this branch as a ZIP from GitHub and extract it (for example to `C:\Sites`).
3. Open the `backend` folder and double-click **`start-windows.bat`**.

The first run installs everything, creates a local SQLite database with the website content and asks for your admin
email and password; then the panel opens at http://127.0.0.1:8000/admin. Later runs just start it. Keep the window
open while you use it. This copy lives only on your computer (no real leads arrive there).

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

## Cloudflare in front (optional)

Cloudflare cannot run this app (it needs PHP, MySQL and cron), but it can sit in front of the server:

1. In Cloudflare DNS, point the domain (an **A record**) at the server's IP with the orange cloud (Proxied) on.
2. **SSL/TLS → Full (strict)**, with a free Cloudflare Origin Certificate installed on the server.
3. A cache rule that **bypasses the cache** for `/admin*`, `/livewire*`, `/preview*` and `/api*`.

## Where content lives

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

`gtech:seed-content` loads the original copy from `database/data/content.json` (exported from the old site). It
only fills what is missing and never overwrites edits, unless you pass `--force`.
**Restore original** on a page puts that single page back to its launch copy.

## Going live

1. Point the domain at the server (document root `backend/public`) and keep `APP_URL` on the site's address.
2. In `.env` keep `GTECH_BLADE_LIVE=true` (the default in `.env.example`): search engines may index the pages and
   `robots.txt` lists the sitemap. Set it to `false` on a test copy to keep it out of Google.
3. Run `php artisan optimize`.
4. Check a few pages, then submit `https://www.gtechdigital.co.uk/sitemap.xml` in Google Search Console.

All page addresses are the same as on the old site, and the old addresses still redirect.

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

- **SEO menu:**
  - *SEO dashboard*: overall, SEO, AEO (answer engines) and GEO (AI engines) scores, the commonest problems, the
    weakest pages and posts, search and AI traffic, AI crawlers, and index health.
  - *SEO audit*: every check for every service, industry and landing page, with how to fix it; the blog posts;
    the keyword and entity map; internal links (orphans, broken links, keyword conflicts). Pages and posts are
    shown 25 at a time with search and sorting, so it copes with thousands of posts. Re-runs straight after any
    content change ("Run again" forces it).
  - *Internal link map*: every page, blog post and case study with the links between them, including links written
    in the text. Rebuilt by itself whenever content is saved; search for a page, click it to see its links in and out.
  - *Keyword map*, *SEO overrides* and *Redirects* are in the same menu.
  - The blog post editor has an **SEO check** panel (score and checks), and its link box can **link to any page of
    the site** from a searchable list.
  - **Schema markup**: the page, blog post and case study editors have a *Schema markup* section showing the
    automatic schema the page outputs, links to test it in Google, a switch to turn it off, and your own JSON-LD
    with ready-made templates (FAQ, How-to, Product, Review, Video, Event, Local business). Saved in SEO overrides.
- **Day / Night** buttons at the top right switch the admin between the white and the dark look (dark is the default).

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

## Website front end

The public pages are Blade templates in `resources/views/site` with the same HTML, CSS and behaviour as the old
Next.js site (checked page by page and pixel by pixel on all 67 pages before it was removed), plus SEO fixes: full
canonical and share-image addresses, Open Graph and Twitter tags on every page, and `lastmod` dates in the sitemap.

- **Styles:** `public/css/site.css`. **Behaviour:** `public/js/site.js` (menus, popup, forms, cookie consent,
  back-to-top, scroll motion, analytics).
- **Images and videos:** `public/bg`, `case`, `pages`, `partners`, `posts`, `services`, `videos`. Uploads from the
  media library go to `storage/app/public` (run `php artisan storage:link` once).
- **Blade helpers:** `@icon('lucide:check', 16)` (icons from `resources/data/icons.json`), `@hl($heading)` (two-tone
  headings with `[[red words]]`) and `@rt($html)` (cleaned rich text).
- With `GTECH_BLADE_LIVE=false` every page is sent with `X-Robots-Tag: noindex` and `robots.txt` blocks crawlers.
