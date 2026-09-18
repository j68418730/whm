#!/bin/bash
# =========================================================
# Planet Hosts - Firewall Module
# =========================================================

set -eo pipefail

LOG_DIR="/var/log/planethosts"

log() { local m="$1"; echo "$(date '+%Y-%m-%d %H:%M:%S') | FIREWALL | $m" >> "$LOG_DIR/firewall.log"; echo "[FIREWALL] $m"; }

install_firewall() {
    log "Installing firewalld..."
    dnf install -y firewalld || yum install -y firewalld
    systemctl enable --now firewalld
    log "Firewalld installed."
}

open_ports() {
    log "Opening ports..."
    for port in "$@"; do
        firewall-cmd --permanent --add-port="$port" || true
        log "  Port $port opened."
    done
    firewall-cmd --reload || true
}

open_services() {
    log "Opening services..."
    for svc in "$@"; do
        firewall-cmd --permanent --add-service="$svc" || true
        log "  Service $svc opened."
    done
    firewall-cmd --reload || true
}

configure_default() {
    log "Configuring default firewall rules..."
    firewall-cmd --permanent --add-service=http || true
    firewall-cmd --permanent --add-service=https || true
    firewall-cmd --permanent --add-service=ssh || true
    # Panel ports
    firewall-cmd --permanent --add-port=2082/tcp || true
    firewall-cmd --permanent --add-port=2083/tcp || true
    firewall-cmd --permanent --add-port=2086/tcp || true
    firewall-cmd --permanent --add-port=2087/tcp || true
    firewall-cmd --permanent --add-port=2089/tcp || true
    firewall-cmd --permanent --add-port=2096/tcp || true
    # DJ / Chat / Icecast
    firewall-cmd --permanent --add-port=2100/tcp || true
    firewall-cmd --permanent --add-port=2101/tcp || true
    firewall-cmd --permanent --add-port=8000/tcp || true
    firewall-cmd --permanent --add-port=8001/tcp || true
    firewall-cmd --permanent --add-port=8080/tcp || true
    # Mail (Postfix + Dovecot)
    firewall-cmd --permanent --add-port=25/tcp || true
    firewall-cmd --permanent --add-port=465/tcp || true
    firewall-cmd --permanent --add-port=587/tcp || true
    firewall-cmd --permanent --add-port=110/tcp || true
    firewall-cmd --permanent --add-port=143/tcp || true
    firewall-cmd --permanent --add-port=993/tcp || true
    firewall-cmd --permanent --add-port=995/tcp || true
    # FTP
    firewall-cmd --permanent --add-port=21/tcp || true
    # Streaming / game ranges
    firewall-cmd --permanent --add-port=11000-11999/tcp || true
    firewall-cmd --permanent --add-port=12000-13999/tcp || true
    firewall-cmd --permanent --add-port=14000-15999/tcp || true
    firewall-cmd --permanent --add-port=16000-16499/tcp || true
    firewall-cmd --permanent --add-port=17000-17999/tcp || true
    firewall-cmd --permanent --add-port=18000-18999/tcp || true
    firewall-cmd --permanent --add-port=19000-19999/tcp || true
    firewall-cmd --permanent --add-port=20000-20999/tcp || true
    firewall-cmd --permanent --add-port=50000-55000/udp || true
    firewall-cmd --reload || true
    log "Default firewall rules applied."
}

case "${1:-install}" in
    install) install_firewall ;;
    ports) shift; open_ports "$@" ;;
    services) shift; open_services "$@" ;;
    default) configure_default ;;
    *) echo "Usage: $0 {install|ports|services|default}" ;;
esac
