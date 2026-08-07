#!/bin/bash
# Planet Hosts — Security Center Modular Installer
# Orchestrates the install/*.sh modules in order.
# Each module can be run independently:  bash install/02-clamav.sh
set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
LOG_DIR="/var/log/planethosts"
mkdir -p "$LOG_DIR"

echo "=============================================="
echo " Planet Hosts — Security Center Installer"
echo " OS: $(. /etc/os-release 2>/dev/null; echo "$PRETTY_NAME")"
echo "=============================================="

# Optional flags
#   --skip-firewall  : do not run 01-firewall (existing firewall unchanged)
SKIP_FIREWALL=0
for arg in "$@"; do
    case "$arg" in
        --skip-firewall) SKIP_FIREWALL=1;;
    esac
done

run_all() {
    local dir="$SCRIPT_DIR/install"
    if [ ! -d "$dir" ]; then
        echo "ERROR: install/ directory not found next to $0"
        exit 1
    fi

    bash "$dir/00-prerequisites.sh"

    if [ "$SKIP_FIREWALL" -eq 1 ]; then
        echo "[SKIP] 01-firewall (--skip-firewall)"
    else
        bash "$dir/01-firewall.sh"
    fi

    for m in 02-clamav 03-yara 04-trivy 05-osv 06-lynis 07-aide \
             08-rkhunter 09-chkrootkit 10-logwatch 11-goaccess \
             12-testssl 13-spamassassin 14-opendkim 15-security-center; do
        if [ -f "$dir/$m.sh" ]; then
            echo "[RUN] $m"
            bash "$dir/$m.sh" || echo "[WARN] $m exited non-zero"
        else
            echo "[SKIP] $m (not found)"
        fi
    done
}

run_all
echo "=============================================="
echo " Security Center installation complete."
echo " Log: $LOG_DIR/security-center.log"
echo "=============================================="
