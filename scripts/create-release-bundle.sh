#!/usr/bin/env bash
set -euo pipefail
stage=$(mktemp -d)
trap 'rm -rf "$stage"' EXIT
git archive HEAD | tar -x -C "$stage"
cp composer.lock "$stage/composer.lock"
cp -a vendor "$stage/vendor"
mkdir -p "$stage/public/build"
cp -a public/build/. "$stage/public/build/"
# Never package CI environment, runtime caches, or uploads.
rm -rf "$stage/storage" "$stage/bootstrap/cache"
mkdir -p "$stage/bootstrap/cache"

short_sha=${GITHUB_SHA:0:7}
version="$(date -u +%Y.%m).${GITHUB_RUN_NUMBER}.${GITHUB_RUN_ATTEMPT}+${short_sha}"

# SBOM (CycloneDX) via Syft binary when available; otherwise write package inventories.
mkdir -p "$stage/sbom"
if command -v syft >/dev/null 2>&1; then
  syft "dir:${stage}" -o cyclonedx-json="$stage/sbom/cyclonedx.json"
else
  composer show --locked --format=json > "$stage/sbom/composer-locked.json" 2>/dev/null \
    || composer show --format=json > "$stage/sbom/composer.json"
  if [[ -f package-lock.json ]]; then
    cp package-lock.json "$stage/sbom/package-lock.json"
  fi
  printf '%s\n' '{"bomFormat":"CycloneDX","specVersion":"1.5","note":"fallback inventory without syft"}' \
    > "$stage/sbom/cyclonedx.json"
fi

# Checksum every packaged file (excluding the checksum file itself).
(
  cd "$stage"
  find . -type f ! -name CHECKSUMS.sha256 -print0 \
    | sort -z \
    | xargs -0 sha256sum
) > "$stage/CHECKSUMS.sha256"

jq -n \
  --arg commit "$GITHUB_SHA" \
  --arg repository "$GITHUB_REPOSITORY" \
  --arg run_id "$GITHUB_RUN_ID" \
  --arg version "$version" \
  '{
     commit: $commit,
     repository: $repository,
     run_id: $run_id,
     version: $version,
     runtime: "laravel-php83",
     packaging: "ssh-release-no-docker"
   }' > "$stage/release.json"

tar -czf release.tar.gz -C "$stage" .
echo "Created release ${version}"
