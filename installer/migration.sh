#!/bin/bash
# =========================================================
# Planet Hosts Master Panel - Migration Tool
# =========================================================
# Handles database and configuration migrations between
# panel versions. Used by update.sh.

set -eo pipefail

PANEL_DIR="/var/www/radiohosting"
LOG_DIR="/var/log/planethosts"
MIGRATIONS_DIR="$PANEL_DIR/database/migrations"

log() {
    local msg="$1"
    local ts=$(date '+%Y-%m-%d %H:%M:%S')
    mkdir -p "$LOG_DIR"
    echo "$ts | MIGRATION | $msg" >> "$LOG_DIR/migration.log"
    echo "[MIGRATION] $msg"
}

log "Starting migration check..."

if [ ! -d "$MIGRATIONS_DIR" ]; then
    log "No migrations directory found. Skipping."
    exit 0
fi

MIGRATED=0
for migration in "$MIGRATIONS_DIR"/*.sql; do
    if [ ! -f "$migration" ]; then
        continue
    fi

    migration_name=$(basename "$migration" .sql)

    # Check if already applied
    ALREADY_APPLIED=$(mysql -u root radiohosting -e \
        "SELECT COUNT(*) FROM migrations WHERE name='$migration_name'" 2>/dev/null | tail -1 || echo "0")

    if [ "$ALREADY_APPLIED" = "0" ]; then
        log "Applying migration: $migration_name"
        mysql -u root radiohosting < "$migration" 2>/dev/null || {
            log "  FAILED: $migration_name"
            continue
        }
        mysql -u root radiohosting -e \
            "INSERT INTO migrations (name, applied_at) VALUES ('$migration_name', NOW())" 2>/dev/null || true
        log "  Applied: $migration_name"
        ((MIGRATED++))
    fi
done

log "Migration complete: $MIGRATED migrations applied"

exit 0
