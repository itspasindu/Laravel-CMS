# Dummy CMS

Lightweight Laravel 11 CMS scaffold for exercising real build, migrate, auth, storage, and test steps. No CI/CD workflows, Dockerfiles, or deployment scripts are included yet.

## Stack

- PHP ^8.2, Laravel ^11.31, Laravel Breeze (Blade + Alpine + Tailwind)
- Vite ^6, Tailwind CSS ^3.1, Alpine.js, Axios
- MySQL primary (`DB_CONNECTION=mysql`); SQLite fallback documented in `.env.example`
- Database drivers for session, cache, and queue
- Local filesystem disk with `php artisan storage:link` for public uploads

## Ubuntu server setup

```bash
# From the project root
cp .env.example .env

# Edit .env: set APP_URL, MySQL credentials (or switch to sqlite), and generate the key
composer install
php artisan key:generate

# Optional quick SQLite fallback instead of MySQL:
#   touch database/database.sqlite
#   set DB_CONNECTION=sqlite and DB_DATABASE to the absolute sqlite path in .env

npm install
npm run build

php artisan migrate --seed
php artisan storage:link

# Verify
./vendor/bin/pint --test
php artisan test
php artisan serve
```

Default seeded admin (from `DatabaseSeeder`):

- Email: `admin@example.com`
- Password: `password` (Laravel factory default)

## Useful routes

| Path | Purpose |
|------|---------|
| `/` | Public home listing News + Notices |
| `/health` | JSON health check (`{"status":"ok"}`) |
| `/up` | Laravel framework health endpoint |
| `/login` | Breeze auth (+ OIDC stub link) |
| `/dashboard` | Protected dashboard |
| `/admin/news` | News CRUD (auth required) |
| `/admin/notices` | Notice CRUD (auth required) |
| `/auth/oidc/redirect` | OIDC placeholder (falls back when disabled) |

## OIDC stub

Environment variables are documented in `.env.example` (`OIDC_*`). With `OIDC_ENABLED=false` (default), the stub redirects to Breeze login so tests and local runs do not need an IdP.

## Notes

- Uploaded news images and notice attachments are stored on the `public` disk under `storage/app/public`.
- Run `php artisan storage:link` so files are reachable at `/storage/...`.
- Frontend assets are built to `public/build` (including `manifest.json`) via `npm run build`.
