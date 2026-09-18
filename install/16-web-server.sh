#!/bin/bash
# 16-web-server — Nginx + ModSecurity + OWASP CRS (idempotent)
# Nginx runs as a reverse proxy on 8080 (avoids Apache conflict on 80).
# ModSecurity + CRS are wired into Apache (libapache2-mod-security2 / mod_security).
set +e
DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$DIR/lib.sh"

sc_log "web-server" "install" "RUNNING" "Installing Nginx + ModSecurity + OWASP CRS"

# ─── Nginx ───
if ! command -v nginx >/dev/null 2>&1; then
    $PKG_INSTALL nginx 2>/dev/null || true
fi
if command -v nginx >/dev/null 2>&1; then
    mkdir -p /etc/nginx/sites-available /etc/nginx/sites-enabled
    rm -f /etc/nginx/sites-enabled/default 2>/dev/null || true
    if [ ! -f /etc/nginx/sites-available/planet-proxy ]; then
        cat > /etc/nginx/sites-available/planet-proxy << 'NGINX'
server {
    listen 8080 default_server;
    listen [::]:8080 default_server;
    server_name _;
    root /var/www/radiohosting/public;
    index index.php index.html;
    location / {
        try_files $uri $uri/ /index.php?$args;
    }
    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    location ~ /\.ht { deny all; }
}
NGINX
        ln -sf /etc/nginx/sites-available/planet-proxy /etc/nginx/sites-enabled/ 2>/dev/null || true
    fi
    systemctl enable --now nginx 2>/dev/null || true
    if systemctl is-active nginx >/dev/null 2>&1; then
        sc_status nginx ok "nginx active on :8080"
    else
        sc_status nginx warn "nginx installed but not running"
    fi
    sc_log "web-server" "nginx" "OK" "Nginx configured on :8080"
else
    sc_status nginx failed "nginx package install failed"
    sc_log "web-server" "nginx" "FAIL" "nginx could not be installed"
fi

# ─── ModSecurity + OWASP CRS (Apache) ───
case "$PKG_MGR" in
    apt)
        $PKG_INSTALL libapache2-mod-security2 modsecurity-crs 2>/dev/null || true
        a2enmod security2 2>/dev/null || true
        MS_CONF="/etc/modsecurity/modsecurity.conf-recommended"
        MS_TARGET="/etc/modsecurity/modsecurity.conf"
        if [ -f "$MS_CONF" ] && [ ! -f "$MS_TARGET" ]; then
            cp "$MS_CONF" "$MS_TARGET"
        fi
        sed -i 's/^SecRuleEngine .*/SecRuleEngine On/' "$MS_TARGET" 2>/dev/null || true
        CRS_DIR="/usr/share/modsecurity-crs"
        ;;
    dnf|yum)
        $PKG_INSTALL mod_security mod_security_crs 2>/dev/null || true
        MS_TARGET="/etc/httpd/conf.d/mod_security.conf"
        sed -i 's/^SecRuleEngine .*/SecRuleEngine On/' "$MS_TARGET" 2>/dev/null || true
        CRS_DIR="/usr/share/modsecurity_crs"
        ;;
esac
if command -v modsec_rules_file >/dev/null 2>&1 || [ -d "$CRS_DIR" ] || dpkg -l modsecurity-crs >/dev/null 2>&1; then
    sc_status modsecurity ok "ModSecurity active"
    sc_status crs ok "OWASP CRS present"
    systemctl reload apache2 2>/dev/null || systemctl reload httpd 2>/dev/null || true
    sc_log "web-server" "modsecurity" "OK" "ModSecurity + CRS configured"
else
    sc_status modsecurity warn "ModSecurity not detected"
    sc_log "web-server" "modsecurity" "WARN" "ModSecurity could not be configured"
fi

exit 0