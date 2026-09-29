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
