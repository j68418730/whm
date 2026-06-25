#!/bin/bash
# =========================================================
# Planet Hosts - DNS Module
# =========================================================

set -eo pipefail

LOG_DIR="/var/log/planethosts"

log() { local m="$1"; echo "$(date '+%Y-%m-%d %H:%M:%S') | DNS | $m" >> "$LOG_DIR/dns.log"; echo "[DNS] $m"; }

install_bind() {
    log "Installing BIND..."
    dnf install -y bind bind-utils || yum install -y bind bind-utils
    systemctl enable --now named
    log "BIND installed."
}

create_zone() {
    local domain="$1" ip="$2"
    if [ -z "$domain" ] || [ -z "$ip" ]; then
        echo "Usage: $0 create-zone <domain> <ip>"
        return 1
    fi
    log "Creating zone for $domain -> $ip"
    cat > "/var/named/$domain.zone" <<EOF
\$TTL 86400
@ IN SOA ns1.$domain. admin.$domain. (
    $(date +%Y%m%d%H) ; serial
    3600 ; refresh
    1800 ; retry
    604800 ; expire
    86400 ; minimum
)
@ IN NS ns1.$domain.
@ IN NS ns2.$domain.
@ IN A $ip
ns1 IN A $ip
ns2 IN A $ip
www IN A $ip
EOF
    log "Zone file created: /var/named/$domain.zone"
}

case "${1:-install}" in
    install) install_bind ;;
    create-zone) create_zone "$2" "$3" ;;
    *) echo "Usage: $0 {install|create-zone}" ;;
esac
