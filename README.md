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
