#!/bin/bash
# 11-goaccess — real-time log analyzer
set -e
DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$DIR/lib.sh"

sc_log "goaccess" "install" "RUNNING" "Installing GoAccess"
$PKG_INSTALL goaccess 2>/dev/null || true

cat > /usr/local/bin/ph-goaccess << 'EOF'
#!/bin/bash
# Generate HTML report from apache/nginx logs
OUT="/var/www/radiohosting/storage/security/goaccess-report.html"
mkdir -p "$(dirname "$OUT")"
LOGSOURCE="/var/log/apache2/access.log"
[ -f /var/log/nginx/access.log ] && [ ! -s "$LOGSOURCE" ] && LOGSOURCE="/var/log/nginx/access.log"
if command -v goaccess >/dev/null 2>&1; then
    goaccess "$LOGSOURCE" --log-format=COMBINED --output "$OUT" 2>/dev/null || true
fi
echo "$OUT"
EOF
chmod +x /usr/local/bin/ph-goaccess

sc_status goaccess ok "$(goaccess --version 2>/dev/null | head -1 || echo installed)"
sc_log "goaccess" "install" "OK" "GoAccess installed"
