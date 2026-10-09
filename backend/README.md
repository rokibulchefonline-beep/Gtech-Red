# GTech Digital: website and admin panel

Laravel 13 + MySQL + Filament 3 (PHP 8.3 or newer). One app serves the public website and the admin panel at
`/admin`: pages, services and industries, blog, case studies, leads and free audit requests, analytics, SEO tools
and settings.

## Run it on your computer (Laravel Herd, Windows)

1. Install [Laravel Herd](https://herd.laravel.com) and set PHP to 8.3 or newer. Create a MySQL database.
2. In the `backend` folder:
   ```
   copy .env.example .env
   composer install
   php artisan key:generate
   php artisan migrate --force
   php artisan gtech:seed-content
   php artisan gtech:create-admin
   php artisan storage:link
   ```
3. Link the `backend` folder in Herd and open `http://backend.test` (or the name you gave it), admin at `/admin`.

After each `git pull`: `composer install`, `php artisan migrate --force`, `php artisan view:clear`,
`php artisan view:cache`.

## Deploy to a Plesk server

**1. Domain and PHP.** In Plesk, open the domain → *PHP Settings*: PHP 8.3 or 8.4 (FPM). Raise
`upload_max_filesize` and `post_max_size` to `55M` (for hero video uploads) and `memory_limit` to `256M`.
Needed PHP extensions (normally on): pdo_mysql, mbstring, openssl, gd, intl, fileinfo, zip, curl, bcmath.

**2. Database.** *Databases* → add a MySQL database and user.

**3. Code.** *Git* → add this repository (branch to deploy) into `httpdocs`, or upload the files.
Then *Hosting Settings* → **Document root: `httpdocs/backend/public`**. This is important: only the `public`
folder may be reachable from the web.

**4. Environment.** In `backend/`, copy `.env.production.example` to `.env` (in the file manager or over SSH) and
fill in `APP_URL`, the database details, mail (SMTP) and any API keys. Then, over SSH in `backend/`:
```
php artisan key:generate          (first install only, never change APP_KEY afterwards)
sh deploy.sh                      (composer install, migrations, caches)
php artisan gtech:seed-content    (first install only: services, pages and blog content)
php artisan gtech:create-admin    (first install only: your admin login)
php artisan gtech:geoip-update    (visitor countries for Analytics)
```
Use Plesk's PHP if `php` is a different version: `PHP_BIN=/opt/plesk/php/8.4/bin/php sh deploy.sh`.

**5. Automatic deploys (optional).** *Git* → *Enable additional deployment actions* and enter:
`cd backend && PHP_BIN=/opt/plesk/php/8.4/bin/php sh deploy.sh`

**6. Scheduled tasks.** *Scheduled Tasks* → add a task that runs **every minute**:
`/opt/plesk/php/8.4/bin/php /var/www/vhosts/YOUR-DOMAIN/httpdocs/backend/artisan schedule:run`
It sends follow-up reminders, publishes scheduled page changes, checks the site for broken links each night, removes old
lost leads and refreshes the country database monthly.

**7. SSL.** *SSL/TLS Certificates* → Let's Encrypt for the domain and `www`, with "redirect HTTP to HTTPS" on.

**8. Optional.** Install `ffmpeg` on the server (or set `FFMPEG_PATH` in `.env`) so uploaded hero videos are
compressed automatically.

## Updating the live site

Push to the deployed branch and pull in Plesk *Git* (it runs `deploy.sh` if step 5 is set), or over SSH:
`git pull && sh backend/deploy.sh`.

## Settings worth knowing

- **Site settings** (admin): contact details, tracking IDs (Google Tag Manager, GA4, Meta Pixel), SMTP email with a
  test button, lead routing, Google reCAPTCHA for the forms, two-factor sign-in rules.
- **`GTECH_BLADE_LIVE`**: `true` on the live site; `false` on a test copy (pages are sent with `noindex`).
- **Pages are cached** for speed and refresh by themselves when content is saved in the admin.

## Troubleshooting

- **500 error after an update:** `php artisan optimize:clear`, then check `storage/logs/laravel.log`.
- **Uploads fail:** raise `upload_max_filesize` / `post_max_size` in PHP settings; check `storage/` is writable.
- **Images or videos 404:** run `php artisan storage:link`.
- **"Access is denied" renaming a view on Windows:** `php artisan view:clear && php artisan view:cache`, and exclude
  the project's `storage` folder from Windows Defender.

Tests: `php artisan test`.
