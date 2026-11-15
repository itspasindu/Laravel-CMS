#!/usr/bin/env bash
# Manual application rollback; intentionally never rolls back database migrations.
set -euo pipefail
root=${1:-/opt/dummy-cms}
health_url=${2:?Pass the staging health URL}
exec 9>"$root/deploy.lock"
flock -n 9 || { echo 'Another deployment is running'; exit 1; }
previous=$(readlink -f "$root/previous")
[[ "$previous" == "$root/releases/"* && -d "$previous" ]]
ln -s "$previous" "$root/current.rollback"
mv -Tf "$root/current.rollback" "$root/current"
sudo -n systemctl reload php8.3-fpm
(cd "$previous" && php artisan queue:restart)
curl --fail --silent --show-error --retry 5 --retry-all-errors --max-time 10 "$health_url"
echo "Restored $previous; database migrations unchanged."
