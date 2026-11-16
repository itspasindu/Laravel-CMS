# Dummy CMS

Lightweight Laravel 12 CMS scaffold for exercising real build, migrate, auth, storage, and test steps. GitHub Actions workflows **test, scan, package, deploy, and smoke-verify** releases to an Ubuntu staging server over SSH (**no Docker**). See [CI/CD setup](docs/CI-CD.md) for the required GitHub settings and server preparation.

## Stack

- PHP ^8.2, Laravel ^12.71, Laravel Breeze (Blade + Alpine + Tailwind)
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

## Application environment commands

These commands use **Bash on Ubuntu**. Run them as the deploy account after the directory setup in [CI/CD setup](docs/CI-CD.md). The application settings live in `/opt/dummy-cms/shared/.env`; GitHub Actions settings are configured separately below.

### Create the complete staging `.env`

This includes every variable from `.env.example`, including optional services. Change `APP_URL`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, and `MAIL_FROM_ADDRESS` for your server. The database and user must already exist. The command refuses to overwrite an existing file.

```bash
export CMS_ENV_FILE=/opt/dummy-cms/shared/.env
# For local development instead: export CMS_ENV_FILE="$PWD/.env"

(
  umask 027
  set -o noclobber
  cat > "$CMS_ENV_FILE" <<'ENV'
APP_NAME="Dummy CMS"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://cms.example.com

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US
APP_MAINTENANCE_DRIVER=file
# Only used with APP_MAINTENANCE_DRIVER=cache:
APP_MAINTENANCE_STORE=database
# Only used by the local PHP development server:
PHP_CLI_SERVER_WORKERS=4
BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dummy_cms
DB_USERNAME=dummy_cms
DB_PASSWORD="REPLACE_WITH_DATABASE_PASSWORD"

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null
BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
CACHE_STORE=database
CACHE_PREFIX=dummy_cms_cache_

# Optional; unused while CACHE_STORE=database:
MEMCACHED_HOST=127.0.0.1
# Optional; unused while database drivers are selected:
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# log writes mail to application logs; it does not deliver email:
MAIL_MAILER=log
MAIL_SCHEME=null
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

# Optional; unused by the local/public upload disks:
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="${APP_NAME}"

# OIDC is currently a stub; keep disabled for normal Breeze login:
OIDC_ENABLED=false
OIDC_CLIENT_ID=
OIDC_CLIENT_SECRET=
OIDC_REDIRECT_URI="${APP_URL}/auth/oidc/callback"
OIDC_ISSUER=
OIDC_AUTHORIZATION_ENDPOINT=
OIDC_TOKEN_ENDPOINT=
OIDC_USERINFO_ENDPOINT=
ENV
)

# Set the real domain and database credentials without putting secrets in shell history.
nano "$CMS_ENV_FILE"
```

The quoted `ENV` delimiter preserves Laravel's `${APP_NAME}` and `${APP_URL}` references. Do not `source` this file into your shell. For local development, use `APP_ENV=local`, `APP_DEBUG=true`, `APP_URL=http://localhost:8000`, and `LOG_LEVEL=debug`.

### Generate the application key once

This works before the first release, using PHP CLI without Composer. An existing key is preserved; keep the same key across deployments.

```bash
if grep -qx 'APP_KEY=' "$CMS_ENV_FILE"; then
  cms_app_key=$(php -r 'echo "base64:".base64_encode(random_bytes(32));') &&
    sed -i "s|^APP_KEY=$|APP_KEY=${cms_app_key}|" "$CMS_ENV_FILE"
  unset cms_app_key
fi

# On staging, allow PHP-FPM's group to read the shared environment file.
chgrp www-data "$CMS_ENV_FILE"
chmod 640 "$CMS_ENV_FILE"
```

### Optional services and later changes

Use `nano "$CMS_ENV_FILE"` to update the relevant values:

| Service | Values to change |
| --- | --- |
| SQLite instead of MySQL | `DB_CONNECTION=sqlite`, `DB_DATABASE=/opt/dummy-cms/shared/database.sqlite`; MySQL host, port, user, and password are unused. Create the file with `touch /opt/dummy-cms/shared/database.sqlite` and grant the deploy account and `www-data` write access. |
| SMTP email | Set `MAIL_MAILER=smtp`, then your provider's `MAIL_SCHEME`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, and verified `MAIL_FROM_ADDRESS`. |
| Redis | Install the PHP Redis extension and configure `REDIS_CLIENT`, `REDIS_HOST`, `REDIS_PASSWORD`, and `REDIS_PORT`; select `redis` for the cache, queue, or session driver only when needed. |
| Memcached | Install the PHP Memcached extension, set `CACHE_STORE=memcached` and `MEMCACHED_HOST`. |
| S3 | Configure `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION`, `AWS_BUCKET`, and `AWS_USE_PATH_STYLE_ENDPOINT`; install the S3 filesystem adapter before using `FILESYSTEM_DISK=s3`. Existing CMS uploads explicitly use the `public` disk. |
| OIDC | The `OIDC_*` fields are placeholders for future integration; setting them does not implement a complete SSO flow. |

After changing server settings **on an already deployed application**, refresh cached configuration and workers:

```bash
cd /opt/dummy-cms/current
php artisan config:cache
php artisan queue:restart
sudo systemctl reload php8.3-fpm
```

`VITE_*` values are read during the frontend build. Changing the server `.env` alone does not change bundled JavaScript; set build-time values in CI and build a new release. Never put secrets in `VITE_*` variables.

## GitHub Actions environment commands

Run these commands on your trusted workstation in **Bash**, with GitHub CLI, OpenSSH, and OpenSSL installed. They configure the repository; do not add these secrets to the application `.env`.

### Select the repository and prepare environments

```bash
gh auth login
export CMS_REPO=itspasindu/Laravel-CMS
```

In the repository's **Settings → Environments**, create `release-signing` and `staging`, and restrict both to the `main` branch. Then run the following commands. Repository administration access is needed for configuration.

### Release signing keys

Generate these once outside the checkout. The commands preserve an existing private key.

```bash
mkdir -p "$HOME/.config/dummy-cms"
chmod 700 "$HOME/.config/dummy-cms"
export CMS_SIGNING_KEY="$HOME/.config/dummy-cms/release-private.pem"

if [ ! -e "$CMS_SIGNING_KEY" ]; then
  (umask 077; openssl genpkey -algorithm RSA \
    -pkeyopt rsa_keygen_bits:3072 -out "$CMS_SIGNING_KEY")
fi
openssl pkey -in "$CMS_SIGNING_KEY" -pubout \
  -out "$HOME/.config/dummy-cms/release-public.pem"

gh secret set RELEASE_SIGNING_PRIVATE_KEY --repo "$CMS_REPO" \
  --env release-signing < "$CMS_SIGNING_KEY"
gh variable set RELEASE_SIGNING_PUBLIC_KEY --repo "$CMS_REPO" \
  < "$HOME/.config/dummy-cms/release-public.pem"
```

### Staging SSH credentials

Replace the hostname and account below. Use a dedicated deployment key without a passphrase for the unattended workflow. `ssh-copy-id` requires existing access to that server.

```bash
export CMS_STAGING_HOST=your-server.example.com
export CMS_STAGING_USER=deploy
export CMS_SSH_KEY="$HOME/.ssh/dummy-cms-deploy"
mkdir -p "$HOME/.ssh"
chmod 700 "$HOME/.ssh"

if [ ! -e "$CMS_SSH_KEY" ]; then
  ssh-keygen -t ed25519 -C dummy-cms-github-actions -N '' -f "$CMS_SSH_KEY"
fi
ssh-copy-id -i "$CMS_SSH_KEY.pub" "$CMS_STAGING_USER@$CMS_STAGING_HOST"

gh secret set SSH_PRIVATE_KEY --repo "$CMS_REPO" --env staging < "$CMS_SSH_KEY"
gh secret set STAGING_HOST --repo "$CMS_REPO" --env staging --body "$CMS_STAGING_HOST"
gh secret set STAGING_USER --repo "$CMS_REPO" --env staging --body "$CMS_STAGING_USER"

ssh-keyscan -t ed25519 "$CMS_STAGING_HOST" > "$HOME/.config/dummy-cms/known_hosts"
ssh-keygen -lf "$HOME/.config/dummy-cms/known_hosts"
```

Before uploading `known_hosts`, compare that fingerprint with `sudo ssh-keygen -lf /etc/ssh/ssh_host_ed25519_key.pub` run through the server's trusted console. After they match:

```bash
gh secret set STAGING_SSH_KNOWN_HOSTS --repo "$CMS_REPO" --env staging \
  < "$HOME/.config/dummy-cms/known_hosts"
```

### Deployment variables and optional Tailscale key

```bash
gh variable set STAGING_DEPLOY_ROOT --repo "$CMS_REPO" --env staging \
  --body '/opt/dummy-cms'
gh variable set STAGING_HEALTH_URL --repo "$CMS_REPO" --env staging \
  --body 'https://cms.example.com/'

# Only for a server reached through Tailscale; enter the key at the hidden prompt.
gh secret set TAILSCALE_AUTHKEY --repo "$CMS_REPO" --env staging
```

Use your actual application URL for `STAGING_HEALTH_URL`; it must be reachable from the Ubuntu server. Skip the Tailscale command for a directly reachable SSH server. GitHub supplies `GITHUB_TOKEN` automatically.

Verify the configured names without displaying secret values:

```bash
gh secret list --repo "$CMS_REPO" --env release-signing
gh secret list --repo "$CMS_REPO" --env staging
gh variable list --repo "$CMS_REPO"
gh variable list --repo "$CMS_REPO" --env staging
```

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
