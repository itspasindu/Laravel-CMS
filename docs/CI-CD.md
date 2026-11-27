# Laravel CI/CD

Adapted from the administrator pipeline overview (test → scan → package → deploy → verify) and [itspasindu/reusable-cicd-pipeline](https://github.com/itspasindu/reusable-cicd-pipeline).  
This project uses **PHP 8.3 on GitHub-hosted Ubuntu 24.04**, Node 22 for asset builds, and an Ubuntu SSH staging target. **No Docker** is required for application packaging or staging runtime.

## Pipeline

1. **Test** — Pint, PHPUnit (SQLite), release-trust/deployment recovery scripts, Vite build, Playwright smoke (Chromium, Firefox, WebKit, Mobile Chrome, Mobile Safari) against `php artisan serve`.
2. **Scan** — npm audit high/critical via `scripts/npm-audit-ci.mjs` (allowlists unpatched `picomatch` GHSA-fc5q-39xc-vjpf until a fixed release exists), TruffleHog (verified secrets only), PHPStan/Larastan (level 5; CodeQL has no PHP support), CodeQL (JavaScript), Trivy filesystem scan (HIGH/CRITICAL).
3. **Package** — On `main` only: production Composer deps, built assets, SBOM inventory, `CHECKSUMS.sha256`, `release.json` version `YYYY.MM.N.A+sha`, then RSA signature in `release-signing`.
4. **Deploy** — CD verifies the CI run and signature, copies the archive over SSH (optional Tailscale), switches `current`, **backs up the database**, migrates, reloads PHP-FPM, restarts queues.
5. **Verify** — Smoke checks `/health`, `/up`, `/`, `/login` from the server. Failure after switch restores `previous` (files only; migrations are not reversed — restore from `shared/backups/` if needed).

Pull requests stop after test + scan. Only `main` is packaged and deployed. Failed CI/CD runs open a GitHub issue with a link to the run.

Dependabot opens weekly grouped PRs for Composer, npm, and GitHub Actions (see `.github/dependabot.yml`).

Release artifacts expire after 30 days. Manual CD redeploys an existing verified artifact; it does not rebuild it. Deployments are serialized in Actions and with a server-side lock.

## What was intentionally not ported from the React overview

| Overview item | This Laravel pipeline |
| --- | --- |
| Docker / container runtime | Not used |
| PostgreSQL password at deploy | App uses shared `/opt/dummy-cms/shared/.env` (MySQL/SQLite) |
| Wiki publishing (`WIKI_TOKEN`) | Optional later; not required |
| Pipeline-monitor API | Not used |

## GitHub configuration

Create environments named `release-signing` and `staging`. Restrict both to `main`. Protect `main` and require the test and security checks.

| Location | Name | Value |
| --- | --- | --- |
| `release-signing` environment secret | `RELEASE_SIGNING_PRIVATE_KEY` | RSA private key, PEM |
| Repository variable | `RELEASE_SIGNING_PUBLIC_KEY` | Matching public key, PEM |
| `staging` environment secret | `SSH_PRIVATE_KEY` | Deploy private key |
| `staging` environment secret | `STAGING_HOST` | Hostname or IP (no protocol) |
| `staging` environment secret | `STAGING_USER` | e.g. `deploy` |
| `staging` environment secret | `STAGING_SSH_KNOWN_HOSTS` | `ssh-keyscan` / host key line |
| `staging` environment secret (optional) | `TAILSCALE_AUTHKEY` | Private Tailscale access |
| `staging` environment variable | `STAGING_DEPLOY_ROOT` | Default `/opt/dummy-cms` |
| `staging` environment variable | `STAGING_HEALTH_URL` | URL reachable **from the server**, e.g. `http://127.0.0.1/health` |

Optional labels: `ci-failure`, `cd-failure` (created automatically when possible).

### Required checks and environment reviewers

After CI has run once on `main`, apply quality gates (branch protection + environment reviewers):

```bash
bash scripts/configure-github-quality-gates.sh
# or: REVIEWER_LOGIN=your-github-login bash scripts/configure-github-quality-gates.sh
```

That script:

- Protects `main` with required status checks `test` and `security`, 1 approving review, linear history, conversation resolution (`enforce_admins` off so a solo maintainer can still merge)
- Adds you as a required reviewer on `staging` and `release-signing` (deployments wait for approval)
- Keeps deployment branch policy limited to protected branches

If a PR shows checks as pending forever, open **Settings → Branches → main** and select the exact check names GitHub reports (often `CMS CI / test` and `CMS CI / security`).

### Local PHP static analysis

```bash
composer install
composer analyse
```

Config lives in `phpstan.neon.dist` (Larastan level 5, `app/` only).

## Ubuntu server preparation

Install Nginx, PHP 8.3/8.5 CLI+FPM (+ extensions), MySQL (`mysqldump`), `curl`, `tar`, `gzip`, `util-linux`. Composer and Node are **not** required on the server.

### Least-privilege deploy user (recommended)

Run once as root from a checkout (or copy the script onto the host):

```bash
sudo bash scripts/setup-least-privilege-deploy.sh
```

That script:

- Creates `deploy` (or `$DEPLOY_USER`) in group `www-data`
- Owns `/opt/dummy-cms` (including `shared/backups`)
- Generates an **ed25519 deploy key** and installs it in `authorized_keys` with `no-port-forwarding,no-agent-forwarding,no-X11-forwarding,no-pty`
- Installs sudoers allowing **only** `systemctl reload` for `php8.3/8.4/8.5-fpm` and `php-fpm`

Put the private key in the GitHub `staging` environment secret `SSH_PRIVATE_KEY`. Do not reuse a personal admin key for CD.

Manual equivalent (if you skip the script):

```bash
sudo usermod -aG www-data deploy
sudo install -d -o deploy -g www-data -m 2775 /opt/dummy-cms /opt/dummy-cms/shared /opt/dummy-cms/shared/storage /opt/dummy-cms/shared/backups
# Create /opt/dummy-cms/shared/.env from .env.example (production values + permanent APP_KEY)
sudo chown deploy:www-data /opt/dummy-cms/shared/.env
sudo chmod 640 /opt/dummy-cms/shared/.env
```

```sudoers
deploy ALL=(root) NOPASSWD: /usr/bin/systemctl reload php8.5-fpm, /usr/bin/systemctl reload php8.4-fpm, /usr/bin/systemctl reload php8.3-fpm, /usr/bin/systemctl reload php-fpm
```

Grant the app DB user only schema/DML on `dummy_cms` (no `FILE` / `SUPER` / global privileges). `mysqldump` needs `SELECT`, `LOCK TABLES`, `SHOW VIEW`, `TRIGGER`, and `ROUTINE` on that database.

Nginx `root` must be `/opt/dummy-cms/current/public` with `$realpath_root` for PHP. Ensure `www-data` can traverse `/opt/dummy-cms` (group `www-data`, directories `2775`).

### Database backup before migrate

Every deploy runs `scripts/backup-database.sh` before `php artisan migrate --force`. Backups land in `/opt/dummy-cms/shared/backups/pre-migrate-<release-id>-<utc>.sql.gz` (MySQL) or `.sqlite.gz` (SQLite). The newest 10 backups are retained.

## Rollback

```bash
bash /opt/dummy-cms/current/scripts/rollback-release.sh /opt/dummy-cms http://127.0.0.1/health
```

Restores the previous application files only. To restore schema/data after a bad migration, use the matching file under `shared/backups/` (e.g. `gunzip < …sql.gz | mysql …`).

## Validation

```bash
node --test scripts/tests/release-trust.test.mjs
bash scripts/tests/deployment.test.sh
bash scripts/smoke-check.sh http://127.0.0.1/health
```
