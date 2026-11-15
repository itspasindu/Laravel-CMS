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
jq -n --arg commit "$GITHUB_SHA" --arg repository "$GITHUB_REPOSITORY" \
  --arg run_id "$GITHUB_RUN_ID" --arg version "$(date -u +%Y.%m).${GITHUB_RUN_NUMBER}.${GITHUB_RUN_ATTEMPT}" \
  '{commit: $commit, repository: $repository, run_id: $run_id, version: $version, runtime: "laravel-php83"}' > "$stage/release.json"
tar -czf release.tar.gz -C "$stage" .
