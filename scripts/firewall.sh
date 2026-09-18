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
    # Web / FTP / misc
    for port in 20/tcp 21/tcp 22/tcp 26/tcp 80/tcp 443/tcp 990/tcp; do
        firewall-cmd --permanent --add-port="$port" || true
    done
    # DNS (Bind9)
    for port in 53/tcp 53/udp; do
        firewall-cmd --permanent --add-port="$port" || true
    done
    # Database (MariaDB)
    firewall-cmd --permanent --add-port=3306/tcp || true
    # Dashboard / internal apps
    for port in 5000/tcp 5001/tcp; do
        firewall-cmd --permanent --add-port="$port" || true
    done
    # Panel ports
    for port in 2082/tcp 2083/tcp 2086/tcp 2087/tcp 2089/tcp 2096/tcp 2097/tcp 2100/tcp 2101/tcp; do
        firewall-cmd --permanent --add-port="$port" || true
    done
    # DJ / Chat / Icecast / streaming engines
    for port in 8000/tcp 8001/tcp 8002/tcp 8004/tcp 8080/tcp 8081/tcp; do
        firewall-cmd --permanent --add-port="$port" || true
    done
    # Mail (Postfix + Dovecot + ManageSieve)
    for port in 25/tcp 465/tcp 587/tcp 110/tcp 143/tcp 993/tcp 995/tcp 4190/tcp; do
        firewall-cmd --permanent --add-port="$port" || true
    done
    # FTP
    firewall-cmd --permanent --add-port=21/tcp || true
    # Streaming / media ranges (dj, shoutcast v1/v2, icecast, autodj, rtmp, rtsp, webrtc, audio relay)
    for port in 10000-10999/tcp 11000-11999/tcp 12000-13999/tcp 14000-15999/tcp 16000-16499/tcp 17000-17999/tcp 18000-18999/tcp 19000-19999/tcp 20000-20999/tcp; do
        firewall-cmd --permanent --add-port="$port" || true
    done
    # Game servers
    for port in 25560-25660/tcp 27000-28000/tcp 30000-50000/tcp; do
        firewall-cmd --permanent --add-port="$port" || true
    done
    # WebRTC media (UDP)
    firewall-cmd --permanent --add-port=50000-55000/udp || true
    firewall-cmd --reload || true
    log "Default firewall rules applied (full production port map)."
}

case "${1:-install}" in
    install) install_firewall ;;
    ports) shift; open_ports "$@" ;;
    services) shift; open_services "$@" ;;
    default) configure_default ;;
    *) echo "Usage: $0 {install|ports|services|default}" ;;
esac
