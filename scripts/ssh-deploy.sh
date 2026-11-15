#!/usr/bin/env bash
set -euo pipefail
[[ "$HOST" =~ ^[a-zA-Z0-9][a-zA-Z0-9.-]*$ ]] || { echo 'Invalid STAGING_HOST'; exit 1; }
[[ "$DEPLOY_USER" =~ ^[a-z_][a-z0-9_-]*$ ]] || { echo 'Invalid STAGING_USER'; exit 1; }
[[ "$DEPLOY_ROOT" =~ ^/[-a-zA-Z0-9_/]+$ && "$DEPLOY_ROOT" != / ]] || exit 1
[[ "$HEALTH_URL" =~ ^https?:// ]] || { echo 'Set STAGING_HEALTH_URL'; exit 1; }
target="${DEPLOY_USER}@${HOST}"
ssh_options=(-o BatchMode=yes -o StrictHostKeyChecking=yes -o ConnectTimeout=15)
release_id="${GITHUB_RUN_ID}-${GITHUB_RUN_ATTEMPT}"
remote_archive="$DEPLOY_ROOT/incoming/$release_id.tar.gz"
printf -v command 'mkdir -p %q' "$DEPLOY_ROOT/incoming"
ssh "${ssh_options[@]}" "$target" "$command"
scp "${ssh_options[@]}" signed-release/release.tar.gz "$target:$remote_archive"
printf -v command 'bash -s -- %q %q %q %q' "$DEPLOY_ROOT" "$remote_archive" "$release_id" "$HEALTH_URL"
ssh "${ssh_options[@]}" "$target" "$command" < scripts/deploy-release.sh
