#!/bin/bash
# Planet Hosts — Backup System
# Usage: backup.sh <action> <username> [domain]
# Actions: run, restore, list
set -eo pipefail

ACTION="${1:-run}"
USERNAME="$2"
DOMAIN="$3"
MYSQL="mysql -u root -pSkylinehosting171"
BACKUP_BASE="/backups"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
LOGDIR="/var/log/radiohosting/backup"

mkdir -p "$LOGDIR"

log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $*" >> "${LOGDIR}/backup.log"
    echo "$*"
}

case "$ACTION" in
    run)
        # Run backups for all active accounts
        $MYSQL -N -e "SELECT u.id, u.username, u.domain, COALESCE(bs.daily_enabled,1), COALESCE(bs.weekly_enabled,1), COALESCE(bs.monthly_enabled,1), COALESCE(bs.retention_daily,7), COALESCE(bs.retention_weekly,4), COALESCE(bs.retention_monthly,3) FROM radiohosting.hosting_users u LEFT JOIN radiohosting.backup_settings bs ON u.id=bs.account_id WHERE u.status IN ('completed','active');" 2>/dev/null | while IFS=$'\t' read -r id username domain daily weekly monthly ret_daily ret_weekly ret_monthly; do
            [ -z "$id" ] && continue
            HOMEDIR="/home/${username}"
            [ ! -d "$HOMEDIR" ] && continue

            BACKUP_DIR="${BACKUP_BASE}/${username}"
            mkdir -p "$BACKUP_DIR"/{daily,weekly,monthly}

            DAY_OF_WEEK=$(date +%u)
            DAY_OF_MONTH=$(date +%d)

            # Determine backup type
            BACKUP_TYPE="daily"
            FREQ="daily"
            RETENTION="$ret_daily"

            if [ "$monthly" = "1" ] && [ "$DAY_OF_MONTH" = "01" ]; then
                BACKUP_TYPE="monthly"
                FREQ="monthly"
                RETENTION="$ret_monthly"
            elif [ "$weekly" = "1" ] && [ "$DAY_OF_WEEK" = "7" ]; then
                BACKUP_TYPE="weekly"
                FREQ="weekly"
                RETENTION="$ret_weekly"
            elif [ "$daily" != "1" ]; then
                log "SKIP ${username}: daily backup disabled"
                continue
            fi

            # Create backup
            BACKUP_FILE="${BACKUP_DIR}/${BACKUP_TYPE}/${username}_${TIMESTAMP}.tar.gz"
            DB_BACKUP="${BACKUP_DIR}/${BACKUP_TYPE}/${username}_db_${TIMESTAMP}.sql.gz"

            log "Starting ${FREQ} backup for ${username}"

            # Files backup
            if tar -czf "$BACKUP_FILE" -C "$HOMEDIR" --exclude="tmp/*" --exclude=".credentials.json" . 2>/dev/null; then
                log "OK ${username}: files backup (${BACKUP_TYPE})"
            else
                log "FAIL ${username}: files backup failed"
                continue
            fi

            # Database backup
            DB_NAME=$($MYSQL -N -e "SELECT database_name FROM radiohosting.hosting_users WHERE id=${id};" 2>/dev/null || true)
            DB_USER=$($MYSQL -N -e "SELECT database_user FROM radiohosting.hosting_users WHERE id=${id};" 2>/dev/null || true)
            DB_PASS=$($MYSQL -N -e "SELECT database_password FROM radiohosting.hosting_users WHERE id=${id};" 2>/dev/null || true)
            if [ -n "$DB_NAME" ] && [ -n "$DB_USER" ] && [ -n "$DB_PASS" ]; then
                if mysqldump -u "${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" 2>/dev/null | gzip > "$DB_BACKUP"; then
                    log "OK ${username}: database backup (${DB_NAME})"
                else
                    log "WARN ${username}: database backup failed"
                fi
            fi

            # File size
            SIZE=$(du -h "$BACKUP_FILE" 2>/dev/null | awk '{print $1}')
            log "OK ${username}: backup complete (${SIZE})"

            # Rotate old backups
            find "${BACKUP_DIR}/${BACKUP_TYPE}/" -name "*.tar.gz" -mtime "+${RETENTION}" -delete 2>/dev/null || true
            find "${BACKUP_DIR}/${BACKUP_TYPE}/" -name "*.sql.gz" -mtime "+${RETENTION}" -delete 2>/dev/null || true

            # Update last_backup timestamp
            $MYSQL -e "INSERT INTO radiohosting.backup_settings (account_id, last_backup, next_backup) VALUES (${id}, NOW(), DATE_ADD(NOW(), INTERVAL 1 DAY)) ON DUPLICATE KEY UPDATE last_backup=NOW(), next_backup=DATE_ADD(NOW(), INTERVAL 1 DAY);" 2>/dev/null || true
        done
        log "Backup run complete"
        ;;

    restore)
        if [ -z "$USERNAME" ]; then
            echo "Usage: $0 restore <username> [backup_file]"
            exit 1
        fi
        HOMEDIR="/home/${USERNAME}"
        [ ! -d "$HOMEDIR" ] && echo "Home dir not found: $HOMEDIR" && exit 1

        BACKUP_FILE="$3"
        if [ -z "$BACKUP_FILE" ]; then
            # Find latest backup
            BACKUP_FILE=$(ls -t "${BACKUP_BASE}/${USERNAME}"/daily/*.tar.gz "${BACKUP_BASE}/${USERNAME}"/weekly/*.tar.gz "${BACKUP_BASE}/${USERNAME}"/monthly/*.tar.gz 2>/dev/null | head -1)
        fi

        if [ -z "$BACKUP_FILE" ] || [ ! -f "$BACKUP_FILE" ]; then
            echo "No backup found for ${USERNAME}"
            exit 1
        fi

        log "Restoring ${USERNAME} from ${BACKUP_FILE}"

        # Extract backup
        if tar -xzf "$BACKUP_FILE" -C "$HOMEDIR" 2>/dev/null; then
            chown -R "${USERNAME}:${USERNAME}" "$HOMEDIR"
            log "OK ${USERNAME}: files restored from ${BACKUP_FILE}"

            # Restore database if backup exists
            DB_BACKUP="${BACKUP_FILE%.tar.gz}_db.sql.gz"
            if [ -f "$DB_BACKUP" ]; then
                DB_NAME=$($MYSQL -N -e "SELECT database_name FROM radiohosting.hosting_users WHERE username='${USERNAME}';" 2>/dev/null || true)
                if [ -n "$DB_NAME" ]; then
                    gunzip -c "$DB_BACKUP" | mysql -u root -pSkylinehosting171 "$DB_NAME" 2>/dev/null
                    log "OK ${USERNAME}: database restored"
                fi
            fi
        else
            log "FAIL ${USERNAME}: restore failed"
            exit 1
        fi

        log "Restore complete for ${USERNAME}"
        ;;

    list)
        if [ -z "$USERNAME" ]; then
            echo "Usage: $0 list <username>"
            exit 1
        fi
        echo "Backups for ${USERNAME}:"
        find "${BACKUP_BASE}/${USERNAME}" -name "*.tar.gz" -exec ls -lh {} \; 2>/dev/null || echo "No backups found"
        ;;

    *)
        echo "Usage: $0 <run|restore|list> [username] [backup_file]"
        exit 1
        ;;
esac
exit 0
