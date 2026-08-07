#!/bin/bash
# 10-logwatch — log analysis
set -e
DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$DIR/lib.sh"

sc_log "logwatch" "install" "RUNNING" "Installing Logwatch"
$PKG_INSTALL logwatch 2>/dev/null || true

cat > /usr/local/bin/ph-logwatch << 'EOF'
#!/bin/bash
LOG="/var/log/planethosts/logwatch.log"
mkdir -p /var/log/planethosts
echo "[$(date '+%Y-%m-%d %H:%M:%S')] log analysis" >> "$LOG"
if command -v logwatch >/dev/null 2>&1; then
    logwatch --detail Low --range today --service All --format text >> "$LOG" 2>&1 || true
fi
echo "[$(date '+%Y-%m-%d %H:%M:%S')] done" >> "$LOG"
EOF
chmod +x /usr/local/bin/ph-logwatch

sc_status logwatch ok "$(logwatch --version 2>/dev/null || echo installed)"
sc_log "logwatch" "install" "OK" "Logwatch installed"
