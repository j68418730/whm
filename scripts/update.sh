#!/bin/bash
# Planet Hosts - Release-based self updater (managed by the WHM Update page).
# Usage: sudo bash scripts/update.sh [--check]
# Download the release package from the update source, verify the SHA-256,
# stage, back up, apply, migrate, reload services and health check.
# No git is required on installed servers. Runtime/config files (untracked)
# are preserved: .env, config/install.lock, storage/, public/uploads/.

set -e
BASE_PATH="$(cd "$(dirname "$0")/.." && pwd)"
LOG_FILE="$BASE_PATH/storage/update.log"
BACKUP_TAR="$BASE_PATH/storage/update_backup.tar.gz"
BACKUP_SQL="$BASE_PATH/storage/update_backup.sql"
LOCK_FILE="$BASE_PATH/storage/update.lock"
UPDATE_DIR="$BASE_PATH/storage/update"
REMOTE_JSON="$UPDATE_DIR/remote.json"
PACKAGE="$UPDATE_DIR/package.tar.gz"
STAGE="$UPDATE_DIR/stage"
STATE_FILE="$BASE_PATH/storage/current_release.json"
STATE_BEFORE="$UPDATE_DIR/release_before.json"
ALERT_FILE="$BASE_PATH/storage/update_available.json"

log() { echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"; }

# ---------------------------------------------------------------------------
# Config: update source + channel come from .env / AWS-less env (see Core\Updates)
# ---------------------------------------------------------------------------
SRC=$(grep -m1 '^UPDATE_SOURCE=' "$BASE_PATH/.env" 2>/dev/null | cut -d= -f2 | tr -d '\r' | tr -d '"' | tr -d "'" | sed 's/^[[:space:]]*//;s/[[:space:]]*$//')
[ -n "$SRC" ] || SRC='https://raw.githubusercontent.com/j68418730/whm/master/VERSION.json'
CHANNEL=$(grep -m1 '^UPDATE_CHANNEL=' "$BASE_PATH/.env" 2>/dev/null | cut -d= -f2 | tr -d '\r' | tr -d '"' | tr -d "'" | sed 's/^[[:space:]]*//;s/[[:space:]]*$//')
[ -n "$CHANNEL" ] || CHANNEL='stable'

JSON_GET() { # $1=file $2=key
    /usr/bin/php -r '$d=json_decode(file_get_contents($argv[1]),true); echo isset($d[$argv[2]])?(is_array($d[$argv[2]])?json_encode($d[$argv[2]]):$d[$argv[2]]):"";' "$1" "$2"
}

# System-level actions (sudoers/cron/services) only run for a real install path.
SYSTEM_PATH="${PH_SYSTEM_PATH:-/var/www/radiohosting}"

# Ensure the web user can trigger self-updates from the WHM UI (idempotent).
if [ "$(id -u)" = "0" ] && [ "$BASE_PATH" = "$SYSTEM_PATH" ]; then
    SUDOERS_FILE=/etc/sudoers.d/radiohosting-update
    mkdir -p "$BASE_PATH/storage" "$UPDATE_DIR"
    {
        echo "# Allow WHM UI triggered self-update/rollback"
        echo "# Panel invokes: sudo /bin/bash $BASE_PATH/scripts/update.sh [--rollback]"
        echo "www-data ALL=(root) NOPASSWD: /bin/bash $BASE_PATH/scripts/update.sh"
        echo "www-data ALL=(root) NOPASSWD: /bin/bash $BASE_PATH/scripts/update.sh *"
    } > "$SUDOERS_FILE"
    chmod 440 "$SUDOERS_FILE"
    if ! visudo -c >/dev/null 2>&1; then
        log "sudoers validation failed; removing $SUDOERS_FILE"
        rm -f "$SUDOERS_FILE"
        visudo -c >/dev/null 2>&1 || true
    fi
fi

db_creds() {
    DB_USER=$(grep -m1 '^DB_USERNAME=' "$BASE_PATH/.env" 2>/dev/null | cut -d= -f2 | tr -d '\r' | tr -d '"' | tr -d "'")
    DB_PASS=$(grep -m1 '^DB_PASSWORD=' "$BASE_PATH/.env" 2>/dev/null | cut -d= -f2 | tr -d '\r' | tr -d '"' | tr -d "'")
    DB_NAME=$(grep -m1 '^DB_DATABASE=' "$BASE_PATH/.env" 2>/dev/null | cut -d= -f2 | tr -d '\r' | tr -d '"' | tr -d "'")
}

# ---------------------------------------------------------------------------
# --check : compare installed vs released version, update the alert state
# ---------------------------------------------------------------------------
if [ "$1" = "--check" ]; then
    /usr/bin/php "$BASE_PATH/scripts/updates_check.php" 10 2>&1 | tail -1
    chown www-data:www-data "$ALERT_FILE" 2>/dev/null || true
    chmod 644 "$ALERT_FILE" 2>/dev/null || true
    exit 0
fi

# ---------------------------------------------------------------------------
# --rollback
# ---------------------------------------------------------------------------
if [ "$1" = "--rollback" ]; then
    log "Rollback started..."
    touch "$LOCK_FILE"
    if [ -f "$BACKUP_TAR" ]; then
        log "Restoring files from $BACKUP_TAR"
        tar xzf "$BACKUP_TAR" -C "$BASE_PATH" 2>&1 | tee -a "$LOG_FILE" || log "Restore tar failed"
    fi
    if [ -f "$STATE_BEFORE" ]; then
        if /usr/bin/php -r '$d=json_decode(file_get_contents($argv[1]),true); echo is_array($d)?"ok":"";' "$STATE_BEFORE" | grep -q ok; then
            cp "$STATE_BEFORE" "$STATE_FILE"
            log "Restored release state."
        fi
    fi
    if [ -f "$BACKUP_SQL" ]; then
        log "Restoring database from $BACKUP_SQL"
        db_creds
        mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$BACKUP_SQL" 2>&1 | tee -a "$LOG_FILE" || log "DB restore failed"
    fi
    chown -R www-data:www-data "$BASE_PATH" 2>&1 | tee -a "$LOG_FILE" || true
    systemctl reload apache2 2>&1 | tee -a "$LOG_FILE" || log "Apache reload failed"
    rm -f "$LOCK_FILE"
    /usr/bin/php "$BASE_PATH/scripts/updates_check.php" 10 > /dev/null 2>&1 || true
    log "Rollback complete."
    exit 0
fi

# ---------------------------------------------------------------------------
# Normal update
# ---------------------------------------------------------------------------
log "Update started..."

if [ -f "$LOCK_FILE" ]; then
    log "Update already in progress (lock file exists). Aborting."
    exit 1
fi
touch "$LOCK_FILE"
trap 'rm -f "$LOCK_FILE"' EXIT

mkdir -p "$UPDATE_DIR"

# Installed release (state file if present, else VERSION.json in tree)
if [ -f "$STATE_FILE" ]; then IJSON="$STATE_FILE"; else IJSON="$BASE_PATH/VERSION.json"; fi
ICODE=$( [ -f "$IJSON" ] && JSON_GET "$IJSON" version_code || echo 0 )

# Fetch the release manifest
log "Fetching update manifest from $SRC"
if ! curl -sfL -m 30 -o "$REMOTE_JSON" "$SRC"; then
    log "Failed to reach update source."
    rm -f "$LOCK_FILE"
    exit 1
fi
RCODE=$(JSON_GET "$REMOTE_JSON" version_code)
RVER=$(JSON_GET "$REMOTE_JSON" version)
RCHAN=$(JSON_GET "$REMOTE_JSON" channel)
RREQ=$(JSON_GET "$REMOTE_JSON" require_version_code)
RDL=$(JSON_GET "$REMOTE_JSON" download)
RSUM=$(JSON_GET "$REMOTE_JSON" checksum)
RSHA=$(JSON_GET "$REMOTE_JSON" target_sha)
RPHP=$(JSON_GET "$REMOTE_JSON" php_required)
RBOOT=$(JSON_GET "$REMOTE_JSON" requires_reboot)
SERVICES=$(JSON_GET "$REMOTE_JSON" requires_services_restart)

[ -n "$RVER" ] && log "Remote release: $RVER (code $RCODE, channel ${RCHAN:-?})"

# Version source sanity
if [ -z "$RCODE" ] || [ "$RCODE" = "0" ]; then
    log "Release manifest invalid (no version_code). Aborting."
    rm -f "$LOCK_FILE"
    exit 1
fi

# Up to date?
if [ "$RCODE" -le "$ICODE" ]; then
    log "Already up to date (installed $ICODE, latest $RCODE)."
    /usr/bin/php "$BASE_PATH/scripts/updates_check.php" 10 > /dev/null 2>&1 || true
    exit 0
fi

# Channel check
if [ -n "$RCHAN" ] && [ "$RCHAN" != "$CHANNEL" ]; then
    log "Release channel mismatch: remote=$RCHAN, configured=$CHANNEL. Aborting."
    rm -f "$LOCK_FILE"
    exit 1
fi

# Compatibility (minimum installed version)
if [ -n "$RREQ" ] && [ "$RREQ" != "0" ] && [ "$ICODE" -lt "$RREQ" ]; then
    log "Incompatible: this release requires version_code >= $RREQ, installed is $ICODE. Aborting."
    rm -f "$LOCK_FILE"
    exit 1
fi

[ -n "$RPHP" ] && log "Requires PHP: $RPHP (installed: $(php -r 'echo PHP_VERSION;'))"

# ---------------------------------------------------------------------------
# Download + verify
# ---------------------------------------------------------------------------
log "Downloading update package..."
[ -n "$RDL" ] || RDL="https://codeload.github.com/j68418730/whm/tar.gz/${RSHA:-master}"
if ! curl -sfL -m 300 -o "$PACKAGE" "$RDL"; then
    log "Package download failed."
    rm -f "$LOCK_FILE"
    exit 1
fi
SZ=$(stat -c%s "$PACKAGE" 2>/dev/null || echo '?')
log "Downloaded package ($SZ bytes)."

if [ -n "$RSUM" ]; then
    ACTUAL=$(sha256sum "$PACKAGE" | awk '{print $1}')
    if [ "$ACTUAL" != "$RSUM" ]; then
        log "CHECKSUM MISMATCH: expected $RSUM, got $ACTUAL. Aborting (package not trusted)."
        rm -f "$LOCK_FILE"
        exit 1
    fi
    log "Checksum verified: $ACTUAL"
else
    log "No checksum in manifest; package not verified. Aborting."
    rm -f "$LOCK_FILE"
    exit 1
fi

# Structural verification
if ! tar -tzf "$PACKAGE" 2>/dev/null | grep -q 'public/index.php'; then
    log "Package missing public/index.php - not a Planet Hosts release. Aborting."
    rm -f "$LOCK_FILE"
    exit 1
fi

# ---------------------------------------------------------------------------
# Backup
# ---------------------------------------------------------------------------
log "Creating backup..."
rm -f "$BACKUP_TAR" "$BACKUP_SQL"
tar czf "$BACKUP_TAR" --exclude='.git' --exclude="$BACKUP_TAR" --exclude="$BACKUP_SQL" --exclude="$UPDATE_DIR" --exclude='storage/logs' -C "$BASE_PATH" . 2>&1 | tee -a "$LOG_FILE" || log "Backup tar failed"
db_creds
if [ -n "$DB_USER" ] && [ -n "$DB_PASS" ] && [ -n "$DB_NAME" ]; then
    mysqldump -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$BACKUP_SQL" 2>&1 | tee -a "$LOG_FILE" || log "DB dump failed"
fi
log "Backup created."

# Record the pre-update release state (for rollback)
if [ -f "$STATE_FILE" ]; then cp "$STATE_FILE" "$STATE_BEFORE"; fi

# ---------------------------------------------------------------------------
# Stage + apply
# ---------------------------------------------------------------------------
log "Staging update..."
rm -rf "$STAGE"
mkdir -p "$STAGE"
tar xzf "$PACKAGE" -C "$STAGE" --strip-components=1 2>&1 | tee -a "$LOG_FILE"

log "Applying files..."
# Archive contains only tracked files; .env, config/install.lock, storage/,
# public/uploads/ are never in the package, so runtime data is preserved.
if command -v rsync >/dev/null 2>&1; then
    rsync -a --quiet "$STAGE/" "$BASE_PATH/" \
        --exclude='.git/' --exclude='storage/' --exclude='public/uploads/' --exclude='.env'
else
    cp -a "$STAGE/." "$BASE_PATH/"
fi

# ---------------------------------------------------------------------------
# Migrations
# ---------------------------------------------------------------------------
log "Running migrations..."
db_creds
run_one_migration() {
    local f="$1"
    case "$f" in
        *.sql) mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$f" 2>&1 | tee -a "$LOG_FILE" || log "Migration $f failed (may already be applied)";;
        *.php) php "$BASE_PATH/scripts/migrate.php" "$f" 2>&1 | tee -a "$LOG_FILE" || log "Migration $f failed (see log above)";;
    esac
}
MIGS=$(JSON_GET "$REMOTE_JSON" migrations)
if [ -n "$MIGS" ] && [ "$MIGS" != "[]" ] && [ "$MIGS" != "null" ]; then
    echo "$MIGS" | /usr/bin/php -r '$d=json_decode(stream_get_contents(STDIN),true); foreach($d as $m) echo $m, "\n";' | while IFS= read -r rel; do
        [ -n "$rel" ] || continue
        log "Running $rel"
        run_one_migration "$BASE_PATH/database/migrations/$rel"
    done
else
    for m in "$BASE_PATH/database/migrations/"*.sql "$BASE_PATH/database/migrations/"*.php; do
        [ -f "$m" ] || continue
        log "Running $(basename "$m")"
        run_one_migration "$m"
    done
fi

# ---------------------------------------------------------------------------
# Cron (backup runner + update check) - idempotent (system installs only)
# ---------------------------------------------------------------------------
if [ "$BASE_PATH" = "$SYSTEM_PATH" ]; then
log "Installing crons..."
CRON_FILE=/etc/cron.d/planet-hosts-backup
if [ ! -f "$CRON_FILE" ]; then
    echo "* * * * * root /usr/bin/php $BASE_PATH/scripts/backup_cron.php > /dev/null 2>&1" > "$CRON_FILE"
    chmod 644 "$CRON_FILE"
fi
UPD_CRON_FILE=/etc/cron.d/planet-hosts-updates
if [ ! -f "$UPD_CRON_FILE" ]; then
    echo "*/5 * * * * root /bin/bash $BASE_PATH/scripts/check_update.sh >/dev/null 2>&1" > "$UPD_CRON_FILE"
    chmod 644 "$UPD_CRON_FILE"
fi
fi

# ---------------------------------------------------------------------------
# Finalize
# ---------------------------------------------------------------------------
log "Linting PHP..."
php -l "$BASE_PATH/public/index.php" 2>&1 | tee -a "$LOG_FILE"

chown -R www-data:www-data "$BASE_PATH" 2>&1 | tee -a "$LOG_FILE" || true
bash "$BASE_PATH/scripts/setup_storage.sh" 2>&1 | tee -a "$LOG_FILE" || true

log "Reloading services..."
if [ "$BASE_PATH" = "$SYSTEM_PATH" ] && echo "$SERVICES" | grep -q 'apache2'; then
    systemctl reload apache2 2>&1 | tee -a "$LOG_FILE" || log "Apache reload failed"
fi
[ "$RBOOT" = "true" ] && log "This release requires a server reboot."

# Record installed release state
cat > "$STATE_FILE" <<EOF
{"version":"$RVER","version_code":$RCODE,"channel":"$RCHAN","target_sha":"${RSHA:-unknown}","checksum":"$RSUM","php_required":"$RPHP","requires_reboot":$([ "$RBOOT" = "true" ] && echo true || echo false),"installed_at":"$(date -u '+%Y-%m-%dT%H:%M:%SZ')","from_version_code":$ICODE}
EOF
chown www-data:www-data "$STATE_FILE" 2>/dev/null || true

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

# Refresh the dashboard alert
/usr/bin/php "$BASE_PATH/scripts/updates_check.php" 10 > /dev/null 2>&1 || true
rm -f "$LOCK_FILE"

log "Update complete: Ph-Whm $RVER (code $RCODE)."
echo "UPDATE_DONE version=$RVER code=$RCODE"
exit 0