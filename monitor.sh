#!/bin/bash
# Planet Hosts — Monitoring Daemon
# Runs via systemd timer every 5 minutes
# Checks all active accounts and generates alerts
set -eo pipefail

LOGDIR="/var/log/radiohosting/monitor"
ALERT_FILE="${LOGDIR}/alerts.json"
MYSQL="mysql -u root -pSkylinehosting171"
NOW=$(date '+%Y-%m-%d %H:%M:%S')
TIMESTAMP=$(date +%s)

mkdir -p "$LOGDIR"

# Initialize alerts file
if [ ! -f "$ALERT_FILE" ]; then
    echo '{"alerts":[]}' > "$ALERT_FILE"
fi

send_alert() {
    local account_id="$1"
    local severity="$2"
    local message="$3"
    local alert=$(cat "$ALERT_FILE")
    alert=$(echo "$alert" | python3 -c "
import sys, json
data = json.load(sys.stdin)
data['alerts'].append({
    'account_id': $account_id,
    'severity': '$severity',
    'message': '$message',
    'timestamp': '$NOW',
    'acknowledged': false
})
# Keep last 100 alerts
data['alerts'] = data['alerts'][-100:]
print(json.dumps(data))
" 2>/dev/null || echo "$alert")
    echo "$alert" > "$ALERT_FILE"
    logger -t "planet-monitor" "[$severity] Account $account_id: $message"
}

# Get all active/completed accounts
$MYSQL -N -e "SELECT id, username, domain, disk_used, bandwidth_used, status, database_name, database_user, database_password FROM radiohosting.hosting_users WHERE status IN ('completed','active');" 2>/dev/null | while IFS=$'\t' read -r id username domain disk_used bw_used status db_name db_user db_pass; do
    [ -z "$id" ] && continue
    HOMEDIR="/home/${username}"

    # === Disk usage check ===
    if [ -d "$HOMEDIR" ]; then
        ACTUAL_DISK=$(du -sk "$HOMEDIR" 2>/dev/null | awk '{print $1}' || echo 0)
        # Get package disk limit
        PKG_DISK=$($MYSQL -N -e "SELECT p.disk_space FROM radiohosting.hosting_users u JOIN radiohosting.hosting_packages p ON u.package_id=p.id WHERE u.id=${id};" 2>/dev/null || echo 0)
        if [ "$PKG_DISK" -gt 0 ] && [ "$ACTUAL_DISK" -gt "$PKG_DISK" ]; then
            send_alert "$id" "critical" "Disk quota exceeded: ${ACTUAL_DISK}KB / ${PKG_DISK}KB for ${username}"
        fi
        # Update disk_used in DB
        $MYSQL -e "UPDATE radiohosting.hosting_users SET disk_used=${ACTUAL_DISK} WHERE id=${id};" 2>/dev/null || true
    fi

    # === SSL certificate expiry check ===
    if [ -d "/etc/letsencrypt/live/${domain}" ]; then
        EXPIRY=$(openssl x509 -enddate -noout -in "/etc/letsencrypt/live/${domain}/fullchain.pem" 2>/dev/null | cut -d= -f2 || echo "")
        if [ -n "$EXPIRY" ]; then
            EXPIRY_EPOCH=$(date -d "$EXPIRY" +%s 2>/dev/null || echo 0)
            DAYS_LEFT=$(( (EXPIRY_EPOCH - TIMESTAMP) / 86400 ))
            if [ "$DAYS_LEFT" -lt 10 ] && [ "$DAYS_LEFT" -ge 0 ]; then
                send_alert "$id" "warning" "SSL expires in ${DAYS_LEFT} days for ${domain}"
            elif [ "$DAYS_LEFT" -lt 0 ]; then
                send_alert "$id" "critical" "SSL certificate EXPIRED for ${domain}"
            fi
        fi
    fi

    # === Apache check ===
    if [ -f "/etc/apache2/sites-available/${username}.conf" ]; then
        if ! apache2ctl configtest 2>/dev/null >/dev/null; then
            send_alert "$id" "critical" "Apache configtest failed for ${username}"
        fi
        # Check if vhost responds
        if command -v curl &>/dev/null; then
            HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" --connect-timeout 5 "http://${domain}/" 2>/dev/null || echo "000")
            if [ "$HTTP_CODE" = "000" ]; then
                send_alert "$id" "warning" "Apache not responding for ${domain} (HTTP ${HTTP_CODE})"
            fi
        fi
    fi

    # === DNS check ===
    if [ -f "/etc/bind/zones/db.${domain}" ]; then
        if ! named-checkzone "${domain}" "/etc/bind/zones/db.${domain}" 2>/dev/null >/dev/null; then
            send_alert "$id" "critical" "DNS zone validation failed for ${domain}"
        fi
    fi

    # === Database connectivity check ===
    if [ -n "$db_name" ] && [ -n "$db_user" ] && [ -n "$db_pass" ]; then
        if ! mysql -u "${db_user}" -p"${db_pass}" -e "SELECT 1;" "${db_name}" 2>/dev/null >/dev/null; then
            send_alert "$id" "critical" "Database connection failed for ${username} (${db_name})"
        fi
    fi

    # === Mail check ===
    if [ -d "${HOMEDIR}/mail/${domain}" ]; then
        if ! ls "${HOMEDIR}/mail/${domain}/" &>/dev/null; then
            send_alert "$id" "warning" "Mail directory inaccessible for ${domain}"
        fi
    fi

    # === PHP-FPM check ===
    PHP_SOCK="/var/run/php/php*-fpm-${username}.sock"
    if ls $PHP_SOCK 2>/dev/null >/dev/null; then
        SOCK=$(ls $PHP_SOCK 2>/dev/null | head -1)
        if [ -n "$SOCK" ] && [ ! -S "$SOCK" ]; then
            send_alert "$id" "warning" "PHP-FPM socket missing for ${username}"
        fi
    fi
done

# === Bandwidth usage (from Apache logs) ===
$MYSQL -N -e "SELECT id, username, domain FROM radiohosting.hosting_users WHERE status IN ('completed','active');" 2>/dev/null | while IFS=$'\t' read -r id username domain; do
    [ -z "$id" ] && continue
    LOG_FILE="/home/${username}/logs/access.log"
    if [ -f "$LOG_FILE" ]; then
        # Approximate bandwidth from log (sum of bytes_sent)
        BW_USED=$(awk '{sum += $10} END {print sum}' "$LOG_FILE" 2>/dev/null || echo 0)
        if [ "$BW_USED" -gt 0 ]; then
            $MYSQL -e "UPDATE radiohosting.hosting_users SET bandwidth_used=${BW_USED} WHERE id=${id};" 2>/dev/null || true
            PKG_BW=$($MYSQL -N -e "SELECT p.bandwidth FROM radiohosting.hosting_users u JOIN radiohosting.hosting_packages p ON u.package_id=p.id WHERE u.id=${id};" 2>/dev/null || echo 0)
            if [ "$PKG_BW" -gt 0 ] && [ "$BW_USED" -gt "$PKG_BW" ]; then
                send_alert "$id" "warning" "Bandwidth limit exceeded: ${BW_USED} bytes / ${PKG_BW} bytes for ${username}"
            fi
        fi
    fi
done

# Log health summary
TOTAL_ACCOUNTS=$($MYSQL -N -e "SELECT COUNT(*) FROM radiohosting.hosting_users;" 2>/dev/null || echo 0)
HEALTHY=$($MYSQL -N -e "SELECT COUNT(*) FROM radiohosting.hosting_users WHERE status IN ('completed','active');" 2>/dev/null || echo 0)
echo "[${NOW}] Monitor run complete: ${HEALTHY}/${TOTAL_ACCOUNTS} healthy" >> "${LOGDIR}/monitor.log"

# Clean up old logs (keep 30 days)
find "$LOGDIR" -name "*.log" -mtime +30 -delete 2>/dev/null || true

exit 0
