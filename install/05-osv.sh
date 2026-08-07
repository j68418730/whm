#!/bin/bash
# 05-osv — OSV Scanner (open-source vulnerability database)
set -e
DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$DIR/lib.sh"

sc_log "osv" "install" "RUNNING" "Installing OSV-Scanner"
if command -v osv-scanner >/dev/null 2>&1; then
    sc_log "osv" "install" "SKIP" "already installed"
else
    curl -fsSL https://raw.githubusercontent.com/google/osv-scanner/main/install.sh 2>/dev/null | sh -s -- -b /usr/local/bin 2>/dev/null || {
        sc_log "osv" "install" "FAIL" "script failed"
    }
fi

cat > /usr/local/bin/ph-osvscan << 'EOF'
#!/bin/bash
# Usage: ph-osvscan [dir]
DIR="${1:-/home}"
LOG="/var/log/planethosts/osv.log"
mkdir -p /var/log/planethosts
echo "[$(date '+%Y-%m-%d %H:%M:%S')] scan start $DIR" >> "$LOG"
if command -v osv-scanner >/dev/null 2>&1; then
    osv-scanner scan "$DIR" >> "$LOG" 2>&1 || true
fi
echo "[$(date '+%Y-%m-%d %H:%M:%S')] scan done" >> "$LOG"
EOF
chmod +x /usr/local/bin/ph-osvscan

sc_status osv ok "$(osv-scanner --version 2>/dev/null || echo installed)"
sc_log "osv" "install" "OK" "OSV-Scanner installed"
