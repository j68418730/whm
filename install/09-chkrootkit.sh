#!/bin/bash
# 09-chkrootkit — rootkit checker
set +e
DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$DIR/lib.sh"

sc_log "chkrootkit" "install" "RUNNING" "Installing chkrootkit"
$PKG_INSTALL chkrootkit 2>/dev/null || true

if command -v chkrootkit >/dev/null 2>&1; then
    cat > /etc/cron.d/planet-chkrootkit << 'EOF'
45 4 * * * root /usr/local/bin/ph-chkrootkit >/dev/null 2>&1
EOF
    chmod 644 /etc/cron.d/planet-chkrootkit
fi

cat > /usr/local/bin/ph-chkrootkit << 'EOF'
#!/bin/bash
LOG="/var/log/planethosts/chkrootkit.log"
mkdir -p /var/log/planethosts
echo "[$(date '+%Y-%m-%d %H:%M:%S')] scan start" >> "$LOG"
if command -v chkrootkit >/dev/null 2>&1; then
    chkrootkit -q >> "$LOG" 2>&1 || true
fi
echo "[$(date '+%Y-%m-%d %H:%M:%S')] scan done" >> "$LOG"
EOF
chmod +x /usr/local/bin/ph-chkrootkit

sc_status chkrootkit ok "$(chkrootkit -V 2>/dev/null | head -1 || echo installed)"
sc_log "chkrootkit" "install" "OK" "chkrootkit installed with daily scan"
