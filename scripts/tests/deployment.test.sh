#!/usr/bin/env bash
# Exercise deployment failure boundaries without a real PHP service or database.
set -euo pipefail
scripts=$(cd "$(dirname "$0")/.." && pwd)
temp=$(mktemp -d)
trap 'rm -rf "$temp"' EXIT
mkdir -p "$temp/bin" "$temp/package/vendor" "$temp/package/public/build" "$temp/package/bootstrap/cache" "$temp/package/scripts"
touch "$temp/package/vendor/autoload.php" "$temp/package/public/build/manifest.json"
cp "$scripts/backup-database.sh" "$temp/package/scripts/backup-database.sh"
tar -czf "$temp/release.tar.gz" -C "$temp/package" .
cat > "$temp/bin/php" <<'MOCK'
#!/usr/bin/env bash
if [[ "${FAIL_MIGRATION:-0}" == 1 && "$*" == *'migrate --force'* ]]; then exit 1; fi
exit 0
MOCK
cat > "$temp/bin/curl" <<'MOCK'
#!/usr/bin/env bash
exit "${FAIL_HEALTH:-0}"
MOCK
cat > "$temp/bin/mysqldump" <<'MOCK'
#!/usr/bin/env bash
echo '-- mock mysqldump'
exit 0
MOCK
printf '#!/usr/bin/env bash\nexit 0\n' > "$temp/bin/sudo"
printf '#!/usr/bin/env bash\nexit 0\n' > "$temp/bin/sleep"
chmod +x "$temp/bin/"*
# Git Bash lacks flock; Ubuntu CI exercises the real locking command.
if ! command -v flock >/dev/null; then
  printf '#!/usr/bin/env bash\nexit 0\n' > "$temp/bin/flock"
  chmod +x "$temp/bin/flock"
fi
export PATH="$temp/bin:$PATH"
export PHP_FPM_SERVICE=php8.3-fpm
for scenario in success migration health first; do
  root="$temp/$scenario"
  mkdir -p "$root/shared"
  cat > "$root/shared/.env" <<ENV
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dummy_cms
DB_USERNAME=dummy_cms
DB_PASSWORD=secret
ENV
  if [[ "$scenario" != first ]]; then
    mkdir -p "$root/releases/old"
    ln -s "$root/releases/old" "$root/current"
  fi
  cp "$temp/release.tar.gz" "$root/archive.tar.gz"
  export FAIL_MIGRATION=0 FAIL_HEALTH=0
  [[ "$scenario" != migration ]] || export FAIL_MIGRATION=1
  [[ "$scenario" != health && "$scenario" != first ]] || export FAIL_HEALTH=1
  result=0
  bash "$scripts/deploy-release.sh" "$root" "$root/archive.tar.gz" 123-1 http://localhost/health || result=$?
  if [[ "$scenario" == success ]]; then
    [[ "$result" == 0 && "$(readlink "$root/current")" == "$root/releases/123-1" ]]
    [[ "$(readlink "$root/previous")" == "$root/releases/old" ]]
    ls "$root/shared/backups"/pre-migrate-123-1-*.sql.gz >/dev/null
  elif [[ "$scenario" == first ]]; then
    [[ "$result" != 0 && ! -L "$root/current" ]]
  else
    [[ "$result" != 0 && "$(readlink "$root/current")" == "$root/releases/old" ]]
  fi
  echo "$scenario: passed"
done
