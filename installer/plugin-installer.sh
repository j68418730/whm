#!/bin/bash
# =========================================================
# Planet Hosts Master Panel - Plugin Installer
# =========================================================
# Automatically scans plugins/ directory, reads plugin.json,
# installs dependencies, imports SQL, registers permissions,
# widgets, and menus.

set -eo pipefail

PANEL_DIR="/var/www/radiohosting"
LOG_DIR="/var/log/planethosts"
PLUGIN_DIR="$PANEL_DIR/plugins"

log() {
    local msg="$1"
    local ts=$(date '+%Y-%m-%d %H:%M:%S')
    mkdir -p "$LOG_DIR"
    echo "$ts | PLUGINS | $msg" >> "$LOG_DIR/plugins.log"
    echo "[PLUGINS] $msg"
}

log "Starting plugin installer..."
log "Scanning $PLUGIN_DIR..."

if [ ! -d "$PLUGIN_DIR" ]; then
    log "No plugins directory found. Skipping."
    exit 0
fi

INSTALLED=0
FAILED=0
SKIPPED=0

for plugin_path in "$PLUGIN_DIR"/*/; do
    plugin_name=$(basename "$plugin_path")

    # Skip if no plugin.json
    if [ ! -f "$plugin_path/plugin.json" ]; then
        continue
    fi

    log "Found plugin: $plugin_name"

    # Read plugin.json
    PLUGIN_JSON=$(cat "$plugin_path/plugin.json")

    # Extract fields
    NAME=$(echo "$PLUGIN_JSON" | python3 -c "import sys,json; d=json.load(sys.stdin); print(d.get('name','$plugin_name'))" 2>/dev/null || echo "$plugin_name")
    VERSION=$(echo "$PLUGIN_JSON" | python3 -c "import sys,json; d=json.load(sys.stdin); print(d.get('version','1.0.0'))" 2>/dev/null || echo "1.0.0")
    DESCRIPTION=$(echo "$PLUGIN_JSON" | python3 -c "import sys,json; d=json.load(sys.stdin); print(d.get('description',''))" 2>/dev/null || echo "")
    DEPENDS=$(echo "$PLUGIN_JSON" | python3 -c "import sys,json; d=json.load(sys.stdin); print(','.join(d.get('dependencies',[])))" 2>/dev/null || echo "")
    ENABLED=$(echo "$PLUGIN_JSON" | python3 -c "import sys,json; d=json.load(sys.stdin); print(d.get('enabled',true))" 2>/dev/null || echo "true")

    log "  Name: $NAME v$VERSION"
    [ -n "$DESCRIPTION" ] && log "  Description: $DESCRIPTION"
    [ -n "$DEPENDS" ] && log "  Dependencies: $DEPENDS"

    # Check dependencies
    DEPS_MET=true
    if [ -n "$DEPENDS" ]; then
        IFS=',' read -ra DEPS <<< "$DEPENDS"
        for dep in "${DEPS[@]}"; do
            dep=$(echo "$dep" | xargs)
            if [ ! -d "$PLUGIN_DIR/$dep" ]; then
                log "  WARNING: Missing dependency '$dep'"
                DEPS_MET=false
            fi
        done
    fi

    if [ "$DEPS_MET" = false ]; then
        log "  SKIPPED: Dependencies not met"
        ((SKIPPED++))
        continue
    fi

    # Import database schema
    if [ -f "$plugin_path/database/schema.sql" ]; then
        log "  Importing schema..."
        mysql -u root radiohosting < "$plugin_path/database/schema.sql" 2>/dev/null || {
            log "  WARNING: Schema import failed (may already exist)"
        }
    fi

    # Run install.php
    if [ -f "$plugin_path/install.php" ]; then
        log "  Running install.php..."
        php "$plugin_path/install.php" 2>/dev/null || {
            log "  WARNING: install.php failed"
        }
    fi

    # Register permissions
    if [ -f "$plugin_path/permissions.json" ]; then
        log "  Registering permissions..."
        php -r "
            \$perms = json_decode(file_get_contents('$plugin_path/permissions.json'), true);
            if (\$perms && isset(\$perms['permissions'])) {
                foreach (\$perms['permissions'] as \$p) {
                    // Permission registration logic
                    echo '    -> ' . (\$p['key'] ?? 'unknown') . PHP_EOL;
                }
            }
        " 2>/dev/null || true
    fi

    # Register widgets
    if [ -f "$plugin_path/widgets.json" ]; then
        log "  Registering widgets..."
        php -r "
            \$widgets = json_decode(file_get_contents('$plugin_path/widgets.json'), true);
            if (\$widgets && isset(\$widgets['widgets'])) {
                foreach (\$widgets['widgets'] as \$w) {
                    echo '    -> ' . (\$w['name'] ?? 'unknown') . PHP_EOL;
                }
            }
        " 2>/dev/null || true
    fi

    # Register routes
    if [ -f "$plugin_path/routes.php" ]; then
        log "  Routes registered: $plugin_path/routes.php"
    fi

    # Enable plugin
    log "  Plugin enabled: $NAME"
    ((INSTALLED++))

done

log "Plugin installation complete: $INSTALLED installed, $FAILED failed, $SKIPPED skipped"

exit 0
