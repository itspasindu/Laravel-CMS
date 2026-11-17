#!/usr/bin/env bash
# Configure PR required checks on main and required reviewers on deploy environments.
# Usage:
#   bash scripts/configure-github-quality-gates.sh
#   REVIEWER_LOGIN=itspasindu bash scripts/configure-github-quality-gates.sh
set -euo pipefail

REPO="${REPO:-$(gh repo view --json nameWithOwner -q .nameWithOwner)}"
REVIEWER_LOGIN="${REVIEWER_LOGIN:-$(gh api user -q .login)}"
REVIEWER_ID="$(gh api "users/${REVIEWER_LOGIN}" -q .id)"

echo "Repository: ${REPO}"
echo "Reviewer:   ${REVIEWER_LOGIN} (${REVIEWER_ID})"

echo "Applying branch protection on main (required CI checks)..."
# Check names match the reusable workflow job titles as reported by Actions.
gh api \
  --method PUT \
  -H "Accept: application/vnd.github+json" \
  "repos/${REPO}/branches/main/protection" \
  --input - <<EOF
{
  "required_status_checks": {
    "strict": true,
    "contexts": [
      "test",
      "security"
    ]
  },
  "enforce_admins": false,
  "required_pull_request_reviews": {
    "required_approving_review_count": 1,
    "dismiss_stale_reviews": true
  },
  "restrictions": null,
  "required_linear_history": true,
  "allow_force_pushes": false,
  "allow_deletions": false,
  "block_creations": false,
  "required_conversation_resolution": true
}
EOF

for ENV_NAME in staging release-signing; do
  echo "Configuring environment reviewers for ${ENV_NAME}..."
  gh api \
    --method PUT \
    -H "Accept: application/vnd.github+json" \
    "repos/${REPO}/environments/${ENV_NAME}" \
    --input - <<EOF
{
  "wait_timer": 0,
  "prevent_self_review": false,
  "reviewers": [
    {
      "type": "User",
      "id": ${REVIEWER_ID}
    }
  ],
  "deployment_branch_policy": {
    "protected_branches": true,
    "custom_branch_policies": false
  }
}
EOF
done

echo
echo "Done."
echo "If required checks stay pending on a PR, open the branch protection UI and pick the exact"
echo "check names shown after a green CI run (often \"CI / test\" and \"CI / security\")."
