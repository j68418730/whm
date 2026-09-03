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

# Domains that should NEVER be auto-suspended (primary server domain, critical services)
EXEMPT_DOMAINS=(
    "planet-hosts.com"
    "suggawayz.com"
)

is_exempt_domain() {
    local domain="$1"
    for exempt in "${EXEMPT_DOMAINS[@]}"; do
        if [[ "$domain" == "$exempt" ]]; then
            return 0
        fi
    done
    return 1
}

# Get all completed/active accounts with package limits
$MYSQL -N -e "
SELECT u.id, u.username, u.domain, u.disk_used, u.bandwidth_used, u.status,
       p.disk_space, p.bandwidth, p.email_accounts, p.databases,
       p.subdomains, p.addon_domains, p.ftp_accounts, u.allow_suspension,
       u.no_auto_suspend
FROM radiohosting.hosting_users u 
JOIN radiohosting.hosting_packages p ON u.package_id = p.id 
WHERE u.status IN ('completed','active');
" 2>/dev/null | while IFS=$'\t' read -r id username domain disk_used bw_used status max_disk max_bw max_emails max_dbs max_sub max_addon max_ftp allow_suspension no_auto_suspend; do
    [ -z "$id" ] && continue
    
    # Skip auto-suspend for exempt domains (primary server domain, critical services)
    if is_exempt_domain "$domain"; then
        log "EXEMPT ${username} (${domain}) - skipping quota enforcement"
        continue
    fi
    
    # Skip auto-suspend if admin has disabled it for this account (manual only)
    if [ "${allow_suspension:-1}" = "0" ] || [ "${no_auto_suspend:-0}" = "1" ]; then
        continue
    fi
    HOMEDIR="/home/${username}"
    VIOLATIONS=""
    SHOULD_SUSPEND=0

    # ------------------------------------------------------------------
    # hosting_packages.disk_space and .bandwidth are stored in GB (see
    # database/seed_packages.sql and admin/Views/account/show.php where
    # disk_used_MB / (disk_space * 1024) is computed).
    # This script's live measurements are in KB (du -sk) and bytes, so we
    # convert the package limits to match before comparing.
    #   1 GB disk  = 1024 * 1024 KB  (1,048,576)
    #   1 GB bw    = 1024^3 bytes    (1,073,741,824)
    # ------------------------------------------------------------------
    MAX_DISK_KB=$(( max_disk * 1024 * 1024 ))
    MAX_BW_BYTES=$(( max_bw * 1024 * 1024 * 1024 ))

    # === Disk quota enforcement (disk_used in KB) ===
    if [ "$max_disk" -gt 0 ] && [ "$disk_used" -gt "$MAX_DISK_KB" ]; then
        VIOLATIONS="${VIOLATIONS} disk(${disk_used}/${MAX_DISK_KB}KB)"
        SHOULD_SUSPEND=1
    fi

    # === Bandwidth enforcement (bw_used in bytes) ===
    if [ "$max_bw" -gt 0 ] && [ "$bw_used" -gt "$MAX_BW_BYTES" ]; then
        VIOLATIONS="${VIOLATIONS} bw(${bw_used}/${MAX_BW_BYTES}bytes)"
        SHOULD_SUSPEND=1
    fi

    # === Actual disk usage check (live, du -sk = KB) ===
    if [ -d "$HOMEDIR" ]; then
        LIVE_DISK=$(du -sk "$HOMEDIR" 2>/dev/null | awk '{print $1}' || echo 0)
        if [ "$max_disk" -gt 0 ] && [ "$LIVE_DISK" -gt "$MAX_DISK_KB" ]; then
            VIOLATIONS="${VIOLATIONS} live_disk(${LIVE_DISK}/${MAX_DISK_KB}KB)"
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
