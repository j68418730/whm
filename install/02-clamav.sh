#!/bin/bash
# 02-clamav — malware scanner
set +e
DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$DIR/lib.sh"

sc_log "clamav" "install" "RUNNING" "Installing ClamAV"
$PKG_INSTALL clamav clamav-daemon clamav-freshclam 2>/dev/null || \
    $PKG_INSTALL clamav clamav-daemon

systemctl enable --now clamav-freshclam 2>/dev/null || true
systemctl enable --now clamav-daemon 2>/dev/null || true

# Scan script
cat > /usr/local/bin/ph-clamscan << 'EOF'
#!/bin/bash
# Usage: ph-clamscan [dir]
DIR="${1:-/home}"
LOG="/var/log/planethosts/clamscan.log"
mkdir -p /var/log/planethosts
echo "[$(date '+%Y-%m-%d %H:%M:%S')] scan start $DIR" >> "$LOG"
if command -v clamscan >/dev/null 2>&1; then
    clamscan -ri "$DIR" --exclude-dir=/proc --exclude-dir=/sys --exclude-dir=/dev \
        --log="$LOG" --max-filesize=100M --max-scansize=200M 2>/dev/null || true
fi
echo "[$(date '+%Y-%m-%d %H:%M:%S')] scan done" >> "$LOG"
EOF
chmod +x /usr/local/bin/ph-clamscan

# Daily scan cron (quiet)
cat > /etc/cron.d/planet-clamav << 'EOF'
15 3 * * * root /usr/local/bin/ph-clamscan /home >/dev/null 2>&1
EOF
chmod 644 /etc/cron.d/planet-clamav

sc_status clamav ok "$(clamscan --version 2>/dev/null | head -1 || echo installed)"
sc_log "clamav" "install" "OK" "ClamAV installed with daily scan"
