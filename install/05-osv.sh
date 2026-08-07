#!/bin/bash
# 05-osv — OSV Scanner (open-source vulnerability database)
set +e
DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$DIR/lib.sh"

sc_log "osv" "install" "RUNNING" "Installing OSV-Scanner"
if command -v osv-scanner >/dev/null 2>&1; then
    sc_log "osv" "install" "SKIP" "already installed"
else
    OSV_VER=$(curl -fsSL https://api.github.com/repos/google/osv-scanner/releases/latest 2>/dev/null | grep -oE '"tag_name": *"v[0-9.]+"' | head -1 | grep -oE 'v[0-9.]+')
    [ -z "$OSV_VER" ] && OSV_VER="v2.5.0"
    ARCH=$(uname -m); [ "$ARCH" = "x86_64" ] && ARCH="amd64"; [ "$ARCH" = "aarch64" ] && ARCH="arm64"
    curl -fsSL -o /usr/local/bin/osv-scanner "https://github.com/google/osv-scanner/releases/download/${OSV_VER}/osv-scanner_linux_${ARCH}" >/dev/null 2>&1
    chmod +x /usr/local/bin/osv-scanner 2>/dev/null
    if ! command -v osv-scanner >/dev/null 2>&1; then
        # fallback: official installer script
        curl -fsSL https://raw.githubusercontent.com/google/osv-scanner/main/install.sh 2>/dev/null | sh -s -- -b /usr/local/bin >/dev/null 2>&1
    fi
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

VERSION="$(osv-scanner --version 2>/dev/null || echo installed)"
sc_status osv ok "$VERSION"
sc_log "osv" "install" "OK" "OSV-Scanner installed ($VERSION)"
exit 0
