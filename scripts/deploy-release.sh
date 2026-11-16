#!/usr/bin/env bash
# Executed on Ubuntu via SSH. The archive signature is checked before transfer.
set -euo pipefail
root=${1:?deploy root}
archive=${2:?archive}
release_id=${3:?release id}
health_url=${4:?health URL}
[[ "$root" = /* && "$root" != / && "$release_id" =~ ^[0-9]+-[0-9]+$ ]] || exit 1
test -f "$root/shared/.env"
mkdir -p "$root/releases"
exec 9>"$root/deploy.lock"
flock -n 9 || { echo 'Another deployment is running'; exit 1; }
release="$root/releases/$release_id"
test ! -e "$release"
previous=''
if [[ -L "$root/current" ]]; then
  previous=$(readlink -f "$root/current")
  [[ "$previous" == "$root/releases/"* && -d "$previous" ]] || exit 1
fi
if [[ ! -L "$root/current" && -e "$root/current" ]]; then
  echo 'current must be a symlink' >&2
  exit 1
fi
switched=0

php_fpm_unit() {
  local unit sock
  # Optional override from the server environment / deploy wrapper.
  if [[ -n "${PHP_FPM_SERVICE:-}" ]]; then
    printf '%s\n' "${PHP_FPM_SERVICE%.service}"
    return 0
  fi
  for unit in php8.5-fpm php8.4-fpm php8.3-fpm php-fpm; do
    if [[ -f "/usr/lib/systemd/system/${unit}.service" || -f "/lib/systemd/system/${unit}.service" ]]; then
      printf '%s\n' "$unit"
      return 0
    fi
  done
  # Fallback: infer from the running FPM socket created by the package.
  for sock in /run/php/php*-fpm.sock; do
    [[ -S "$sock" ]] || continue
    unit=$(basename "$sock" .sock)
    printf '%s\n' "$unit"
    return 0
  done
  return 1
}

reload_php_fpm() {
  local unit
  unit=$(php_fpm_unit) || {
    echo 'No php*-fpm systemd unit found to reload' >&2
    echo 'Checked unit files under /usr/lib/systemd/system and /lib/systemd/system,' >&2
    echo 'and sockets under /run/php. Set PHP_FPM_SERVICE=php8.5-fpm if needed.' >&2
    ls -l /run/php 2>/dev/null || true
    return 1
  }
  echo "Reloading ${unit}"
  sudo -n systemctl reload "$unit"
}

recover() {
  status=$?
  trap - EXIT
  if (( status != 0 && switched == 1 )); then
    if [[ -n "$previous" && -d "$previous" ]]; then
      ln -s "$previous" "$root/current.recovery"
      mv -Tf "$root/current.recovery" "$root/current"
      reload_php_fpm || true
      (cd "$previous" && php artisan queue:restart) || true
      echo "Restored previous application: $previous" >&2
    else
      rm -f "$root/current"
      echo 'First deployment failed; no previous release exists.' >&2
    fi
    echo 'Database migrations were NOT reversed.' >&2
  fi
  exit "$status"
}
trap recover EXIT
mkdir "$release"
tar -xzf "$archive" -C "$release"
test -f "$release/vendor/autoload.php"
test -f "$release/public/build/manifest.json"
test ! -e "$release/.env"
test ! -e "$release/storage"
# Nginx/PHP-FPM run as www-data and must be able to traverse the release tree.
chgrp -R www-data "$release"
find "$release" -type d -exec chmod 2755 {} +
find "$release" -type f -exec chmod 0644 {} +
chmod -R g+rwX "$root" "$root/releases" "$root/shared" || true
mkdir -p "$root/shared/storage/app/public" "$root/shared/storage/app/private" \
  "$root/shared/storage/framework/cache/data" "$root/shared/storage/framework/sessions" \
  "$root/shared/storage/framework/views" "$root/shared/storage/logs"
ln -s "$root/shared/.env" "$release/.env"
ln -s "$root/shared/storage" "$release/storage"
mkdir -p "$release/bootstrap/cache"
chmod -R g+rwX "$release/bootstrap/cache" "$root/shared/storage"
cd "$release"
php artisan package:discover --ansi
php artisan storage:link
php artisan config:cache
php artisan route:cache
# Cache views on demand: shared compiled views must not be cleared mid-release.
php artisan migrate --force
ln -s "$release" "$root/current.next"
mv -Tf "$root/current.next" "$root/current"
switched=1
reload_php_fpm
php artisan queue:restart
smoke_script="$release/scripts/smoke-check.sh"
healthy=0
for attempt in $(seq 1 12); do
  if [[ -x "$smoke_script" ]] || [[ -f "$smoke_script" ]]; then
    if bash "$smoke_script" "$health_url"; then
      healthy=1
      break
    fi
  elif curl --fail --location --silent --show-error --max-time 10 "$health_url" >/dev/null; then
    healthy=1
    break
  fi
  sleep 5
done
[[ "$healthy" == 1 ]] || { echo 'Smoke / health checks failed' >&2; exit 1; }
if [[ -n "$previous" && -d "$previous" ]]; then
  ln -s "$previous" "$root/previous.next"
  mv -Tf "$root/previous.next" "$root/previous"
fi
rm -f "$archive"
echo "Deployed $release_id"
