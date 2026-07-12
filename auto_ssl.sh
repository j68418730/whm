#!/bin/bash
# auto_ssl.sh — Creates DNS zone + Apache vhost + SSL cert for a new account
# Usage: auto_ssl.sh <username> <domain> <home_dir>

set -e

USERNAME="$1"
DOMAIN="$2"
HOME_DIR="$3"

if [ -z "$USERNAME" ] || [ -z "$DOMAIN" ]; then
    echo "Usage: $0 <username> <domain> [home_dir]"
    exit 1
fi

if [ -z "$HOME_DIR" ]; then
    HOME_DIR="/home/$USERNAME"
fi

# === 1. Create DNS zone ===
ZONE_FILE="/etc/bind/zones/db.$DOMAIN"
if [ ! -f "$ZONE_FILE" ]; then
    cat > "$ZONE_FILE" << ZONEEOF
\$TTL    604800
@       IN      SOA     ns1.planet-hosts.com. admin.$DOMAIN. (
                  $(date +%Y%m%d)01     ; Serial
                  604800         ; Refresh
                  86400          ; Retry
                  2419200        ; Expire
                  604800         ; Negative Cache TTL
)
; Nameservers
@       IN      NS      ns1.planet-hosts.com.
@       IN      NS      ns2.planet-hosts.com.

; A records
@       IN      A       15.204.114.226
www     IN      A       15.204.114.226
ZONEEOF

    # Add to named.conf.local if not already there
    if ! grep -q "zone \"$DOMAIN\"" /etc/bind/named.conf.local; then
        cat >> /etc/bind/named.conf.local << CONFEOF

zone "$DOMAIN" {
    type master;
    file "/etc/bind/zones/db.$DOMAIN";
};
CONFEOF
    fi

    # Verify and reload
    named-checkzone "$DOMAIN" "$ZONE_FILE" 2>/dev/null
    named-checkconf 2>/dev/null
    systemctl reload named 2>/dev/null
fi

# === 2. Create Apache vhost (port 80) if not exists ===
VHOST_FILE="/etc/apache2/sites-available/$USERNAME.conf"
if [ ! -f "$VHOST_FILE" ]; then
    cat > "$VHOST_FILE" << VHOSTEOF
<VirtualHost *:80>
    ServerAdmin webmaster@$DOMAIN
    ServerName $DOMAIN
    ServerAlias www.$DOMAIN
    DocumentRoot $HOME_DIR/public_html
    <Directory $HOME_DIR/public_html>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
        DirectoryIndex index.php index.html
    </Directory>
    ErrorLog /var/log/apache2/${DOMAIN}_error.log
    CustomLog /var/log/apache2/${DOMAIN}_access.log combined
</VirtualHost>
VHOSTEOF
    a2ensite "$USERNAME.conf" 2>/dev/null
fi

# === 3. Get SSL cert via certbot ===
certbot --apache -d "$DOMAIN" -d "www.$DOMAIN" --non-interactive --agree-tos --email "support@planet-hosts.com" --redirect 2>/dev/null

# === 4. Reload Apache ===
systemctl reload apache2 2>/dev/null

echo "SSL setup complete for $DOMAIN"
