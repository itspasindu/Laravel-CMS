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
recover() {
  status=$?
  trap - EXIT
  if (( status != 0 && switched == 1 )); then
    if [[ -n "$previous" && -d "$previous" ]]; then
      ln -s "$previous" "$root/current.recovery"
      mv -Tf "$root/current.recovery" "$root/current"
      sudo -n systemctl reload php8.3-fpm || true
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
sudo -n systemctl reload php8.3-fpm
php artisan queue:restart
healthy=0
for attempt in $(seq 1 12); do
  if curl --fail --location --silent --show-error --max-time 10 "$health_url" >/dev/null; then
    healthy=1
    break
  fi
  sleep 5
done
[[ "$healthy" == 1 ]] || { echo 'Health check failed' >&2; exit 1; }
if [[ -n "$previous" && -d "$previous" ]]; then
  ln -s "$previous" "$root/previous.next"
  mv -Tf "$root/previous.next" "$root/previous"
fi
rm -f "$archive"
echo "Deployed $release_id"
