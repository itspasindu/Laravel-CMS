#!/usr/bin/env bash
# Configure a least-privilege deploy user, SSH key restrictions, and sudoers
# for php-fpm reload only. Run as root on the Ubuntu staging host.
#
# Usage:
#   sudo bash scripts/setup-least-privilege-deploy.sh
#   sudo DEPLOY_USER=deploy DEPLOY_ROOT=/opt/dummy-cms bash scripts/setup-least-privilege-deploy.sh
set -euo pipefail

if [[ "$(id -u)" -ne 0 ]]; then
  echo 'Run as root (sudo).' >&2
  exit 1
fi

DEPLOY_USER=${DEPLOY_USER:-deploy}
DEPLOY_ROOT=${DEPLOY_ROOT:-/opt/dummy-cms}
DEPLOY_GROUP=${DEPLOY_GROUP:-www-data}
SSH_DIR="/home/${DEPLOY_USER}/.ssh"
KEY_PATH="${SSH_DIR}/cms_deploy_ed25519"
SUDOERS_FILE="/etc/sudoers.d/90-${DEPLOY_USER}-php-fpm"

if ! id -u "$DEPLOY_USER" >/dev/null 2>&1; then
  useradd --create-home --shell /bin/bash --user-group "$DEPLOY_USER"
fi

if getent group "$DEPLOY_GROUP" >/dev/null 2>&1; then
  usermod -aG "$DEPLOY_GROUP" "$DEPLOY_USER"
fi

install -d -o "$DEPLOY_USER" -g "$DEPLOY_GROUP" -m 2775 \
  "$DEPLOY_ROOT" \
  "$DEPLOY_ROOT/shared" \
  "$DEPLOY_ROOT/shared/storage" \
  "$DEPLOY_ROOT/shared/backups" \
  "$DEPLOY_ROOT/releases" \
  "$DEPLOY_ROOT/incoming"

install -d -o "$DEPLOY_USER" -g "$DEPLOY_USER" -m 700 "$SSH_DIR"

if [[ ! -f "$KEY_PATH" ]]; then
  sudo -u "$DEPLOY_USER" ssh-keygen -t ed25519 -N '' -f "$KEY_PATH" -C "cms-deploy@$(hostname -s)"
fi

pubkey=$(cat "${KEY_PATH}.pub")
# Dedicated deploy key: no port/agent/X11 forwarding, no PTY. Keep command=
# unset so GitHub Actions can still scp + stream deploy-release.sh over ssh.
auth_line="no-port-forwarding,no-agent-forwarding,no-X11-forwarding,no-pty ${pubkey}"
auth_file="${SSH_DIR}/authorized_keys"
if [[ -f "$auth_file" ]] && grep -Fq "$pubkey" "$auth_file"; then
  echo "Public key already present in ${auth_file}"
else
  printf '%s\n' "$auth_line" >> "$auth_file"
fi
chown "$DEPLOY_USER:$DEPLOY_USER" "$auth_file"
chmod 600 "$auth_file"

cat > "$SUDOERS_FILE" <<EOF
# Least-privilege: only reload PHP-FPM units used by this CMS.
${DEPLOY_USER} ALL=(root) NOPASSWD: /usr/bin/systemctl reload php8.5-fpm, /usr/bin/systemctl reload php8.4-fpm, /usr/bin/systemctl reload php8.3-fpm, /usr/bin/systemctl reload php-fpm
EOF
chmod 440 "$SUDOERS_FILE"
visudo -cf "$SUDOERS_FILE"

cat <<EOF

Least-privilege deploy host ready.

SSH private key (store as GitHub staging secret SSH_PRIVATE_KEY):
  ${KEY_PATH}

Public key authorized for ${DEPLOY_USER} with:
  no-port-forwarding,no-agent-forwarding,no-X11-forwarding,no-pty

Sudoers (${SUDOERS_FILE}):
  passwordless systemctl reload for php8.3/8.4/8.5-fpm and php-fpm only

Deploy tree owned by ${DEPLOY_USER}:${DEPLOY_GROUP}:
  ${DEPLOY_ROOT}

MySQL (recommended grants for ${DEPLOY_USER} app user — adjust names):
  GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX, DROP, REFERENCES
    ON dummy_cms.* TO 'dummy_cms'@'localhost' IDENTIFIED BY '...';
  GRANT LOCK TABLES, SHOW VIEW, TRIGGER, ROUTINE ON dummy_cms.* TO 'dummy_cms'@'localhost';
  FLUSH PRIVILEGES;

Do NOT grant FILE, SUPER, PROCESS, or global privileges to the app/deploy DB user.
EOF
