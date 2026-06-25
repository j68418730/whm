#!/bin/bash
# =========================================================
# Planet Hosts Master Panel - Setup
# =========================================================
# Runs once after successful license activation.
# Verifies services, creates admin account, imports defaults,
# builds caches, and marks installation as complete.

set -eo pipefail

PANEL_DIR="/var/www/radiohosting"
LOG_DIR="/var/log/planethosts"
MARKER="$PANEL_DIR/.installed"

log() {
    local msg="$1"
    local ts=$(date '+%Y-%m-%d %H:%M:%S')
    mkdir -p "$LOG_DIR"
    echo "$ts | SETUP | $msg" >> "$LOG_DIR/setup.log"
    echo "[SETUP] $msg"
}

# --- Check if already installed ---
if [ -f "$MARKER" ]; then
    log "Panel already installed ($MARKER exists). Exiting."
    exit 0
fi

log "Starting initial setup..."

# --- Verify License ---
log "Verifying license..."
if [ ! -f /etc/planethosts/license.json ]; then
    echo "ERROR: No license found at /etc/planethosts/license.json"
    echo "Run license-activate.sh first."
    exit 1
fi
log "License verified."

# --- Verify Services ---
log "Verifying services..."
for svc in httpd mariadb php-fpm; do
    if systemctl is-active --quiet "$svc" 2>/dev/null; then
        log "  $svc: OK"
    else
        log "  $svc: WARNING - not active"
    fi
done

# --- Verify FFmpeg ---
if command -v ffmpeg >/dev/null 2>&1; then
    log "  FFmpeg: OK"
else
    log "  FFmpeg: WARNING - not found"
fi

# --- Verify Icecast ---
if command -v icecast >/dev/null 2>&1 || systemctl list-units --type=service 2>/dev/null | grep -q icecast; then
    log "  Icecast: OK"
else
    log "  Icecast: WARNING - not found"
fi

# --- Verify Liquidsoap ---
if command -v liquidsoap >/dev/null 2>&1; then
    log "  Liquidsoap: OK"
else
    log "  Liquidsoap: WARNING - not found"
fi

# --- Verify Firewall ---
if systemctl is-active --quiet firewalld 2>/dev/null; then
    log "  Firewall: OK"
else
    log "  Firewall: WARNING - not active"
fi

# --- Verify Cron ---
if systemctl is-active --quiet crond 2>/dev/null; then
    log "  Cron: OK"
else
    log "  Cron: WARNING - not active"
fi

# --- Create Storage Directories ---
log "Creating storage directories..."
for dir in storage logs cache uploads backups temp; do
    mkdir -p "$PANEL_DIR/$dir"
    chown apache:apache "$PANEL_DIR/$dir" 2>/dev/null || true
    chmod 755 "$PANEL_DIR/$dir" 2>/dev/null || true
done
log "Storage directories created."

# --- Set Permissions ---
log "Setting permissions..."
chown -R apache:apache "$PANEL_DIR" 2>/dev/null || true
find "$PANEL_DIR" -type d -exec chmod 755 {} \; 2>/dev/null || true
find "$PANEL_DIR" -type f -exec chmod 644 {} \; 2>/dev/null || true
chmod 600 "$PANEL_DIR/.env" 2>/dev/null || true
log "Permissions set."

# --- Generate Application Keys ---
log "Generating application keys..."
if [ -f "$PANEL_DIR/artisan" ]; then
    php "$PANEL_DIR/artisan" key:generate --force 2>/dev/null || true
    log "App key generated."
fi

# --- Generate Security Salts ---
log "Generating security salts..."
SALTS_FILE="$PANEL_DIR/config/salts.php"
mkdir -p "$(dirname "$SALTS_FILE")"
cat > "$SALTS_FILE" <<EOF
<?php
return [
    'cipher' => 'AES-256-CBC',
    'key' => '$(openssl rand -hex 32)',
    'salt' => '$(openssl rand -hex 16)',
    'pepper' => '$(openssl rand -hex 16)',
    'auth_key' => '$(openssl rand -hex 32)',
    'token_key' => '$(openssl rand -hex 16)',
];
EOF
chmod 600 "$SALTS_FILE"
log "Security salts generated."

# --- Create Default Admin Account ---
log "Creating default admin account..."
if [ -f "$PANEL_DIR/artisan" ]; then
    php "$PANEL_DIR/artisan" planethosts:create-admin 2>/dev/null || true
    log "Default admin account processed."
fi

# --- Import Default Roles ---
log "Importing default roles..."
if [ -f "$PANEL_DIR/database/seeders/RoleSeeder.php" ]; then
    php "$PANEL_DIR/artisan" db:seed --class=RoleSeeder 2>/dev/null || true
    log "Default roles imported."
fi

# --- Import Default Settings ---
log "Importing default settings..."
if [ -f "$PANEL_DIR/database/seeders/SettingsSeeder.php" ]; then
    php "$PANEL_DIR/artisan" db:seed --class=SettingsSeeder 2>/dev/null || true
    log "Default settings imported."
fi

# --- Import Default DNS Templates ---
if [ -f "$PANEL_DIR/database/seeders/DnsTemplateSeeder.php" ]; then
    php "$PANEL_DIR/artisan" db:seed --class=DnsTemplateSeeder 2>/dev/null || true
    log "DNS templates imported."
fi

# --- Import Default Email Templates ---
if [ -f "$PANEL_DIR/database/seeders/EmailTemplateSeeder.php" ]; then
    php "$PANEL_DIR/artisan" db:seed --class=EmailTemplateSeeder 2>/dev/null || true
    log "Email templates imported."
fi

# --- Import Default Firewall Templates ---
if [ -f "$PANEL_DIR/database/seeders/FirewallSeeder.php" ]; then
    php "$PANEL_DIR/artisan" db:seed --class=FirewallSeeder 2>/dev/null || true
    log "Firewall templates imported."
fi

# --- Import Default Hosting Packages ---
if [ -f "$PANEL_DIR/database/seeders/PackageSeeder.php" ]; then
    php "$PANEL_DIR/artisan" db:seed --class=PackageSeeder 2>/dev/null || true
    log "Default hosting packages imported."
fi

# --- Import Default Dashboard Widgets ---
if [ -f "$PANEL_DIR/database/seeders/WidgetSeeder.php" ]; then
    php "$PANEL_DIR/artisan" db:seed --class=WidgetSeeder 2>/dev/null || true
    log "Default dashboard widgets imported."
fi

# --- Register Core Menus ---
log "Registering menus and permissions..."
if [ -f "$PANEL_DIR/artisan" ]; then
    php "$PANEL_DIR/artisan" planethosts:register-menus 2>/dev/null || true
    php "$PANEL_DIR/artisan" planethosts:register-permissions 2>/dev/null || true
    php "$PANEL_DIR/artisan" planethosts:register-routes 2>/dev/null || true
    php "$PANEL_DIR/artisan" planethosts:register-widgets 2>/dev/null || true
    log "Menus, permissions, routes, widgets registered."
fi

# --- Build Caches ---
log "Building caches..."
if [ -f "$PANEL_DIR/artisan" ]; then
    php "$PANEL_DIR/artisan" config:cache 2>/dev/null || true
    php "$PANEL_DIR/artisan" route:cache 2>/dev/null || true
    php "$PANEL_DIR/artisan" view:cache 2>/dev/null || true
    php "$PANEL_DIR/artisan" planethosts:cache-menus 2>/dev/null || true
    log "Caches built."
fi

# --- Run Plugin Installer ---
log "Running plugin installer..."
if [ -f "$SCRIPT_DIR/plugin-installer.sh" ]; then
    bash "$SCRIPT_DIR/plugin-installer.sh" || true
    log "Plugin installer finished."
fi

# --- Run Health Check ---
log "Running health check..."
if [ -f "$SCRIPT_DIR/healthcheck.sh" ]; then
    bash "$SCRIPT_DIR/healthcheck.sh" || true
    log "Health check completed."
fi

# --- Mark as Installed ---
touch "$MARKER"
log "Setup complete. Marker created at $MARKER."

echo ""
echo "=================================================="
echo " Setup Complete"
echo "=================================================="
echo ""
echo "Panel is ready at http://$(curl -s ifconfig.me 2>/dev/null || hostname -I | awk '{print $1}')/"
echo ""

exit 0
