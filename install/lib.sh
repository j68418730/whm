#!/bin/bash
# Planet Hosts — Security Center shared library
# Source this from install/*.sh modules.
# Provides logging, OS detection, package manager, status helpers.

# ─── Logging ───
SC_LOG="/var/log/planethosts/security-center.log"
mkdir -p /var/log/planethosts 2>/dev/null || true
sc_log() {
    local module="$1" action="$2" status="$3" msg="$4"
    local ts=$(date '+%Y-%m-%d %H:%M:%S')
    echo "$ts | $module | $action | $status | $msg" >> "$SC_LOG"
    echo "[$status] [$module] $msg"
}

# ─── OS detection + package manager ───
detect_os() {
    if [ -f /etc/os-release ]; then
        . /etc/os-release
        OS_ID="$ID"
        OS_NAME="$NAME"
        OS_VERSION_ID="$VERSION_ID"
    else
        OS_ID="linux"
        OS_NAME="Linux"
        OS_VERSION_ID=""
    fi
    case "$OS_ID" in
        debian|ubuntu) PKG_MGR="apt"; PKG_INSTALL="apt-get install -y"; PKG_UPDATE="apt-get update -y";;
        almalinux|rocky|centos|fedora|rhel) PKG_MGR="dnf"; PKG_INSTALL="dnf install -y"; PKG_UPDATE="dnf makecache -y";;
        *) PKG_MGR="apt"; PKG_INSTALL="apt-get install -y"; PKG_UPDATE="apt-get update -y";;
    esac
    export OS_ID OS_NAME OS_VERSION_ID PKG_MGR PKG_INSTALL PKG_UPDATE
}

detect_os

# ─── Status helpers (write to a status file the UI can read) ───
SC_STATUS_DIR="/var/www/radiohosting/storage/security"
mkdir -p "$SC_STATUS_DIR" 2>/dev/null || true
sc_status() {
    # sc_status <module> <status> <version|last_result>
    local module="$1" status="$2" value="${3:-}"
    echo "$(date '+%Y-%m-%d %H:%M:%S')|$module|$status|$value" > "${SC_STATUS_DIR}/${module}.status"
}

# ─── Run module scripts in order ───
run_modules() {
    local dir="${1:-$(dirname "${BASH_SOURCE[0]}")}"
    local modules=(00-prerequisites 01-firewall 02-clamav 03-yara 04-trivy 05-osv 06-lynis 07-aide 08-rkhunter 09-chkrootkit 10-logwatch 11-goaccess 12-testssl 13-spamassassin 14-opendkim 15-security-center)
    for m in "${modules[@]}"; do
        local f="${dir}/${m}.sh"
        if [ -f "$f" ]; then
            sc_log "ORCHESTRATOR" "$m" "RUNNING" "Executing $f"
            bash "$f"
            sc_log "ORCHESTRATOR" "$m" "DONE" "Completed $f"
        else
            sc_log "ORCHESTRATOR" "$m" "SKIP" "$f not found"
        fi
    done
}
