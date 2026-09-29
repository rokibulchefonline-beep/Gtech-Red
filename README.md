# Gtech-Red

PHP backend with MongoDB.

## Setup
1. Install the MongoDB PHP extension: `pecl install mongodb` (then enable `extension=mongodb`).
2. `composer install`
3. `cp .env.example .env` and set `MONGODB_URI` / `MONGODB_DB`.
4. `php -S localhost:8000 -t public public/index.php`

## Endpoints
- `GET /api/health` – pings MongoDB
- `GET|POST /api/items`, `GET|PUT|DELETE /api/items/{id}` – example CRUD; replace with your own collections

## Deploy (no local setup)
1. Create a free cluster on MongoDB Atlas, add a database user, and allow network access (0.0.0.0/0 for testing).
2. Deploy this repo to a Docker-capable host (Render, Railway, Fly.io) as a Web Service using the `Dockerfile`.
3. Set env vars `MONGODB_URI` (the Atlas `mongodb+srv://...` string) and `MONGODB_DB`.
4. Open `https://<your-app>/api/health`.

## Site structure
- `/` home, `/about`, `/contact` growth-proposal form (saves to MongoDB `leads`), `/privacy-policy`, `/terms`
- `/services/{group}` and `/services/{item}` (e.g. `/services/search-engine-optimization`) – Digital Marketing, Social Media Marketing, Web Design & Development, Custom Software Development, Branding & Strategy
- `/industries/{industry}` – E-commerce, Education, B2B Marketing, Automotive, Healthcare, Hospitality & Hotels, Travel, Real Estate, Finance
- `/blogs`, `/blogs/{slug}` (e.g. `/blogs/5-seo-tools`) – MongoDB collection `posts` (`title`, `slug`, `excerpt`, `body`, `created_at`)
- `/case-studies`, `/case-studies/{slug}` – MongoDB collection `case_studies` (same fields), e.g. `/case-studies/chefonline`

Menus, services and industries live in `src/data.php`. Pages are in `templates/`.
