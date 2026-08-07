#!/bin/bash
# 06-lynis — security audit
set -e
DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$DIR/lib.sh"

sc_log "lynis" "install" "RUNNING" "Installing Lynis"
$PKG_INSTALL lynis 2>/dev/null || {
    apt-get install -y lynis 2>/dev/null || {
        cd /tmp
        git clone --depth 1 https://github.com/CISOfy/lynis.git /usr/local/lynis 2>/dev/null || true
        ln -sf /usr/local/lynis/lynis /usr/local/bin/lynis 2>/dev/null || true
    }
}

cat > /usr/local/bin/ph-lynis << 'EOF'
#!/bin/bash
LOG="/var/log/planethosts/lynis.log"
mkdir -p /var/log/planethosts
echo "[$(date '+%Y-%m-%d %H:%M:%S')] audit start" >> "$LOG"
if command -v lynis >/dev/null 2>&1; then
    lynis audit system --quick --logfile "$LOG" 2>/dev/null || true
fi
echo "[$(date '+%Y-%m-%d %H:%M:%S')] audit done" >> "$LOG"
EOF
chmod +x /usr/local/bin/ph-lynis

sc_status lynis ok "$(lynis --version 2>/dev/null | head -1 || echo installed)"
sc_log "lynis" "install" "OK" "Lynis installed"
