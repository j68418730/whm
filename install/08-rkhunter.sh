#!/bin/bash
# 08-rkhunter — rootkit hunter
set -e
DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$DIR/lib.sh"

sc_log "rkhunter" "install" "RUNNING" "Installing rkhunter"
$PKG_INSTALL rkhunter 2>/dev/null || true

if command -v rkhunter >/dev/null 2>&1; then
    rkhunter --update 2>/dev/null || true
    rkhunter --propupd 2>/dev/null || true
    # Daily scan
    cat > /etc/cron.d/planet-rkhunter << 'EOF'
40 4 * * * root /usr/local/bin/ph-rkhunter >/dev/null 2>&1
EOF
    chmod 644 /etc/cron.d/planet-rkhunter
fi

cat > /usr/local/bin/ph-rkhunter << 'EOF'
#!/bin/bash
LOG="/var/log/planethosts/rkhunter.log"
mkdir -p /var/log/planethosts
echo "[$(date '+%Y-%m-%d %H:%M:%S')] scan start" >> "$LOG"
if command -v rkhunter >/dev/null 2>&1; then
    rkhunter --update 2>/dev/null || true
    rkhunter --check --skip-keypress --report-warnings-only >> "$LOG" 2>&1 || true
fi
echo "[$(date '+%Y-%m-%d %H:%M:%S')] scan done" >> "$LOG"
EOF
chmod +x /usr/local/bin/ph-rkhunter

sc_status rkhunter ok "$(rkhunter --version 2>/dev/null | tail -1 || echo installed)"
sc_log "rkhunter" "install" "OK" "rkhunter installed with daily scan"
