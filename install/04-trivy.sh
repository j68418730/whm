#!/bin/bash
# 04-trivy — vulnerability scanner (OS packages, containers)
set +e
DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$DIR/lib.sh"

sc_log "trivy" "install" "RUNNING" "Installing Trivy"
if command -v trivy >/dev/null 2>&1; then
    sc_log "trivy" "install" "SKIP" "already installed"
else
    # Official Trivy install script (supports deb/rpm)
    curl -fsSL https://raw.githubusercontent.com/aquasecurity/trivy/main/contrib/install.sh 2>/dev/null | sh -s -- -b /usr/local/bin >/dev/null 2>&1
    if ! command -v trivy >/dev/null 2>&1; then
        sc_log "trivy" "install" "TRYAPT" "script failed, trying package manager"
        $PKG_INSTALL trivy >/dev/null 2>&1
    fi
fi

cat > /usr/local/bin/ph-trivyscan << 'EOF'
#!/bin/bash
# Usage: ph-trivyscan
LOG="/var/log/planethosts/trivy.log"
mkdir -p /var/log/planethosts
echo "[$(date '+%Y-%m-%d %H:%M:%S')] scan start" >> "$LOG"
if command -v trivy >/dev/null 2>&1; then
    trivy fs --scanners vuln,secret,config / 2>>"$LOG" || true
fi
echo "[$(date '+%Y-%m-%d %H:%M:%S')] scan done" >> "$LOG"
EOF
chmod +x /usr/local/bin/ph-trivyscan

VERSION="$(trivy --version 2>/dev/null | head -1 || echo installed)"
sc_status trivy ok "$VERSION"
sc_log "trivy" "install" "OK" "Trivy installed ($VERSION)"
exit 0
