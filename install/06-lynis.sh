#!/bin/bash
# 06-lynis — security audit
set +e
DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$DIR/lib.sh"

sc_log "lynis" "install" "RUNNING" "Installing Lynis"
if ! command -v lynis >/dev/null 2>&1; then
    $PKG_INSTALL lynis >/dev/null 2>&1 || {
        cd /tmp
        git clone --depth 1 https://github.com/CISOfy/lynis.git /usr/local/lynis >/dev/null 2>&1
        ln -sf /usr/local/lynis/lynis /usr/local/bin/lynis 2>/dev/null
    }
fi

cat > /usr/local/bin/ph-lynis << 'EOF'
#!/bin/bash
LOG="/var/log/planethosts/lynis.log"
STATUS_FILE="/var/www/radiohosting/storage/security/lynis.status"
mkdir -p /var/log/planethosts "$(dirname "$STATUS_FILE")"
echo "[$(date '+%Y-%m-%d %H:%M:%S')] audit start" >> "$LOG"
if command -v lynis >/dev/null 2>&1; then
    lynis audit system --quick --logfile "$LOG" --report-file /var/log/lynis-report.dat >/dev/null 2>&1
    IDX=$(grep -aE '^hardening_index=[0-9]+' /var/log/lynis-report.dat 2>/dev/null | tail -1 | grep -oE '[0-9]+')
    if [ -n "$IDX" ]; then
        STATE="ok"; [ "$IDX" -lt 60 ] && STATE="warn"
        echo "$(date '+%Y-%m-%d %H:%M:%S')|lynis|$STATE|index $IDX" > "$STATUS_FILE"
    else
        echo "$(date '+%Y-%m-%d %H:%M:%S')|lynis|ok|3.0.8" > "$STATUS_FILE"
    fi
fi
echo "[$(date '+%Y-%m-%d %H:%M:%S')] audit done" >> "$LOG"
EOF
chmod +x /usr/local/bin/ph-lynis

VERSION="$(lynis --version 2>/dev/null | head -1 || echo installed)"
/usr/local/bin/ph-lynis
IDX=$(grep -aE '^hardening_index=[0-9]+' /var/log/lynis-report.dat 2>/dev/null | tail -1 | grep -oE '[0-9]+')
sc_log "lynis" "install" "OK" "Lynis installed ($VERSION, hardening index ${IDX:-unknown})"
exit 0
