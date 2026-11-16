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
  echo "Checking ${url}"
  curl --fail --location --silent --show-error --max-time 15 "$url" >/dev/null
}

check /health
check /up
check /
check /login
echo "Smoke checks passed for ${base}"
