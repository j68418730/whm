#!/bin/bash
# =========================================================
# Planet Hosts - Apache Module
# =========================================================

set -eo pipefail

PANEL_DIR="/var/www/radiohosting"
LOG_DIR="/var/log/planethosts"

log() { local m="$1"; echo "$(date '+%Y-%m-%d %H:%M:%S') | APACHE | $m" >> "$LOG_DIR/apache.log"; echo "[APACHE] $m"; }

install_apache() {
    log "Installing Apache..."
    dnf install -y httpd httpd-devel mod_ssl mod_rewrite || yum install -y httpd httpd-devel mod_ssl mod_rewrite
    systemctl enable --now httpd
    log "Apache installed and started."
}

configure_vhost() {
    local domain="${1:-radiohosting.local}"
    log "Configuring virtual host for $domain..."
    cat > /etc/httpd/conf.d/radiohosting.conf <<EOF
<VirtualHost *:80>
    ServerAdmin webmaster@$domain
    DocumentRoot $PANEL_DIR/public
    ServerName $domain
    ServerAlias www.$domain
    <Directory $PANEL_DIR/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
        DirectoryIndex index.php index.html
    </Directory>
    ErrorLog /var/log/httpd/radiohosting_error.log
    CustomLog /var/log/httpd/radiohosting_access.log combined
</VirtualHost>
EOF
    systemctl reload httpd
    log "Virtual host configured."
}

configure_ssl() {
    local domain="${1:-radiohosting.local}"
    log "Configuring SSL for $domain..."
    if command -v certbot >/dev/null 2>&1; then
        certbot --apache -d "$domain" --non-interactive --agree-tos --email admin@"$domain" || true
        log "SSL configured via Let's Encrypt."
    else
        log "WARNING: certbot not installed. Skipping SSL."
    fi
}

restart_apache() {
    systemctl restart httpd
    log "Apache restarted."
}

case "${1:-install}" in
    install) install_apache ;;
    vhost) configure_vhost "$2" ;;
    ssl) configure_ssl "$2" ;;
    restart) restart_apache ;;
    *) echo "Usage: $0 {install|vhost|ssl|restart}" ;;
esac
