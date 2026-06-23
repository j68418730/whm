#!/bin/bash
# Planet Hosts - Complete Debian 12 Installer
set -e

if [ "$EUID" -ne 0 ]; then echo "Run as root."; exit 1; fi

SERVER_IP=$(curl -s --max-time 5 https://ifconfig.me/ip 2>/dev/null || echo "127.0.0.1")
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PANEL_DIR="/var/www/radiohosting"

echo "=============================================="
echo " Planet Hosts - Debian 12 Installer"
echo " Server IP: $SERVER_IP"
echo "=============================================="

# Pre-seed Icecast passwords
echo "icecast2 icecast2/icecast2 boolean true" | debconf-set-selections
echo "icecast2 icecast2/sourcepassword password $(hostname)" | debconf-set-selections
echo "icecast2 icecast2/relaypassword password $(hostname)" | debconf-set-selections
echo "icecast2 icecast2/adminpassword password $(hostname)" | debconf-set-selections
export DEBIAN_FRONTEND=noninteractive

# 1. System update
echo "[1/11] Updating system..."
apt update -qq && apt upgrade -y -qq

# 2. Full LAMP + services
echo "[2/10] Installing Apache, PHP, MariaDB, services, and jailkit..."
apt install -y -qq apache2 mariadb-server jailkit quota quotatool \
  php php-cli php-common php-curl php-gd php-intl php-mbstring php-mysql \
  php-xml php-zip php-bcmath php-bz2 php-ctype php-exif php-fileinfo \
  php-ftp php-imap php-ldap php-opcache php-redis php-sockets php-tokenizer \
  php-xmlreader php-xsl php-apcu php-imagick php-soap \
  postfix dovecot-imapd dovecot-pop3d vsftpd bind9 \
  unzip wget curl git openssl \
  firewalld fail2ban

systemctl enable --now apache2 mariadb postfix dovecot vsftpd named

# 3. Streaming stack
echo "[3/8] Installing streaming stack..."
apt install -y -qq icecast2 liquidsoap ezstream-ffmpeg ffmpeg

# 4a. SteamCMD + Game Server support
echo "[4/8 - Game Server] Installing SteamCMD..."
dpkg --add-architecture i386 2>/dev/null || true
apt update -qq && apt install -y -qq steamcmd 2>/dev/null || {
    echo "SteamCMD apt failed, downloading manually..."
    cd /usr/games
    curl -sqL https://steamcdn-a.akamaihd.net/client/installer/steamcmd_linux.tar.gz | tar zxf -
    ln -sf /usr/games/steamcmd.sh /usr/games/steamcmd
}
mkdir -p /home/gameservers
echo "SteamCMD installed at /usr/games/steamcmd"

# 4b. phpMyAdmin + SnappyMail (replaces Roundcube)
echo "[4/9] Installing phpMyAdmin, SnappyMail..."
apt install -y -qq phpmyadmin
SM_VER="2.38.2"
if [ ! -d "$PANEL_DIR/public/snappymail" ]; then
    mkdir -p "$PANEL_DIR/public/snappymail"
    cd /tmp
    curl -sL -o snappymail.zip "https://github.com/the-djmaze/snappymail/releases/download/v${SM_VER}/snappymail-${SM_VER}.zip"
    unzip -qo snappymail.zip -d "$PANEL_DIR/public/snappymail"
    rm -f snappymail.zip
    chown -R www-data:www-data "$PANEL_DIR/public/snappymail"
    # Configure default domain with shortLogin (local part only for IMAP)
    mkdir -p "$PANEL_DIR/public/snappymail/data/_data_/_default_/domains" 2>/dev/null || true
    cat > "$PANEL_DIR/public/snappymail/data/_data_/_default_/domains/default.json" <<'SMCFG'
{
    "IMAP": {
        "host": "localhost", "port": 143, "type": 0, "timeout": 300,
        "shortLogin": true, "lowerLogin": true,
        "sasl": ["SCRAM-SHA3-512", "SCRAM-SHA-512", "SCRAM-SHA-256", "SCRAM-SHA-1", "PLAIN", "LOGIN"],
        "ssl": { "verify_peer": false, "verify_peer_name": false, "allow_self_signed": false, "SNI_enabled": true, "disable_compression": true, "security_level": 1 },
        "disabled_capabilities": ["METADATA", "OBJECTID", "PREVIEW","STATUS=SIZE"],
        "use_expunge_all_on_delete": false, "fast_simple_search": true,
        "force_select": false, "message_all_headers": false, "message_list_limit": 10000, "search_filter": ""
    },
    "SMTP": {
        "host": "localhost", "port": 25, "type": 0, "timeout": 60,
        "shortLogin": true, "lowerLogin": true,
        "sasl": ["SCRAM-SHA3-512", "SCRAM-SHA-512", "SCRAM-SHA-256", "SCRAM-SHA-1", "PLAIN", "LOGIN"],
        "ssl": { "verify_peer": false, "verify_peer_name": false, "allow_self_signed": false, "SNI_enabled": true, "disable_compression": true, "security_level": 1 },
        "useAuth": false, "setSender": false, "usePhpMail": false
    },
    "Sieve": { "host": "localhost", "port": 4190, "type": 0, "timeout": 10, "shortLogin": false, "lowerLogin": true, "sasl": ["SCRAM-SHA3-512", "SCRAM-SHA-512", "SCRAM-SHA-256", "SCRAM-SHA-1", "PLAIN", "LOGIN"], "ssl": { "verify_peer": false, "verify_peer_name": false, "allow_self_signed": false, "SNI_enabled": true, "disable_compression": true, "security_level": 1 }, "enabled": false },
    "whiteList": ""
}
SMCFG
    chown -R www-data:www-data "$PANEL_DIR/public/snappymail"
fi

# Create panel ports vhost config
cat > /etc/apache2/sites-available/panel-ports.conf <<'VHOSTS'
<VirtualHost *:2082>
    DocumentRoot $PANEL_DIR/public
    ServerName $SERVER_IP
    <Directory $PANEL_DIR/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    RewriteEngine On
    RewriteRule ^/$ /portal_user.php [L]
</VirtualHost>
<VirtualHost *:2086>
    DocumentRoot $PANEL_DIR/public
    ServerName $SERVER_IP
    <Directory $PANEL_DIR/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    RewriteEngine On
    RewriteRule ^/$ /portal_reseller.php [L]
</VirtualHost>
<VirtualHost *:2087>
    DocumentRoot $PANEL_DIR/public
    ServerName $SERVER_IP
    <Directory $PANEL_DIR/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    RewriteEngine On
    RewriteRule ^/$ /admin/login [L,R=302]
</VirtualHost>
<VirtualHost *:2096>
    DocumentRoot $PANEL_DIR/public
    ServerName $SERVER_IP
    <Directory $PANEL_DIR/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    RewriteEngine On
    RewriteRule ^/$ /portal_webmail.php [L]
</VirtualHost>
VHOSTS
a2ensite panel-ports 2>/dev/null || true

# 5. Panel files
echo "[5/8] Installing panel files..."
mkdir -p "$PANEL_DIR"
cp -r "$SCRIPT_DIR"/. "$PANEL_DIR"/ 2>/dev/null || true
rm -f "$PANEL_DIR/scripts/keygen.php" "$PANEL_DIR/config/license_private.pem" 2>/dev/null || true
chown -R www-data:www-data "$PANEL_DIR"
chmod -R 755 "$PANEL_DIR"

# 6. Apache vhost
echo "[6/8] Configuring Apache..."
cat > /etc/apache2/sites-available/radiohosting.conf <<VHOST
<VirtualHost *:80>
    ServerAdmin webmaster@localhost
    DocumentRoot $PANEL_DIR/public
    <Directory $PANEL_DIR/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
        DirectoryIndex index.php index.html
    </Directory>
    ErrorLog /var/log/apache2/radiohosting_error.log
    CustomLog /var/log/apache2/radiohosting_access.log combined
</VirtualHost>
VHOST
a2dissite 000-default 2>/dev/null || true
a2ensite radiohosting
a2enmod rewrite
a2enconf phpmyadmin
for port in 2082 2086 2087 2096; do
  grep -q "Listen $port" /etc/apache2/ports.conf || echo "Listen $port" >> /etc/apache2/ports.conf
done
# Open .NET Support Server ports
firewall-cmd --permanent --add-port={5000/tcp,5001/tcp} 2>/dev/null || true
# Open Icecast streaming ports
firewall-cmd --permanent --add-port=6000-10000/tcp 2>/dev/null || true
# Also open with iptables in case firewalld is masked
iptables -I INPUT -p tcp --dport 5000 -j ACCEPT 2>/dev/null || true
iptables -I INPUT -p tcp --dport 5001 -j ACCEPT 2>/dev/null || true
iptables -I INPUT -p tcp --dport 6000:10000 -j ACCEPT 2>/dev/null || true
# Open game server ports
firewall-cmd --permanent --add-port=27000-28000/tcp 2>/dev/null || true
firewall-cmd --permanent --add-port=25560-25660/tcp 2>/dev/null || true
firewall-cmd --permanent --add-port=10000-20000/tcp 2>/dev/null || true
iptables -I INPUT -p tcp --dport 27000:28000 -j ACCEPT 2>/dev/null || true
iptables -I INPUT -p tcp --dport 25560:25660 -j ACCEPT 2>/dev/null || true
iptables -I INPUT -p tcp --dport 10000:20000 -j ACCEPT 2>/dev/null || true
firewall-cmd --reload 2>/dev/null || true
systemctl restart apache2

# 7. Database
echo "[7/8] Configuring database..."
ADMIN_PASS="$(hostname)-$(openssl rand -base64 6 | tr -d '=+/')"
DB_PASS="$(openssl rand -base64 12)"

mysql -u root <<MYSQL
CREATE DATABASE IF NOT EXISTS radiohosting CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'radiouser'@'localhost' IDENTIFIED BY '$DB_PASS';
GRANT ALL PRIVILEGES ON radiohosting.* TO 'radiouser'@'localhost';
FLUSH PRIVILEGES;
MYSQL

# Import master schema (all tables)
mysql -u root radiohosting < "$SCRIPT_DIR/database/install.sql" 2>/dev/null || \
  mysql -u root radiohosting < "$SCRIPT_DIR/database/schema.sql" 2>/dev/null || true
# Also import individual schemas in case install.sql is missing
for f in billing support automation api tables; do
  [ -f "$SCRIPT_DIR/database/${f}.sql" ] && mysql -u root radiohosting < "$SCRIPT_DIR/database/${f}.sql" 2>/dev/null || true
done
# Plugin schemas
for s in "$SCRIPT_DIR"/plugins/*/database/schema.sql; do
  [ -f "$s" ] && mysql -u root radiohosting < "$s" 2>/dev/null || true
done

# .env
cat > "$PANEL_DIR/.env" <<ENV
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=radiohosting
DB_USERNAME=radiouser
DB_PASSWORD=$DB_PASS
ENV
chmod 600 "$PANEL_DIR/.env"

# phpMyAdmin auto-login as root
php -r "
\$c = file_get_contents('/etc/phpmyadmin/config.inc.php');
\$search = \"\\\$cfg['Servers'][\\\$i]['auth_type'] = 'cookie';\";
\$replace = \"\\\$cfg['Servers'][\\\$i]['auth_type'] = 'config';\n\\\$cfg['Servers'][\\\$i]['user'] = 'root';\n\\\$cfg['Servers'][\\\$i]['password'] = 'Skylinehosting171';\";
\$c = str_replace(\$search, \$replace, \$c);
file_put_contents('/etc/phpmyadmin/config.inc.php', \$c);
echo 'phpMyAdmin config set.\n';
"

# Fix MySQL root for TCP access
mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED BY 'Skylinehosting171'; GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost' WITH GRANT OPTION; FLUSH PRIVILEGES;"

# Install timestamp for trial
echo $(date +%s) > "$PANEL_DIR/.installed"
chmod 644 "$PANEL_DIR/.installed"

# Automation cron (runs every 5 minutes)
echo "* * * * * php $PANEL_DIR/public/index.php /admin/automation/run >/dev/null 2>&1" > /etc/cron.d/planet-hosts-automation
chmod 644 /etc/cron.d/planet-hosts-automation

# 8. License activation
echo "[8/8] License activation..."
if [ -f "$SCRIPT_DIR/license.key" ]; then
    cp "$SCRIPT_DIR/license.key" "$PANEL_DIR/license.key"
    echo "License key found and installed."
else
    echo ""
    echo "=============================================="
    echo " LICENSE REQUIRED"
    echo "=============================================="
    echo " This panel requires a license key to operate."
    echo " To obtain a license key, email:"
    echo ""
    echo "   nd2no_19@hotmail.com"
    echo ""
    echo " Include your server IP ($SERVER_IP) in the email."
    echo "=============================================="
    echo ""
    read -t 30 -p "Paste license key (or press Enter to skip): " LICENSE_CONTENT
    if [ -n "$LICENSE_CONTENT" ]; then
        echo "$LICENSE_CONTENT" > "$PANEL_DIR/license.key"
        echo "License key saved."
    else
        echo "Skipping license activation."
    fi
fi
[ -f "$SCRIPT_DIR/config/license_public.pem" ] && cp "$SCRIPT_DIR/config/license_public.pem" "$PANEL_DIR/config/license_public.pem" 2>/dev/null || true

# Set admin user
php -r "
\$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4','radiouser','$DB_PASS');
\$hash = password_hash('$ADMIN_PASS', PASSWORD_DEFAULT);
\$pdo->exec(\"UPDATE admins SET username='root', password_hash='\$hash', email='root@planet-hosts.com' WHERE id=1\");
echo \"Admin set.\n\";
"

# 9. .NET 8 Support Server
echo "[9/9] Installing .NET 8 Support Server..."
wget -q https://dot.net/v1/dotnet-install.sh -O /tmp/dotnet-install.sh
bash /tmp/dotnet-install.sh --channel 8.0 --install-dir /usr/share/dotnet --runtime aspnetcore >/dev/null 2>&1
ln -sf /usr/share/dotnet/dotnet /usr/bin/dotnet 2>/dev/null || true
mkdir -p /opt/PlanetHosts.Support.Server
cp -r "$SCRIPT_DIR/SupportServer/." /opt/PlanetHosts.Support.Server/ 2>/dev/null || true
cat > /etc/systemd/system/planet-support.service << 'SERVICEEOF'
[Unit]
Description=PlanetHosts Support Server (.NET 8)
After=network.target mariadb.service

[Service]
WorkingDirectory=/opt/PlanetHosts.Support.Server
ExecStart=/usr/bin/dotnet /opt/PlanetHosts.Support.Server/PlanetHosts.Support.Server.dll
Restart=always
RestartSec=10
KillSignal=SIGINT
SyslogIdentifier=planet-support
User=root
Environment=ASPNETCORE_URLS=http://0.0.0.0:5000
Environment=ASPNETCORE_ENVIRONMENT=Production

[Install]
WantedBy=multi-user.target
SERVICEEOF
systemctl daemon-reload 2>/dev/null
systemctl enable planet-support 2>/dev/null
systemctl start planet-support 2>/dev/null || true
echo "[9/9] .NET Support Server installed on port 5000"

# 10. SteamCMD for Game Servers
echo "[10/10] Installing SteamCMD..."
dpkg --add-architecture i386 2>/dev/null
apt-get update -qq 2>/dev/null
apt-get install -y -qq steamcmd 2>/dev/null || {
    mkdir -p /usr/games
    cd /usr/games
    curl -sqL https://steamcdn-a.akamaihd.net/client/installer/steamcmd_linux.tar.gz | tar zxf - 2>/dev/null
    chmod +x steamcmd.sh
    ln -sf /usr/games/steamcmd.sh /usr/games/steamcmd 2>/dev/null
}
echo "[10/10] SteamCMD ready"

# 11. Apache proxy for SignalR
echo "[11/11] Configuring Apache proxy for SignalR..."
a2enmod proxy proxy_http proxy_wstunnel 2>/dev/null
printf 'ProxyPass /hub/ http://localhost:5000/hub/\nProxyPassReverse /hub/ http://localhost:5000/hub/\n' > /etc/apache2/conf-enabled/proxy-signalr.conf
# 12. DJ & Chat dedicated ports
echo "[12/12] Setting up DJ (2100) and Chat (2101) ports..."
printf '<VirtualHost *:2100>\n    DocumentRoot /var/www/radiohosting/public\n    ServerName %s\n    <Directory /var/www/radiohosting/public>\n        Options Indexes FollowSymLinks\n        AllowOverride All\n        Require all granted\n    </Directory>\n</VirtualHost>\n' "$SERVER_IP" > /etc/apache2/sites-available/dj-panel.conf
printf '<VirtualHost *:2101>\n    DocumentRoot /var/www/radiohosting/public\n    ServerName %s\n    <Directory /var/www/radiohosting/public>\n        Options Indexes FollowSymLinks\n        AllowOverride All\n        Require all granted\n    </Directory>\n</VirtualHost>\n' "$SERVER_IP" > /etc/apache2/sites-available/chat-panel.conf
printf '\nListen 2100\nListen 2101\n' >> /etc/apache2/ports.conf
a2ensite dj-panel.conf chat-panel.conf 2>/dev/null
firewall-cmd --add-port=2100/tcp --permanent 2>/dev/null
firewall-cmd --add-port=2101/tcp --permanent 2>/dev/null
firewall-cmd --reload 2>/dev/null
systemctl reload apache2 2>/dev/null
echo "[11/11] SignalR proxy configured"
echo ""
echo " Gamepack folder (in Masterinstall) contains game definitions for"
echo " quick deployment. To pre-download game files, run:"
echo "   bash Masterinstall/Gamepack/predownload.sh"
echo " on a machine with sufficient disk space, then copy the"
echo " downloaded/ folder contents into Gamepack/."

# Copy theme into public
rm -rf "$PANEL_DIR/public/theme" 2>/dev/null
cp -r "$PANEL_DIR/theme" "$PANEL_DIR/public/theme" 2>/dev/null || true
# Copy themes into public/theme/themes
cp -r "$PANEL_DIR/theme/themes" "$PANEL_DIR/public/theme/themes" 2>/dev/null || true
# Copy livechat banners
cp -r "$PANEL_DIR/theme/assets/img/livechat" "$PANEL_DIR/public/theme/assets/img/livechat" 2>/dev/null || true

echo ""
echo "=============================================="
echo " Installation Complete"
echo "=============================================="
echo " Panel: http://$SERVER_IP/"
echo " phpMyAdmin: http://$SERVER_IP/phpmyadmin"
echo " Voice Test: http://$SERVER_IP/voice/admin.php"
echo ""
echo " Admin Login: root"
echo " Admin Password: $ADMIN_PASS"
echo " DB Password: $DB_PASS"
echo ""
echo " Services: Apache, MariaDB, Postfix, Dovecot,"
echo "           VSFTPD, Bind9, Icecast2, Firewalld, Fail2ban"
echo "           .NET 8 Support Server (port 5000)"
echo ""
