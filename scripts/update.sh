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
        echo "{\"behind\":$BEHIND,\"checked_at\":\"$(date -c)\"}" > "$BASE_PATH/storage/update_available.json"
    else
        log "No update available."
        echo "{\"behind\":0,\"checked_at\":\"$(date -c)\"}" > "$BASE_PATH/storage/update_available.json"
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
if curl -sk http://localhost:2087/admin/login 2>&1 | grep -q "Admin Login"; then
    log "Health check passed."
else
    log "Health check failed, rolling back."
    rm -f "$LOCK_FILE"
    bash "$0" --rollback
    exit 1
fi

rm -f "$LOCK_FILE"
log "Update complete. Current: $(cd "$BASE_PATH" && git rev-parse --short HEAD 2>/dev/null)"
# Update Masterinstall
if [ -d "/mnt/k_site_del_Masterinstall" ]; then
    log "Updating Masterinstall..."
    cd "/mnt/k_site_del_Masterinstall" && git pull origin master 2>&1 | tee -a "$LOG_FILE" || true
fi
exit 0
