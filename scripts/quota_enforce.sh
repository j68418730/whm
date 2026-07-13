#!/bin/bash
# Planet Hosts — Quota Enforcement
# Runs via systemd timer every 15 minutes
# Auto-suspends accounts exceeding package limits
set -eo pipefail

MYSQL="mysql -u root -pSkylinehosting171"
LOGDIR="/var/log/radiohosting/quota"
mkdir -p "$LOGDIR"

log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $*" >> "${LOGDIR}/quota.log"
}

# Get all completed/active accounts with package limits
$MYSQL -N -e "
SELECT u.id, u.username, u.domain, u.disk_used, u.bandwidth_used, 
       p.disk_space, p.bandwidth, p.email_accounts, p.databases,
       p.subdomains, p.addon_domains, p.ftp_accounts
FROM radiohosting.hosting_users u 
JOIN radiohosting.hosting_packages p ON u.package_id = p.id 
WHERE u.status IN ('completed','active');
" 2>/dev/null | while IFS=$'\t' read -r id username domain disk_used bw_used max_disk max_bw max_emails max_dbs max_sub max_addon max_ftp; do
    [ -z "$id" ] && continue
    HOMEDIR="/home/${username}"
    VIOLATIONS=""
    SHOULD_SUSPEND=0

    # === Disk quota enforcement ===
    if [ "$max_disk" -gt 0 ] && [ "$disk_used" -gt "$max_disk" ]; then
        VIOLATIONS="${VIOLATIONS} disk(${disk_used}/${max_disk}KB)"
        SHOULD_SUSPEND=1
    fi

    # === Bandwidth enforcement ===
    if [ "$max_bw" -gt 0 ] && [ "$bw_used" -gt "$max_bw" ]; then
        VIOLATIONS="${VIOLATIONS} bw(${bw_used}/${max_bw}bytes)"
        SHOULD_SUSPEND=1
    fi

    # === Actual disk usage check (live) ===
    if [ -d "$HOMEDIR" ]; then
        LIVE_DISK=$(du -sk "$HOMEDIR" 2>/dev/null | awk '{print $1}' || echo 0)
        if [ "$max_disk" -gt 0 ] && [ "$LIVE_DISK" -gt "$max_disk" ]; then
            VIOLATIONS="${VIOLATIONS} live_disk(${LIVE_DISK}/${max_disk}KB)"
            SHOULD_SUSPEND=1
        fi
        # Update stored value
        $MYSQL -e "UPDATE radiohosting.hosting_users SET disk_used=${LIVE_DISK} WHERE id=${id};" 2>/dev/null || true
    fi

    if [ "$SHOULD_SUSPEND" = "1" ]; then
        log "SUSPENDING ${username} (${domain}):${VIOLATIONS}"

        # Use provision.sh to suspend
        u_esc=$(printf '%q' "$username")
        d_esc=$(printf '%q' "$domain")
        /var/www/radiohosting/provision.sh suspend "$username" "$domain" "$HOMEDIR" 2>/dev/null || true

        # Update DB
        $MYSQL -e "UPDATE radiohosting.hosting_users SET status='suspended', suspended_at=NOW() WHERE id=${id};" 2>/dev/null || true

        # Log to activity_logs
        $MYSQL -e "INSERT INTO radiohosting.activity_logs (account_id, action, details) VALUES (${id}, 'auto_suspended', 'Auto-suspended due to: ${VIOLATIONS}');" 2>/dev/null || true

        log "SUSPENDED ${username} due to quota violation"
    fi
done

log "Quota enforcement run complete"
exit 0
