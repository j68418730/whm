#!/bin/bash
# 07-aide — file integrity monitoring
set +e
DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$DIR/lib.sh"

sc_log "aide" "install" "RUNNING" "Installing AIDE"
$PKG_INSTALL aide 2>/dev/null || $PKG_INSTALL aide-common 2>/dev/null || true

# Bounded config: critical config dirs only (fast + useful; full-OS scans time out)
cat > /etc/aide/aide-ph.conf << 'AIDEEOF'
database=file:/var/lib/aide/aide-ph.db
database_out=file:/var/lib/aide/aide-ph.db.new
database_new=file:/var/lib/aide/aide-ph.db.new
gzip_dbout=no
report_url=file:/var/log/aide/aide-ph.log
report_url=stdout

# Critical config + binaries only — fast and useful
/etc/apache2 p+sha256
/etc/nginx p+sha256
/etc/icecast2 p+sha256
/etc/ssh p+sha256
/etc/fail2ban p+sha256
/etc/hosts p+sha256
/etc/hosts.allow p+sha256
/etc/hosts.deny p+sha256
/usr/local/bin p+sha256
/etc/sudoers p+sha256
/etc/sudoers.d p+sha256

!/proc
!/sys
!/dev
!/run
!/tmp
!/var/log
!/var/cache
AIDEEOF

cat > /usr/local/bin/ph-aide << 'EOF'
#!/bin/bash
LOG="/var/log/planethosts/aide.log"
mkdir -p /var/log/planethosts
CONF="/etc/aide/aide-ph.conf"
echo "[$(date '+%Y-%m-%d %H:%M:%S')] check start" >> "$LOG"
if command -v aide >/dev/null 2>&1 && [ -f "$CONF" ]; then
    if [ "$1" = "--baseline" ] || [ ! -f /var/lib/aide/aide-ph.db ]; then
        echo "[$(date '+%Y-%m-%d %H:%M:%S')] baseline rebuild" >> "$LOG"
        timeout 180 aide --config="$CONF" --init >> "$LOG" 2>&1 || true
        cp /var/lib/aide/aide-ph.db.new /var/lib/aide/aide-ph.db 2>/dev/null || true
    fi
    timeout 120 aide --config="$CONF" --check >> "$LOG" 2>&1 || true
fi
echo "[$(date '+%Y-%m-%d %H:%M:%S')] check done" >> "$LOG"
EOF
chmod +x /usr/local/bin/ph-aide

# Daily check cron
cat > /etc/cron.d/planet-aide << 'EOF'
30 4 * * * root /usr/local/bin/ph-aide >/dev/null 2>&1
EOF
chmod 644 /etc/cron.d/planet-aide

sc_status aide ok "$(aide --version 2>/dev/null | head -1 || echo installed)"
sc_log "aide" "install" "OK" "AIDE installed with bounded config + daily check"
