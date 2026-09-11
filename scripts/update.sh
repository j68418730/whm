#!/bin/bash
# Planet Hosts - Auto Update Script
# Usage: sudo bash scripts/update.sh [--check] [--rollback]
# Must be run as root or with sudo, from BASE_PATH

set -e
BASE_PATH="$(cd "$(dirname "$0")/.." && pwd)"
LOG_FILE="$BASE_PATH/storage/update.log"
BACKUP_TAR="$BASE_PATH/storage/update_backup.tar.gz"
BACKUP_SQL="$BASE_PATH/storage/update_backup.sql"
LOCK_FILE="$BASE_PATH/storage/update.lock"

log() { echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"; }

# Ensure the web user can trigger self-updates from the WHM UI (idempotent).
if [ "$(id -u)" = "0" ]; then
    SUDOERS_FILE=/etc/sudoers.d/radiohosting-update
    mkdir -p "$BASE_PATH/storage"
    {
        echo "# Allow WHM UI triggered self-update/rollback"
        echo "www-data ALL=(root) NOPASSWD: /bin/bash $BASE_PATH/scripts/update.sh"
    } > "$SUDOERS_FILE"
    chmod 440 "$SUDOERS_FILE"
    if ! visudo -c >/dev/null 2>&1; then
        log "sudoers validation failed; removing $SUDOERS_FILE"
        rm -f "$SUDOERS_FILE"
        visudo -c >/dev/null 2>&1 || true
    fi
fi

if [ -f "$LOCK_FILE" ]; then
    log "Update already in progress (lock file exists). Aborting."
    exit 1
fi

if [ "$1" = "--check" ]; then
    cd "$BASE_PATH" && git fetch origin 2>&1 | tee -a "$LOG_FILE"
    BEHIND=$(git rev-list HEAD..origin/master --count 2>/dev/null || echo 0)
    if [ "$BEHIND" -gt 0 ]; then
        log "Update available: $BEHIND commits behind origin/master"
        git log HEAD..origin/master --oneline -5 2>&1 | tee -a "$LOG_FILE"
        echo "{\"behind\":$BEHIND,\"checked_at\":\"$(date '+%Y-%m-%d %H:%M:%S')\"}" > "$BASE_PATH/storage/update_available.json"
    else
        log "No update available."
        echo "{\"behind\":0,\"checked_at\":\"$(date '+%Y-%m-%d %H:%M:%S')\"}" > "$BASE_PATH/storage/update_available.json"
    fi
    exit 0
fi

if [ "$1" = "--rollback" ]; then
    log "Rollback started..."
    touch "$LOCK_FILE"
    if [ -f "$BACKUP_TAR" ]; then
        log "Restoring files from $BACKUP_TAR"
        tar xzf "$BACKUP_TAR" -C "$BASE_PATH" 2>&1 | tee -a "$LOG_FILE" || log "Restore tar failed"
    fi
    if [ -f "$BACKUP_SQL" ]; then
        log "Restoring database from $BACKUP_SQL"
        # Get DB creds from .env
        DB_USER=$(grep DB_USERNAME "$BASE_PATH/.env" | cut -d= -f2 | tr -d '[:space:]' | tr -d "'\"")
        DB_PASS=$(grep DB_PASSWORD "$BASE_PATH/.env" | cut -d= -f2 | tr -d '[:space:]' | tr -d "'\"")
        DB_NAME=$(grep DB_DATABASE "$BASE_PATH/.env" | cut -d= -f2 | tr -d '[:space:]' | tr -d "'\"")
        mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$BACKUP_SQL" 2>&1 | tee -a "$LOG_FILE" || log "DB restore failed"
    fi
    # Reset git to previous HEAD
    cd "$BASE_PATH" && git reset --hard HEAD@{1} 2>&1 | tee -a "$LOG_FILE" || log "Git reset failed"
    chown -R www-data:www-data "$BASE_PATH" 2>&1 | tee -a "$LOG_FILE" || true
    systemctl reload apache2 2>&1 | tee -a "$LOG_FILE" || true
    systemctl reload php8.2-fpm 2>&1 | tee -a "$LOG_FILE" || systemctl reload php-fpm 2>&1 | tee -a "$LOG_FILE" || true
    rm -f "$LOCK_FILE"
    log "Rollback complete."
    exit 0
fi

# Normal update
log "Update started..."
touch "$LOCK_FILE"

# Backup
log "Creating backup..."
tar czf "$BACKUP_TAR" --exclude='.git' --exclude='storage/update_backup.tar.gz' --exclude='storage/update_backup.sql' --exclude='storage/logs' -C "$BASE_PATH" . 2>&1 | tee -a "$LOG_FILE" || log "Backup tar failed"
DB_USER=$(grep DB_USERNAME "$BASE_PATH/.env" | cut -d= -f2 | tr -d '[:space:]' | tr -d "'\"")
DB_PASS=$(grep DB_PASSWORD "$BASE_PATH/.env" | cut -d= -f2 | tr -d '[:space:]' | tr -d "'\"")
DB_NAME=$(grep DB_DATABASE "$BASE_PATH/.env" | cut -d= -f2 | tr -d '[:space:]' | tr -d "'\"")
if [ -n "$DB_USER" ] && [ -n "$DB_PASS" ] && [ -n "$DB_NAME" ]; then
    mysqldump -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$BACKUP_SQL" 2>&1 | tee -a "$LOG_FILE" || log "DB dump failed"
fi
log "Backup created."

# Pull
log "Pulling from origin/master..."
cd "$BASE_PATH"
if ! git pull origin master 2>&1 | tee -a "$LOG_FILE"; then
    log "Git pull failed, rolling back."
    rm -f "$LOCK_FILE"
    bash "$0" --rollback
    exit 1
fi

# Migrations
log "Running migrations..."
for m in "$BASE_PATH/database/migrations/"*.sql; do
    [ -f "$m" ] || continue
    log "Running $m"
    mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$m" 2>&1 | tee -a "$LOG_FILE" || log "Migration $m failed (may already be applied)"
done
for m in "$BASE_PATH/database/migrations/"*.php; do
    [ -f "$m" ] || continue
    log "Running $m"
    php "$m" 2>&1 | tee -a "$LOG_FILE" || log "Migration $m failed"
done

# Scheduled backup runner (idempotent)
log "Installing backup cron..."
CRON_FILE=/etc/cron.d/planet-hosts-backup
if [ ! -f "$CRON_FILE" ]; then
    echo "* * * * * root /usr/bin/php $BASE_PATH/scripts/backup_cron.php > /dev/null 2>&1" > "$CRON_FILE"
    chmod 644 "$CRON_FILE"
    log "Backup cron installed at $CRON_FILE"
else
    log "Backup cron already present."
fi

# Update check runner (idempotent) - refreshes storage/update_available.json so
# the dashboard alert appears without a manual "Check for Updates" click.
log "Installing update-check cron..."
UPD_CRON_FILE=/etc/cron.d/planet-hosts-updates
if [ ! -f "$UPD_CRON_FILE" ]; then
    echo "*/5 * * * * root /bin/bash $BASE_PATH/scripts/check_update.sh >/dev/null 2>&1" > "$UPD_CRON_FILE"
    chmod 644 "$UPD_CRON_FILE"
    log "Update-check cron installed at $UPD_CRON_FILE"
else
    log "Update-check cron already present."
fi

# Lint
log "Linting PHP files..."
if ! php -l "$BASE_PATH/public/index.php" 2>&1 | tee -a "$LOG_FILE"; then
    log "Lint failed, rolling back."
    rm -f "$LOCK_FILE"
    bash "$0" --rollback
    exit 1
fi

# Permissions
chown -R www-data:www-data "$BASE_PATH" 2>&1 | tee -a "$LOG_FILE" || true
bash "$BASE_PATH/scripts/setup_storage.sh" 2>&1 | tee -a "$LOG_FILE" || true

# Reload services
log "Reloading services..."
systemctl reload apache2 2>&1 | tee -a "$LOG_FILE" || log "Apache reload failed"
systemctl reload php8.2-fpm 2>&1 | tee -a "$LOG_FILE" || systemctl reload php-fpm 2>&1 | tee -a "$LOG_FILE" || log "PHP-FPM reload failed"
systemctl reload nginx 2>&1 | tee -a "$LOG_FILE" || true

# Health check
log "Health check..."
sleep 2
HEALTH_OK=0
for scheme in https http; do
    if curl -sk -m 10 "${scheme}://localhost:2087/admin/login" 2>/dev/null | grep -q "Admin Login"; then
        HEALTH_OK=1
        break
    fi
done
if [ "$HEALTH_OK" = "1" ]; then
    log "Health check passed."
else
    log "Health check failed, rolling back."
    rm -f "$LOCK_FILE"
    bash "$0" --rollback
    exit 1
fi

rm -f "$LOCK_FILE"
log "Update complete. Current: $(cd "$BASE_PATH" && git rev-parse --short HEAD 2>/dev/null)"

# Refresh the dashboard alert so a stale "update available" banner never lingers.
CURRENT_HASH=$(cd "$BASE_PATH" && git rev-parse --short HEAD 2>/dev/null || echo unknown)
echo "{\"current\":\"$CURRENT_HASH\",\"upstream\":\"$CURRENT_HASH\",\"behind\":0,\"update_available\":false,\"checked_at\":\"$(date '+%Y-%m-%d %H:%M:%S')\"}" > "$BASE_PATH/storage/update_available.json"
chown www-data:www-data "$BASE_PATH/storage/update_available.json" 2>/dev/null
chmod 644 "$BASE_PATH/storage/update_available.json" 2>/dev/null

exit 0
