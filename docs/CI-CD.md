# Laravel CI/CD

Adapted from [itspasindu/reusable-cicd-pipeline](https://github.com/itspasindu/reusable-cicd-pipeline/tree/bc8b78a25bb59fbcc167c70144a2a63c3158e2df/.github).
This project uses PHP 8.3 on Ubuntu 24.04, Node 22 for asset builds, and an Ubuntu SSH staging target.

## Pipeline

1. PRs and pushes to `main` run Pint, PHPUnit with SQLite, release trust and deployment recovery tests, a Vite build, Composer production dependency audit, and a full npm audit (high/critical findings fail CI).
2. Successful main runs package the tested source, production Composer dependencies, built assets, and release metadata. Only that archive is signed in the `release-signing` environment. PRs have no signing or deployment credentials.
3. CD runs after successful CI, or manually with a successful main CI run ID. It verifies the source repository, event, branch, workflow, result, signature, commit, and run ID before sending the archive over SSH.
4. Deployment runs migrations, switches the `current` symlink, reloads PHP-FPM, restarts queue workers, and checks the configured HTTP URL. Failure after the switch restores the previous application; failure before the switch leaves it serving. First-deploy failure removes the failed `current` link.

Release artifacts expire after 30 days. Manual CD redeploys an existing verified artifact; it does not rebuild it. Deployments are serialized in Actions and with a server-side lock. A manually selected older run deliberately redeploys that version.

The reference's React/PostgreSQL tests, pipeline-monitor API, wiki publishing, issue automation, and developer-fix workflow are not used by this Laravel application. Results appear in Actions logs, the test report artifact, and the CD job summary. Security gates here cover dependencies; there is no PHP SAST or secret-history scanner in this version.

## GitHub configuration

Create environments named `release-signing` and `staging`. Restrict both to `main`; add reviewers if desired. Protect `main` and require the test and security checks. Keep signing credentials out of PR jobs.

| Location | Name | Value |
| --- | --- | --- |
| `release-signing` environment secret | `RELEASE_SIGNING_PRIVATE_KEY` | RSA private key, PEM format |
| Repository variable | `RELEASE_SIGNING_PUBLIC_KEY` | Matching public key, PEM format, used in signing and staging |
| `staging` environment secret | `SSH_PRIVATE_KEY` | Dedicated server deployment key |
| `staging` environment secret | `STAGING_HOST` | Server hostname or IPv4 address, without protocol/port |
| `staging` environment secret | `STAGING_USER` | Ubuntu deploy account, e.g. `deploy` |
| `staging` environment secret | `STAGING_SSH_KNOWN_HOSTS` | Trusted SSH host-key entry, verified independently |
| `staging` environment secret (optional) | `TAILSCALE_AUTHKEY` | Needed only for a private Tailscale host |
| `staging` environment variable (optional) | `STAGING_DEPLOY_ROOT` | Absolute path; default `/opt/dummy-cms` |
| `staging` environment variable | `STAGING_HEALTH_URL` | Full URL reachable **from the server**, ideally `https://cms.example.com/` to exercise database-backed rendering |

SSH uses port 22. The deploy root must contain only letters, numbers, `/`, `_`, and `-`. No GitHub PAT is required. The built-in token reads source and artifacts.

Generate signing keys on a trusted machine, outside the repository:

```bash
openssl genpkey -algorithm RSA -pkeyopt rsa_keygen_bits:3072 -out release-private.pem
openssl pkey -in release-private.pem -pubout -out release-public.pem
```

Store the private key only in the signing environment secret. Key rotation requires keeping the relevant public key available to verify older artifacts.

## Ubuntu server preparation

Install Nginx, PHP 8.3 CLI/FPM with `mbstring`, `xml`, `curl`, `intl`, `mysql`, `sqlite3`, `gd`, `zip`, and `bcmath`, plus `curl`, `tar`, and `util-linux` (for `flock`). Configure MySQL separately and create the CMS database and application account. Composer and Node are not required on the server because dependencies and assets are bundled.

Example paths below assume a pre-created `deploy` user with SSH access:

```bash
sudo usermod -aG www-data deploy
sudo install -d -o deploy -g www-data -m 2775 /opt/dummy-cms
sudo install -d -o deploy -g www-data -m 2775 /opt/dummy-cms/shared
sudo install -d -o deploy -g www-data -m 2775 /opt/dummy-cms/shared/storage
# Copy this project's .env.example to /opt/dummy-cms/shared/.env, then edit it.
sudo chown deploy:www-data /opt/dummy-cms/shared/.env
sudo chmod 640 /opt/dummy-cms/shared/.env
```

Reconnect the deploy account after changing groups. Set `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL`, a permanent `APP_KEY`, MySQL credentials, and the desired session/cache/queue settings in that shared `.env`. Generate `APP_KEY` once with `php -r 'echo "base64:".base64_encode(random_bytes(32)).PHP_EOL;'`. Never rotate it on each deployment. If using SQLite instead, use an absolute database path under `shared`, not inside a release.

Using `sudo visudo`, allow only the needed PHP service operation (confirm the `systemctl` path on your server):

```sudoers
deploy ALL=(root) NOPASSWD: /usr/bin/systemctl reload php8.3-fpm
```

Configure Nginx with document root `/opt/dummy-cms/current/public`, PHP-FPM socket `/run/php/php8.3-fpm.sock`, Laravel's `try_files $uri $uri/ /index.php?$query_string`, and `fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name`. Use `$realpath_root` to avoid serving stale PHP code across symlink switches. Deny dotfiles, except `.well-known`, and configure TLS for your hostname. Check `nginx -t` and reload Nginx. The site will not serve until the first successful deployment.

If queue jobs are used, run `php /opt/dummy-cms/current/artisan queue:work --sleep=3 --tries=3` under Supervisor/systemd with automatic restart, as the deploy account, with `WorkingDirectory=/opt/dummy-cms/current`. The pipeline calls `queue:restart`; the process manager must start a new worker. Add the Laravel scheduler cron only if scheduled tasks are needed.

The shared storage directory inherits group `www-data`; both PHP-FPM and the deploy account need write access. Preserve the setgid bit on shared directories. Keep server backups of the database, `.env`, and uploaded files.

## First run and rollback

Push these files to `main` after configuring environments. CI executes first; signing and CD follow only if all gates succeed. No workflows or secrets are created remotely merely by editing this checkout.

The repository currently has **no committed `composer.lock`**. CI therefore resolves it, tests those versions, and packages that same generated lock and production dependencies. Commit a reviewed lock from a working PHP environment for repeatable resolution across runs. Audits may expose existing dependency vulnerabilities; resolve them rather than bypassing the checks. This pipeline does not upgrade application dependencies automatically.

Application rollback on the server:

```bash
bash /opt/dummy-cms/current/scripts/rollback-release.sh /opt/dummy-cms https://cms.example.com/
```

Rollback restores the previous application, **not database changes**. Use backward-compatible migrations and back up data before schema changes. No seeders run during deployment. Old release directories are retained for operator-managed cleanup; keep `current` and `previous` targets.

## Validation

```bash
node --test scripts/tests/release-trust.test.mjs
bash scripts/tests/deployment.test.sh
actionlint
```

Deployment tests mock PHP, HTTP, service reload, and sleep, and cover success, migration failure, health failure, and first-deploy failure. Git Bash additionally mocks missing `flock`; Ubuntu CI uses real `flock`. These tests do not substitute for a first staging deployment and server health check.
