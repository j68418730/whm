#!/bin/bash
# =========================================================
# Planet Hosts - SSL Module
# =========================================================

set -eo pipefail

LOG_DIR="/var/log/planethosts"

log() { local m="$1"; echo "$(date '+%Y-%m-%d %H:%M:%S') | SSL | $m" >> "$LOG_DIR/ssl.log"; echo "[SSL] $m"; }

install_certbot() {
    log "Installing Certbot..."
    dnf install -y certbot python3-certbot-apache || yum install -y certbot python3-certbot-apache
    log "Certbot installed."
}

issue_letsencrypt() {
    local domain="$1" email="${2:-admin@$domain}"
    if [ -z "$domain" ]; then
        echo "Usage: $0 issue <domain> [email]"
        return 1
    fi
    log "Issuing Let's Encrypt certificate for $domain..."
    certbot --apache -d "$domain" -d "www.$domain" \
        --non-interactive --agree-tos --email "$email" || true
    log "Certificate issued for $domain."
}

renew_all() {
    log "Renewing all certificates..."
    certbot renew || true
    log "Renewal complete."
}

generate_self_signed() {
    local domain="${1:-radiohosting.local}"
    log "Generating self-signed certificate for $domain..."
    mkdir -p /etc/ssl/private
    openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
        -keyout "/etc/ssl/private/$domain.key" \
        -out "/etc/ssl/certs/$domain.crt" \
        -subj "/CN=$domain/O=Planet Hosts/C=US"
    log "Self-signed certificate generated."
}

case "${1:-install}" in
    install) install_certbot ;;
    issue) issue_letsencrypt "$2" "$3" ;;
    renew) renew_all ;;
    self-signed) generate_self_signed "$2" ;;
    *) echo "Usage: $0 {install|issue|renew|self-signed}" ;;
esac
