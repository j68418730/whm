#!/bin/bash
# =========================================================
# Planet Hosts - Permissions Module
# =========================================================

set -eo pipefail

PANEL_DIR="/var/www/radiohosting"
LOG_DIR="/var/log/planethosts"

log() { local m="$1"; echo "$(date '+%Y-%m-%d %H:%M:%S') | PERMISSIONS | $m" >> "$LOG_DIR/permissions.log"; echo "[PERMISSIONS] $m"; }

set_default_permissions() {
    log "Setting default permissions..."
    chown -R apache:apache "$PANEL_DIR" 2>/dev/null || true
    find "$PANEL_DIR" -type d -exec chmod 755 {} \; 2>/dev/null || true
    find "$PANEL_DIR" -type f -exec chmod 644 {} \; 2>/dev/null || true
    chmod 600 "$PANEL_DIR/.env" 2>/dev/null || true
    log "Default permissions set."
}

set_storage_permissions() {
    log "Setting storage permissions..."
    for dir in storage logs cache uploads backups temp; do
        mkdir -p "$PANEL_DIR/$dir"
        chown apache:apache "$PANEL_DIR/$dir" 2>/dev/null || true
        chmod 755 "$PANEL_DIR/$dir" 2>/dev/null || true
    done
    log "Storage permissions set."
}

secure_sensitive_files() {
    log "Securing sensitive files..."
    [ -f "$PANEL_DIR/.env" ] && chmod 600 "$PANEL_DIR/.env"
    [ -f /etc/planethosts/license.json ] && chmod 600 /etc/planethosts/license.json
    find "$PANEL_DIR/config" -name "*.php" -exec chmod 640 {} \; 2>/dev/null || true
    log "Sensitive files secured."
}

case "${1:-all}" in
    default) set_default_permissions ;;
    storage) set_storage_permissions ;;
    secure) secure_sensitive_files ;;
    all)
        set_default_permissions
        set_storage_permissions
        secure_sensitive_files
        ;;
    *) echo "Usage: $0 {default|storage|secure|all}" ;;
esac
