#!/bin/bash
# =========================================================
# Planet Hosts Master Panel - Update Tool
# =========================================================
# Verifies license, checks for updates, backs up current
# installation, downloads and applies updates, runs
# migrations, clears caches, and restarts services.

set -eo pipefail

PANEL_DIR="/var/www/radiohosting"
LOG_DIR="/var/log/planethosts"
BACKUP_DIR="/var/backups/planethosts"
LICENSE_FILE="/etc/planethosts/license.json"
UPDATE_URL="https://license.planet-hosts.com/api/check-update"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

log() {
    local msg="$1"
    local ts=$(date '+%Y-%m-%d %H:%M:%S')
    mkdir -p "$LOG_DIR"
    echo "$ts | UPDATE | $msg" >> "$LOG_DIR/update.log"
    echo "[UPDATE] $msg"
}

# --- License Check ---
log "Verifying license..."
if [ ! -f "$LICENSE_FILE" ]; then
    log "FAIL: No license found. Run license-activate.sh first."
    exit 1
fi

LICENSE_KEY=$(python3 -c "import json; d=json.load(open('$LICENSE_FILE')); print(d.get('license_key',''))" 2>/dev/null || echo "")
CHANNEL=$(python3 -c "import json; d=json.load(open('$LICENSE_FILE')); print(d.get('update_channel','stable'))" 2>/dev/null || echo "stable")

if [ -z "$LICENSE_KEY" ]; then
    log "FAIL: Invalid license file."
    exit 1
fi

log "License OK (channel: $CHANNEL)"

# --- Check for Updates ---
log "Checking for updates..."
CURRENT_VERSION=$(cat "$PANEL_DIR/VERSION" 2>/dev/null || echo "1.0.0")
SERVER_IP=$(curl -s --max-time 5 https://ifconfig.me/ip 2>/dev/null || hostname -I | awk '{print $1}')

RESPONSE=$(curl -s --max-time 30 \
    -X POST "$UPDATE_URL" \
    -H "Content-Type: application/json" \
    -d "{
        \"license_key\": \"$LICENSE_KEY\",
        \"current_version\": \"$CURRENT_VERSION\",
        \"channel\": \"$CHANNEL\",
        \"ip\": \"$SERVER_IP\"
    }" 2>/dev/null || echo "HTTP_ERROR")

if echo "$RESPONSE" | grep -q "HTTP_ERROR"; then
    log "WARNING: Could not reach update server."
    echo ""
    echo "WARNING: Cannot check for updates. Network issue?"
    echo "Continuing with current version."
    exit 0
fi

STATUS=$(echo "$RESPONSE" | python3 -c "import sys,json; d=json.load(sys.stdin); print(d.get('status','error'))" 2>/dev/null || echo "error")

if [ "$STATUS" != "update_available" ]; then
    log "No updates available. Current version: $CURRENT_VERSION"
    echo ""
    echo "Panel is up to date (v$CURRENT_VERSION)."
    exit 0
fi

UPDATE_VERSION=$(echo "$RESPONSE" | python3 -c "import sys,json; d=json.load(sys.stdin); print(d.get('version',''))" 2>/dev/null || echo "")
DOWNLOAD_URL=$(echo "$RESPONSE" | python3 -c "import sys,json; d=json.load(sys.stdin); print(d.get('download_url',''))" 2>/dev/null || echo "")
CHANGELOG=$(echo "$RESPONSE" | python3 -c "import sys,json; d=json.load(sys.stdin); print(d.get('changelog',''))" 2>/dev/null || echo "")

log "Update available: v$CURRENT_VERSION -> v$UPDATE_VERSION"

echo ""
echo "=================================================="
echo " Update Available"
echo "=================================================="
echo "  Current: v$CURRENT_VERSION"
echo "  New:     v$UPDATE_VERSION"
echo ""
if [ -n "$CHANGELOG" ]; then
    echo "  Changelog:"
    echo "$CHANGELOG" | sed 's/^/    /'
    echo ""
fi
read -p "Apply update? [Y/n]: " CONFIRM
CONFIRM=${CONFIRM:-Y}
if [[ "$CONFIRM" != "Y" && "$CONFIRM" != "y" ]]; then
    log "Update cancelled by user."
    echo "Update cancelled."
    exit 0
fi

# --- Backup ---
log "Creating backup..."
BACKUP_FILE="$BACKUP_DIR/pre-update-$(date +%Y%m%d%H%M%S)-v$CURRENT_VERSION.tar.gz"
mkdir -p "$BACKUP_DIR"
tar -czf "$BACKUP_FILE" -C "$PANEL_DIR" . 2>/dev/null || {
    log "WARNING: Backup failed, continuing anyway."
}
log "Backup saved: $BACKUP_FILE"

# --- Download Update ---
log "Downloading update..."
UPDATE_TMP=$(mktemp -d)
curl -sL --max-time 120 "$DOWNLOAD_URL" -o "$UPDATE_TMP/update.tar.gz" || {
    log "FAIL: Download failed."
    rm -rf "$UPDATE_TMP"
    exit 1
}
log "Download complete."

# --- Apply Update ---
log "Applying update..."
tar -xzf "$UPDATE_TMP/update.tar.gz" -C "$PANEL_DIR" 2>/dev/null || {
    log "FAIL: Extract failed."
    rm -rf "$UPDATE_TMP"
    exit 1
}
rm -rf "$UPDATE_TMP"
log "Update files applied."

# --- Run Migrations ---
log "Running migrations..."
if [ -f "$SCRIPT_DIR/migration.sh" ]; then
    bash "$SCRIPT_DIR/migration.sh" || true
fi

# --- Update Plugins ---
log "Updating plugins..."
if [ -f "$SCRIPT_DIR/plugin-installer.sh" ]; then
    bash "$SCRIPT_DIR/plugin-installer.sh" || true
fi

# --- Clear Cache ---
log "Clearing cache..."
if [ -f "$PANEL_DIR/artisan" ]; then
    php "$PANEL_DIR/artisan" config:clear 2>/dev/null || true
    php "$PANEL_DIR/artisan" route:clear 2>/dev/null || true
    php "$PANEL_DIR/artisan" view:clear 2>/dev/null || true
fi

# --- Rebuild Cache ---
log "Rebuilding cache..."
if [ -f "$PANEL_DIR/artisan" ]; then
    php "$PANEL_DIR/artisan" config:cache 2>/dev/null || true
    php "$PANEL_DIR/artisan" route:cache 2>/dev/null || true
    php "$PANEL_DIR/artisan" view:cache 2>/dev/null || true
    php "$PANEL_DIR/artisan" planethosts:cache-menus 2>/dev/null || true
fi

# --- Update Version File ---
echo "$UPDATE_VERSION" > "$PANEL_DIR/VERSION"

# --- Run Health Check ---
log "Running health check..."
if [ -f "$SCRIPT_DIR/healthcheck.sh" ]; then
    bash "$SCRIPT_DIR/healthcheck.sh" || true
fi

# --- Restart Services ---
log "Restarting services..."
systemctl restart httpd 2>/dev/null || true
systemctl restart mariadb 2>/dev/null || true
systemctl restart php-fpm 2>/dev/null || true
log "Services restarted."

echo ""
echo "=================================================="
echo " Update Complete"
echo "=================================================="
echo "  v$CURRENT_VERSION -> v$UPDATE_VERSION"
echo "  Backup: $BACKUP_FILE"
echo ""

log "Update to v$UPDATE_VERSION complete."

exit 0
