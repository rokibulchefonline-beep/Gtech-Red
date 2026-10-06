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
php artisan gtech:sync-pages          # lists every website page in the Page editor
php artisan gtech:create-admin you@example.com --name="Your Name"   # only if you are not importing users
php artisan optimize
```

Point the web server's document root at `backend/public`.

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

## Keeping page text in sync

The Page editor shows the built-in copy from the website code (`database/data/page-base.json`).
After changing page text in the website code, regenerate it from the repository root:

```bash
npm run export:pages
```

Then commit the file.

## Commands

| Command | What it does |
|---|---|
| `php artisan gtech:import-mongo <folder> [--dry-run] [--auth-secret=...]` | Import a MongoDB export (safe to repeat) |
| `php artisan gtech:sync-pages` | Add any new website pages to the Page editor |
| `php artisan gtech:create-admin <email>` | Create a super admin, or reset an existing user's password |
| `php artisan test` | Run the backend tests |
