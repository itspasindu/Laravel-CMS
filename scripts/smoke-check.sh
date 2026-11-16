#!/usr/bin/env bash
# Post-deploy / local smoke checks. No Docker required.
set -euo pipefail
target=${1:?health or base URL}
base=${target%/}
base=${base%/health}
base=${base%/up}

check() {
  local path=$1
  local url="${base}${path}"
  local code body
  echo "Checking ${url}"
  body=$(mktemp)
  code=$(curl --location --silent --show-error --max-time 15 --output "$body" --write-out '%{http_code}' "$url" || true)
  if [[ "$code" != "200" ]]; then
    echo "Smoke check failed for ${url} (HTTP ${code})" >&2
    head -c 1200 "$body" >&2 || true
    echo >&2
    rm -f "$body"
    return 1
  fi
  rm -f "$body"
}

check /health
check /up
check /
check /login
echo "Smoke checks passed for ${base}"
