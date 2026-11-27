#!/usr/bin/env bash
# Create a pre-migrate database backup under $ROOT/shared/backups.
# Usage: bash scripts/backup-database.sh <deploy-root> <release-id>
set -euo pipefail

root=${1:?deploy root}
release_id=${2:?release id}
env_file="${root}/shared/.env"
backup_dir="${root}/shared/backups"
[[ "$root" = /* && "$root" != / ]] || exit 1
[[ "$release_id" =~ ^[0-9]+-[0-9]+$ ]] || exit 1
test -f "$env_file"

# Read KEY=value from .env without sourcing (avoids executing shell metacharacters).
env_get() {
  local key=$1 line value
  line=$(grep -E "^${key}=" "$env_file" | tail -n 1 || true)
  [[ -n "$line" ]] || { printf ''; return 0; }
  value=${line#*=}
  value=${value%$'\r'}
  if [[ "$value" =~ ^\"(.*)\"$ ]]; then
    value=${BASH_REMATCH[1]}
  elif [[ "$value" =~ ^\'(.*)\'$ ]]; then
    value=${BASH_REMATCH[1]}
  fi
  printf '%s' "$value"
}

mkdir -p "$backup_dir"
chmod 750 "$backup_dir" 2>/dev/null || true

connection=$(env_get DB_CONNECTION)
connection=${connection:-mysql}
stamp=$(date -u +%Y%m%dT%H%M%SZ)
outfile="${backup_dir}/pre-migrate-${release_id}-${stamp}"

case "$connection" in
  sqlite)
    db_path=$(env_get DB_DATABASE)
    if [[ -z "$db_path" || "$db_path" == ':memory:' ]]; then
      echo 'SQLite backup skipped (empty or in-memory database).'
      exit 0
    fi
    if [[ "$db_path" != /* ]]; then
      db_path="${root}/current/${db_path}"
    fi
    if [[ ! -f "$db_path" ]]; then
      echo "SQLite database not found at ${db_path}; skipping backup (first deploy or empty DB)."
      exit 0
    fi
    cp -a "$db_path" "${outfile}.sqlite"
    gzip -f "${outfile}.sqlite"
    echo "Wrote ${outfile}.sqlite.gz"
    ;;
  mysql|mariadb)
    host=$(env_get DB_HOST); host=${host:-127.0.0.1}
    port=$(env_get DB_PORT); port=${port:-3306}
    database=$(env_get DB_DATABASE)
    username=$(env_get DB_USERNAME)
    password=$(env_get DB_PASSWORD)
    [[ -n "$database" && -n "$username" ]] || {
      echo 'DB_DATABASE and DB_USERNAME are required for MySQL backups.' >&2
      exit 1
    }
    if ! command -v mysqldump >/dev/null 2>&1; then
      echo 'mysqldump is required on the server for MySQL pre-migrate backups.' >&2
      exit 1
    fi
    export MYSQL_PWD="$password"
    mysqldump \
      --host="$host" \
      --port="$port" \
      --user="$username" \
      --single-transaction \
      --routines \
      --triggers \
      --databases "$database" \
      | gzip -c > "${outfile}.sql.gz"
    unset MYSQL_PWD
    echo "Wrote ${outfile}.sql.gz"
    ;;
  *)
    echo "Unsupported DB_CONNECTION=${connection}; skipping backup." >&2
    exit 1
    ;;
esac

# Keep the newest 10 backups; delete older ones.
mapfile -t old_backups < <(ls -1t "$backup_dir"/pre-migrate-* 2>/dev/null || true)
if (( ${#old_backups[@]} > 10 )); then
  printf '%s\n' "${old_backups[@]:10}" | while read -r stale; do
    rm -f "$stale"
  done
fi
