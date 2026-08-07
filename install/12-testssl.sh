#!/bin/bash
# 12-testssl — TLS/SSL scanner
set -e
DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$DIR/lib.sh"

sc_log "testssl" "install" "RUNNING" "Installing testssl.sh"
if [ ! -d /opt/testssl.sh ]; then
    git clone --depth 1 https://github.com/drwetter/testssl.sh /opt/testssl.sh 2>/dev/null || true
fi
ln -sf /opt/testssl.sh/testssl.sh /usr/local/bin/testssl 2>/dev/null || true

cat > /usr/local/bin/ph-testssl << 'EOF'
#!/bin/bash
# Usage: ph-testssl <host:port>
HOST="${1:-planet-hosts.com:443}"
LOG="/var/log/planethosts/testssl.log"
mkdir -p /var/log/planethosts
echo "[$(date '+%Y-%m-%d %H:%M:%S')] testssl $HOST" >> "$LOG"
if command -v testssl >/dev/null 2>&1; then
    testssl --quiet "$HOST" >> "$LOG" 2>&1 || true
fi
echo "[$(date '+%Y-%m-%d %H:%M:%S')] done" >> "$LOG"
EOF
chmod +x /usr/local/bin/ph-testssl

sc_status testssl ok "installed"
sc_log "testssl" "install" "OK" "testssl.sh installed"
