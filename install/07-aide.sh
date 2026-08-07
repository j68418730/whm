#!/bin/bash
# 07-aide — file integrity monitoring
set +e
DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$DIR/lib.sh"

sc_log "aide" "install" "RUNNING" "Installing AIDE"
$PKG_INSTALL aide 2>/dev/null || $PKG_INSTALL aide-common 2>/dev/null || true

# Build initial database if not present
if command -v aide >/dev/null 2>&1; then
    if [ ! -f /var/lib/aide/aide.db.gz ]; then
        aideinit --yes 2>/dev/null || aide --init 2>/dev/null || true
        if [ -f /var/lib/aide/aide.db.new.gz ]; then
            mv /var/lib/aide/aide.db.new.gz /var/lib/aide/aide.db.gz 2>/dev/null || true
        fi
    fi
    # Daily check cron
    cat > /etc/cron.d/planet-aide << 'EOF'
30 4 * * * root /usr/local/bin/ph-aide >/dev/null 2>&1
EOF
    chmod 644 /etc/cron.d/planet-aide
fi

cat > /usr/local/bin/ph-aide << 'EOF'
#!/bin/bash
LOG="/var/log/planethosts/aide.log"
mkdir -p /var/log/planethosts
echo "[$(date '+%Y-%m-%d %H:%M:%S')] check start" >> "$LOG"
if command -v aide >/dev/null 2>&1; then
    aide --check >> "$LOG" 2>&1 || true
fi
echo "[$(date '+%Y-%m-%d %H:%M:%S')] check done" >> "$LOG"
EOF
chmod +x /usr/local/bin/ph-aide

sc_status aide ok "$(aide --version 2>/dev/null | head -1 || echo installed)"
sc_log "aide" "install" "OK" "AIDE installed with daily check"
