#!/bin/bash
# Planet Hosts - Complete Debian 12 Installer
# Merged with logging, rollback, version tracking from install.sh
set -e

INSTALLER_VERSION="1.1.0"
START_TIME=$(date +%s)
LOG_DIR="/var/log/planethosts"

HTTPD_INSTALLED=0; MARIADB_INSTALLED=0; PHP_INSTALLED=0
FIREWALLD_INSTALLED=0; ICECAST_INSTALLED=0; LIQUIDSOAP_INSTALLED=0
EZSTREAM_INSTALLED=0; FFMPEG_INSTALLED=0; PHPMYADMIN_INSTALLED=0

log() {
    local module="$1" action="$2" status="$3" msg="$4"
    local ts=$(date '+%Y-%m-%d %H:%M:%S')
    local duration=$(( $(date +%s) - START_TIME ))
    mkdir -p "$LOG_DIR"
    echo "$ts | $module | $action | ${duration}s | $status | $msg" >> "$LOG_DIR/install.log"
    echo "[$status] $msg"
}

rollback() {
    local step="$1"
    log "ROLLBACK" "$step" "ROLLING" "Rolling back $step"
    case "$step" in
        database)
            mysql -u root -e "DROP DATABASE IF EXISTS radiohosting;" 2>/dev/null || true
            mysql -u root -e "DROP USER IF EXISTS 'radiouser'@'localhost';" 2>/dev/null || true
            log "ROLLBACK" "database" "OK" "Database removed"
            ;;
        vhost)
            rm -f /etc/apache2/sites-available/radiohosting.conf
            systemctl reload apache2 2>/dev/null || true
            log "ROLLBACK" "vhost" "OK" "Virtual host removed"
            ;;
        services)
            systemctl disable --now apache2 2>/dev/null || true
            systemctl disable --now mariadb 2>/dev/null || true
            log "ROLLBACK" "services" "OK" "Services disabled"
            ;;
    esac
}

cleanup_and_exit() {
    local code=$1
    log "INSTALLER" "exit" "$code" "Installer finished with code $code"
    exit "$code"
}

get_server_ip() {
    PUBLIC_IP=$(curl -s --max-time 5 https://ifconfig.me/ip 2>/dev/null || \
               curl -s --max-time 5 https://icanhazip.com 2>/dev/null)
    if [ -n "$PUBLIC_IP" ] && [[ "$PUBLIC_IP" =~ ^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
        echo "$PUBLIC_IP"; return
    fi
    LOCAL_IPS=$(hostname -I | tr ' ' '\n' | grep -v '^127\.' | head -1)
    if [ -n "$LOCAL_IPS" ]; then echo "$LOCAL_IPS"; return; fi
    echo "127.0.0.1"
}

install_required() {
    local label="$1"; shift
    log "PACKAGES" "$label" "INSTALLING" "Installing $label..."
    apt install -y -qq "$@" || { log "PACKAGES" "$label" "FAIL" "Failed to install $label"; cleanup_and_exit 1; }
    log "PACKAGES" "$label" "OK" "Installed $label"
}

install_optional() {
    local label="$1"; shift
    log "PACKAGES" "$label" "INSTALLING" "Installing $label..."
    if ! apt install -y -qq "$@"; then
        log "PACKAGES" "$label" "WARNING" "$label not available"; return 1
    fi
    log "PACKAGES" "$label" "OK" "Installed $label"; return 0
}

if [ "$EUID" -ne 0 ]; then echo "Run as root."; exit 1; fi

SERVER_IP=$(get_server_ip)
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PANEL_DIR="/var/www/radiohosting"

clear
echo "=============================================="
echo " Planet Hosts - Debian 12 Installer v$INSTALLER_VERSION"
echo " Server IP: $SERVER_IP"
echo "=============================================="
log "INSTALLER" "start" "OK" "Installer v$INSTALLER_VERSION started"

# Pre-seed Icecast passwords
echo "icecast2 icecast2/icecast2 boolean true" | debconf-set-selections
echo "icecast2 icecast2/sourcepassword password $(hostname)" | debconf-set-selections
echo "icecast2 icecast2/relaypassword password $(hostname)" | debconf-set-selections
echo "icecast2 icecast2/adminpassword password $(hostname)" | debconf-set-selections
export DEBIAN_FRONTEND=noninteractive

# 0. Swap file (2GB for low-memory servers)
log "SWAP" "setup" "RUNNING" "Configuring swap"
if [ ! -f /swapfile ]; then
    dd if=/dev/zero of=/swapfile bs=1M count=2048 2>/dev/null
    chmod 600 /swapfile; mkswap /swapfile 2>/dev/null; swapon /swapfile 2>/dev/null
    echo "/swapfile none swap sw 0 0" >> /etc/fstab
fi
log "SWAP" "setup" "OK" "Swap configured"

# 1. System update
echo "[1/11] Updating system..."
log "UPDATE" "system" "RUNNING" "Updating operating system"
apt update -qq && apt upgrade -y -qq
log "UPDATE" "system" "OK" "System updated"

# 2. Full LAMP + services
echo "[2/10] Installing Apache, PHP, MariaDB, services, and jailkit..."
log "STACK" "install" "RUNNING" "Installing web stack"
install_required "Web stack" apache2 mariadb-server jailkit quota quotatool \
  php php-cli php-common php-curl php-gd php-intl php-mbstring php-mysql \
  php-xml php-zip php-bcmath php-bz2 php-ctype php-exif php-fileinfo \
  php-ftp php-imap php-ldap php-opcache php-redis php-sockets php-tokenizer \
  php-xmlreader php-xsl php-apcu php-imagick php-soap \
  postfix dovecot-imapd dovecot-pop3d vsftpd bind9 \
  unzip wget curl git openssl \
  firewalld fail2ban clamav-daemon rspamd aide rkhunter chkrootkit lynis \
  certbot python3-certbot-apache nginx
# Disable default nginx site (conflicts with Apache on port 80) and reconfigure
systemctl stop nginx 2>/dev/null || true
rm -f /etc/nginx/sites-enabled/default 2>/dev/null || true
systemctl enable --now apache2 mariadb postfix dovecot vsftpd named
HTTPD_INSTALLED=1; MARIADB_INSTALLED=1; PHP_INSTALLED=1
FIREWALLD_INSTALLED=1
log "STACK" "install" "OK" "Web stack installed"

# 3. Streaming stack
echo "[3/8] Installing streaming stack..."
log "MEDIA" "install" "RUNNING" "Installing streaming stack"
install_required "Icecast" icecast2 && ICECAST_INSTALLED=1
systemctl enable --now icecast2 2>/dev/null || true

# Configure nginx on port 8080 (avoids Apache conflict on port 80)
cat > /etc/nginx/sites-available/planet-proxy << "NGINX"
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
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    location ~ /\.ht { deny all; }
}
NGINX
ln -sf /etc/nginx/sites-available/planet-proxy /etc/nginx/sites-enabled/ 2>/dev/null || true
systemctl enable --now nginx 2>/dev/null || true

# Install SHOUTcast DNAS v2
log "SHOUTCAST" "install" "RUNNING" "Installing SHOUTcast DNAS"
if [ -f "$SCRIPT_DIR/sc_serv2_linux_x64-latest.tar.gz" ]; then
    mkdir -p /usr/local/shoutcast /var/log/shoutcast
    tar xzf "$SCRIPT_DIR/sc_serv2_linux_x64-latest.tar.gz" -C /usr/local/shoutcast 2>/dev/null
    chmod 755 /usr/local/shoutcast/sc_serv 2>/dev/null
    cat > /usr/local/shoutcast/sc_serv.conf << "SCEOF"
serveradmin=admin@planet-hosts.com
adminpassword=ShoutcastAdmin171
password=Shoutcast171
portbase=8000
logfile=/var/log/shoutcast/sc_serv.log
w3clog=/var/log/shoutcast/sc_w3c.log
banfile=/usr/local/shoutcast/sc_serv.ban
ripfile=/var/log/shoutcast/sc_rip.log
maxuser=100
SCEOF
    cat > /etc/systemd/system/shoutcast.service << "UNIT"
[Unit]
Description=SHOUTcast DNAS v2 Server
After=network.target
[Service]
Type=simple
User=nobody
Group=nogroup
WorkingDirectory=/usr/local/shoutcast
ExecStart=/usr/local/shoutcast/sc_serv /usr/local/shoutcast/sc_serv.conf
Restart=on-failure
RestartSec=10
[Install]
WantedBy=multi-user.target
UNIT
    systemctl daemon-reload
    systemctl enable --now shoutcast 2>/dev/null || true
    log "SHOUTCAST" "install" "OK" "SHOUTcast installed on port 8000"
else
    log "SHOUTCAST" "install" "SKIP" "sc_serv2_linux_x64-latest.tar.gz not found in script dir"
fi

install_optional "Liquidsoap" liquidsoap && LIQUIDSOAP_INSTALLED=1
install_optional "ezstream" ezstream-ffmpeg && EZSTREAM_INSTALLED=1
install_optional "FFmpeg" ffmpeg && FFMPEG_INSTALLED=1
log "MEDIA" "install" "OK" "Streaming stack installed"

# 4a. SteamCMD + Game Server support
echo "[4/8 - Game Server] Installing SteamCMD..."
log "STEAMCMD" "install" "RUNNING" "Installing SteamCMD"
dpkg --add-architecture i386 2>/dev/null || true
apt update -qq 2>/dev/null
install_optional "SteamCMD" steamcmd 2>/dev/null || {
    log "STEAMCMD" "install" "WARNING" "SteamCMD apt failed, downloading manually"
    mkdir -p /usr/games
    cd /usr/games
    curl -sqL https://steamcdn-a.akamaihd.net/client/installer/steamcmd_linux.tar.gz | tar zxf -
    ln -sf /usr/games/steamcmd.sh /usr/games/steamcmd
}
mkdir -p /home/gameservers
log "STEAMCMD" "install" "OK" "SteamCMD ready"

# 4b. Security Tools: ModSecurity + OWASP CRS + DDoS Protection
echo "[4b/12] Installing ModSecurity, OWASP CRS, and DDoS protection..."
log "SECURITY" "install" "RUNNING" "Installing security tools"
install_optional "ModSecurity" libapache2-mod-security2 && a2enmod security2 2>/dev/null || true
mkdir -p /usr/share/modsecurity-crs
cd /tmp
curl -sL -o crs.tar.gz "https://github.com/coreruleset/coreruleset/archive/refs/tags/v4.0.0.tar.gz" 2>/dev/null || true
if [ -f crs.tar.gz ] && [ -s crs.tar.gz ]; then
    tar xzf crs.tar.gz -C /usr/share/modsecurity-crs --strip-components=1 2>/dev/null || true
    cp /usr/share/modsecurity-crs/crs-setup.conf.example /usr/share/modsecurity-crs/crs-setup.conf 2>/dev/null || true
    rm -f crs.tar.gz
fi
log "SECURITY" "install" "OK" "Security tools installed"

# DDoS iptables rules
log "SECURITY" "iptables" "RUNNING" "Configuring DDoS iptables rules"
iptables -A INPUT -p icmp --icmp-type echo-request -m limit --limit 10/second -j ACCEPT 2>/dev/null
iptables -A INPUT -p icmp --icmp-type echo-request -j DROP 2>/dev/null
iptables -A INPUT -p tcp --syn -m limit --limit 100/second --limit-burst 200 -j ACCEPT 2>/dev/null
iptables -A INPUT -p tcp --syn -j DROP 2>/dev/null
iptables -A INPUT -p tcp --dport 80 -m connlimit --connlimit-above 50 -j DROP 2>/dev/null
iptables -A INPUT -p tcp --dport 443 -m connlimit --connlimit-above 50 -j DROP 2>/dev/null
iptables-save > /etc/iptables.rules 2>/dev/null
log "SECURITY" "iptables" "OK" "DDoS rules applied"

# Fail2Ban radio jails
cat > /etc/fail2ban/jail.local << "JAIL"
[DEFAULT]
bantime = 3600; findtime = 600; maxretry = 5
[sshd]
enabled = true; port = 22; maxretry = 3
[radio-auth]
enabled = true; logpath = /var/log/radiohosting/radio_auth.log; port = 8000:8100; maxretry = 5
[radio-icecast]
enabled = true; logpath = /var/log/icecast2/error.log; port = 8000:8100; maxretry = 10; findtime = 120; bantime = 600
JAIL
systemctl restart fail2ban 2>/dev/null || true

# 4c. Node.js 20.x + npm (for chat/desktop tools)
echo "[4c/13] Installing Node.js 20.x and npm..."
log "NODEJS" "install" "RUNNING" "Installing Node.js 20.x"
if ! command -v node >/dev/null 2>&1; then
    curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
    install_required "Node.js" nodejs
fi
npm install -g npm@latest 2>/dev/null || true
log "NODEJS" "install" "OK" "Node.js $(node --version) npm $(npm --version) installed"

# 4d. phpMyAdmin + SnappyMail (replaces Roundcube)
log "PHPMYADMIN" "install" "RUNNING" "Installing phpMyAdmin"
install_optional "phpMyAdmin" phpmyadmin && PHPMYADMIN_INSTALLED=1
SM_VER="2.38.2"
if [ ! -d "$PANEL_DIR/public/snappymail" ]; then
    log "SNAPPYMAIL" "install" "RUNNING" "Installing SnappyMail v$SM_VER"
    mkdir -p "$PANEL_DIR/public/snappymail"
    cd /tmp
    curl -sL -o snappymail.zip "https://github.com/the-djmaze/snappymail/releases/download/v${SM_VER}/snappymail-${SM_VER}.zip"
    unzip -qo snappymail.zip -d "$PANEL_DIR/public/snappymail"
    rm -f snappymail.zip
    chown -R www-data:www-data "$PANEL_DIR/public/snappymail"
    mkdir -p "$PANEL_DIR/public/snappymail/data/_data_/_default_/domains" 2>/dev/null || true
    cat > "$PANEL_DIR/public/snappymail/data/_data_/_default_/domains/default.json" <<'SMCFG'
{"IMAP":{"host":"localhost","port":143,"type":0,"timeout":300,"shortLogin":true,"lowerLogin":true,"sasl":["SCRAM-SHA3-512","SCRAM-SHA-512","SCRAM-SHA-256","SCRAM-SHA-1","PLAIN","LOGIN"],"ssl":{"verify_peer":false,"verify_peer_name":false,"allow_self_signed":false,"SNI_enabled":true,"disable_compression":true,"security_level":1},"disabled_capabilities":["METADATA","OBJECTID","PREVIEW","STATUS=SIZE"],"use_expunge_all_on_delete":false,"fast_simple_search":true,"force_select":false,"message_all_headers":false,"message_list_limit":10000,"search_filter":""},"SMTP":{"host":"localhost","port":25,"type":0,"timeout":60,"shortLogin":true,"lowerLogin":true,"sasl":["SCRAM-SHA3-512","SCRAM-SHA-512","SCRAM-SHA-256","SCRAM-SHA-1","PLAIN","LOGIN"],"ssl":{"verify_peer":false,"verify_peer_name":false,"allow_self_signed":false,"SNI_enabled":true,"disable_compression":true,"security_level":1},"useAuth":false,"setSender":false,"usePhpMail":false},"Sieve":{"host":"localhost","port":4190,"type":0,"timeout":10,"shortLogin":false,"lowerLogin":true,"sasl":["SCRAM-SHA3-512","SCRAM-SHA-512","SCRAM-SHA-256","SCRAM-SHA-1","PLAIN","LOGIN"],"ssl":{"verify_peer":false,"verify_peer_name":false,"allow_self_signed":false,"SNI_enabled":true,"disable_compression":true,"security_level":1},"enabled":false},"whiteList":""}
SMCFG
    cp "$PANEL_DIR/public/snappymail/data/_data_/_default_/domains/default.json" \
       "$PANEL_DIR/public/snappymail/data/_data_/_default_/domains/_wildcard_.json" 2>/dev/null
    chown -R www-data:www-data "$PANEL_DIR/public/snappymail"
    log "SNAPPYMAIL" "install" "OK" "SnappyMail installed"
fi

# Create panel ports vhost config
log "APACHE" "panel-ports" "RUNNING" "Creating panel ports vhost"
cat > /etc/apache2/sites-available/panel-ports.conf <<VHOSTS
<VirtualHost *:2082>
    DocumentRoot $PANEL_DIR/public
    ServerName $SERVER_IP
    <Directory $PANEL_DIR/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    RewriteEngine On; RewriteRule ^/$ /portal_user.php [L]
</VirtualHost>
<VirtualHost *:2086>
    DocumentRoot $PANEL_DIR/public; ServerName $SERVER_IP
    <Directory $PANEL_DIR/public>
        Options Indexes FollowSymLinks; AllowOverride All; Require all granted
    </Directory>
    RewriteEngine On; RewriteRule ^/$ /portal_reseller.php [L]
</VirtualHost>
<VirtualHost *:2087>
    DocumentRoot $PANEL_DIR/public; ServerName $SERVER_IP
    <Directory $PANEL_DIR/public>
        Options Indexes FollowSymLinks; AllowOverride All; Require all granted
    </Directory>
    RewriteEngine On; RewriteRule ^/$ /admin/login [L,R=302]
</VirtualHost>
<VirtualHost *:2096>
    DocumentRoot $PANEL_DIR/public; ServerName $SERVER_IP
    <Directory $PANEL_DIR/public>
        Options Indexes FollowSymLinks; AllowOverride All; Require all granted
    </Directory>
    RewriteEngine On; RewriteRule ^/$ /portal_webmail.php [L]
</VirtualHost>
VHOSTS
a2ensite panel-ports 2>/dev/null || true
log "APACHE" "panel-ports" "OK" "Panel ports configured"

# 5. Panel files
echo "[5/8] Installing panel files..."
log "PANEL" "deploy" "RUNNING" "Copying panel files to $PANEL_DIR"
mkdir -p "$PANEL_DIR"
cp -r "$SCRIPT_DIR"/. "$PANEL_DIR"/ 2>/dev/null || true
rm -f "$PANEL_DIR/scripts/keygen.php" "$PANEL_DIR/config/license_private.pem" "$PANEL_DIR/install.sh" 2>/dev/null || true
chown -R www-data:www-data "$PANEL_DIR"
chmod -R 755 "$PANEL_DIR"
log "PANEL" "deploy" "OK" "Panel files deployed"

# 6. Apache vhost
echo "[6/8] Configuring Apache..."
log "APACHE" "vhost" "RUNNING" "Creating virtual host"
cat > /etc/apache2/sites-available/radiohosting.conf <<VHOST
<VirtualHost *:80>
    ServerAdmin webmaster@localhost
    DocumentRoot $PANEL_DIR/public
    <Directory $PANEL_DIR/public>
        Options Indexes FollowSymLinks; AllowOverride All; Require all granted
        DirectoryIndex index.php index.html
    </Directory>
    ErrorLog /var/log/apache2/radiohosting_error.log
    CustomLog /var/log/apache2/radiohosting_access.log combined
</VirtualHost>
VHOST
a2dissite 000-default 2>/dev/null || true
a2ensite radiohosting; a2enmod rewrite; a2enconf phpmyadmin
for port in 2082 2086 2087 2096; do
  grep -q "Listen $port" /etc/apache2/ports.conf || echo "Listen $port" >> /etc/apache2/ports.conf
done
log "APACHE" "vhost" "OK" "Virtual host created"

# Firewall ports
log "FIREWALL" "ports" "RUNNING" "Opening firewall ports"
firewall-cmd --permanent --add-port={5000/tcp,5001/tcp} 2>/dev/null || true
firewall-cmd --permanent --add-port=6000-10000/tcp 2>/dev/null || true
firewall-cmd --permanent --add-port=27000-28000/tcp 2>/dev/null || true
firewall-cmd --permanent --add-port=25560-25660/tcp 2>/dev/null || true
firewall-cmd --permanent --add-port=10000-20000/tcp 2>/dev/null || true
firewall-cmd --reload 2>/dev/null || true
iptables -I INPUT -p tcp --dport 5000 -j ACCEPT 2>/dev/null || true
iptables -I INPUT -p tcp --dport 5001 -j ACCEPT 2>/dev/null || true
iptables -I INPUT -p tcp --dport 6000:10000 -j ACCEPT 2>/dev/null || true
iptables -I INPUT -p tcp --dport 27000:28000 -j ACCEPT 2>/dev/null || true
iptables -I INPUT -p tcp --dport 25560:25660 -j ACCEPT 2>/dev/null || true
iptables -I INPUT -p tcp --dport 10000:20000 -j ACCEPT 2>/dev/null || true
log "FIREWALL" "ports" "OK" "Firewall ports opened"
systemctl restart apache2

# 7. Database
echo "[7/8] Configuring database..."
log "DATABASE" "setup" "RUNNING" "Creating database and user"
ADMIN_PASS="$(hostname)-$(openssl rand -base64 6 | tr -d '=+/')"
DB_PASS="$(openssl rand -base64 12)"

mysql -u root <<MYSQL || { log "DATABASE" "create" "FAIL" "Failed to create database"; rollback "database"; cleanup_and_exit 1; }
CREATE DATABASE IF NOT EXISTS radiohosting CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'radiouser'@'localhost' IDENTIFIED BY '$DB_PASS';
GRANT ALL PRIVILEGES ON radiohosting.* TO 'radiouser'@'localhost';
FLUSH PRIVILEGES;
MYSQL

# Import schemas
log "DATABASE" "schema" "RUNNING" "Importing schemas"
mysql -u root radiohosting < "$SCRIPT_DIR/database/install.sql" 2>/dev/null || true
for f in "$SCRIPT_DIR"/database/*.sql; do
  [ -f "$f" ] && mysql -u root radiohosting < "$f" 2>/dev/null || true
done
for s in "$SCRIPT_DIR"/plugins/*/database/schema.sql; do
  [ -f "$s" ] && mysql -u root radiohosting < "$s" 2>/dev/null || true
done
log "DATABASE" "schema" "OK" "Schemas imported"

# .env
cat > "$PANEL_DIR/.env" <<ENV
DB_HOST=localhost; DB_PORT=3306
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
file_put_contents('/etc/phpmyadmin/config.inc.php', str_replace(\$search, \$replace, \$c));
echo 'phpMyAdmin config set.\n';
"

mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED BY 'Skylinehosting171'; GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost' WITH GRANT OPTION; FLUSH PRIVILEGES;"
echo $(date +%s) > "$PANEL_DIR/.installed"
chmod 644 "$PANEL_DIR/.installed"

# Automation cron
echo "* * * * * php $PANEL_DIR/public/index.php /admin/automation/run >/dev/null 2>&1" > /etc/cron.d/planet-hosts-automation
chmod 644 /etc/cron.d/planet-hosts-automation
log "DATABASE" "setup" "OK" "Database configured"

# 8. License activation
echo "[8/8] License activation..."
log "LICENSE" "activate" "RUNNING" "License activation"
if [ -f "$SCRIPT_DIR/license.key" ]; then
    cp "$SCRIPT_DIR/license.key" "$PANEL_DIR/license.key"
    log "LICENSE" "activate" "OK" "License key installed"
else
    echo ""
    echo "=============================================="
    echo " LICENSE REQUIRED"
    echo "=============================================="
    echo " Include your server IP ($SERVER_IP) in the email."
    echo "=============================================="
    read -t 30 -p "Paste license key (or press Enter to skip): " LICENSE_CONTENT
    if [ -n "$LICENSE_CONTENT" ]; then
        echo "$LICENSE_CONTENT" > "$PANEL_DIR/license.key"
        log "LICENSE" "activate" "OK" "License key saved"
    else
        log "LICENSE" "activate" "SKIP" "License activation skipped"
    fi
fi
[ -f "$SCRIPT_DIR/config/license_public.pem" ] && cp "$SCRIPT_DIR/config/license_public.pem" "$PANEL_DIR/config/license_public.pem" 2>/dev/null || true

# Set admin user
log "ADMIN" "setup" "RUNNING" "Setting admin credentials"
php -r "
\$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4','radiouser','$DB_PASS');
\$hash = password_hash('$ADMIN_PASS', PASSWORD_DEFAULT);
\$pdo->exec(\"UPDATE admins SET username='root', password_hash='\$hash', email='root@planet-hosts.com' WHERE id=1\");
echo \"Admin set.\n\";
"
log "ADMIN" "setup" "OK" "Admin credentials set"

# 9. .NET 8 Support Server
echo "[9/9] Installing .NET 8 Support Server..."
log "DOTNET" "install" "RUNNING" "Installing .NET 8"
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
Restart=always; RestartSec=10; KillSignal=SIGINT
SyslogIdentifier=planet-support; User=root
Environment=ASPNETCORE_URLS=http://0.0.0.0:5000
Environment=ASPNETCORE_ENVIRONMENT=Production
[Install]
WantedBy=multi-user.target
SERVICEEOF
systemctl daemon-reload 2>/dev/null
systemctl enable planet-support 2>/dev/null
systemctl start planet-support 2>/dev/null || true
log "DOTNET" "install" "OK" ".NET Support Server installed on port 5000"

# 10. SteamCMD (duplicate guard)
echo "[10/10] Installing SteamCMD..."
log "STEAMCMD" "install" "RUNNING" "Verifying SteamCMD"
dpkg --add-architecture i386 2>/dev/null
apt-get update -qq 2>/dev/null
if ! command -v steamcmd >/dev/null 2>&1; then
  install_optional "SteamCMD" steamcmd 2>/dev/null || {
    mkdir -p /usr/games; cd /usr/games
    curl -sqL https://steamcdn-a.akamaihd.net/client/installer/steamcmd_linux.tar.gz | tar zxf - 2>/dev/null
    chmod +x steamcmd.sh; ln -sf /usr/games/steamcmd.sh /usr/games/steamcmd 2>/dev/null
  }
fi
log "STEAMCMD" "install" "OK" "SteamCMD ready"

# 11. Apache proxy for SignalR
echo "[11/11] Configuring Apache proxy for SignalR..."
log "APACHE" "signalr" "RUNNING" "Configuring SignalR proxy"
a2enmod proxy proxy_http proxy_wstunnel 2>/dev/null
printf 'ProxyPass /hub/ http://localhost:5000/hub/\nProxyPassReverse /hub/ http://localhost:5000/hub/\n' > /etc/apache2/conf-enabled/proxy-signalr.conf

# 12. DJ & Chat dedicated ports
echo "[12/12] Setting up DJ (2100) and Chat (2101) ports..."
log "APACHE" "extra-ports" "RUNNING" "Configuring DJ and Chat ports"
printf '<VirtualHost *:2100>\n    DocumentRoot /var/www/radiohosting/public\n    ServerName %s\n    <Directory /var/www/radiohosting/public>\n        Options Indexes FollowSymLinks\n        AllowOverride All\n        Require all granted\n    </Directory>\n</VirtualHost>\n' "$SERVER_IP" > /etc/apache2/sites-available/dj-panel.conf
printf '<VirtualHost *:2101>\n    DocumentRoot /var/www/radiohosting/public\n    ServerName %s\n    <Directory /var/www/radiohosting/public>\n        Options Indexes FollowSymLinks\n        AllowOverride All\n        Require all granted\n    </Directory>\n</VirtualHost>\n' "$SERVER_IP" > /etc/apache2/sites-available/chat-panel.conf
printf '\nListen 2100\nListen 2101\n' >> /etc/apache2/ports.conf
a2ensite dj-panel.conf chat-panel.conf 2>/dev/null
# Full firewalld configuration: allow all panel, web, mail, FTP, streaming ports
systemctl enable --now firewalld 2>/dev/null || true
if systemctl is-active firewalld >/dev/null 2>&1; then
    for port in 80/tcp 443/tcp 2082/tcp 2083/tcp 2086/tcp 2087/tcp 2096/tcp \
                2100/tcp 2101/tcp 21/tcp 22/tcp 25/tcp 465/tcp 587/tcp \
                110/tcp 143/tcp 993/tcp 995/tcp 8000/tcp 8080/tcp 8443/tcp; do
        firewall-cmd --add-port="$port" --permanent 2>/dev/null || true
    done
    firewall-cmd --reload 2>/dev/null || true
fi
systemctl reload apache2 2>/dev/null
log "APACHE" "extra-ports" "OK" "DJ/Chat ports configured"

# Copy theme into public
rm -rf "$PANEL_DIR/public/theme" 2>/dev/null
cp -r "$PANEL_DIR/theme" "$PANEL_DIR/public/theme" 2>/dev/null || true
cp -r "$PANEL_DIR/theme/themes" "$PANEL_DIR/public/theme/themes" 2>/dev/null || true
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
echo "Installed Components:"
echo "  Apache: $HTTPD_INSTALLED    MariaDB: $MARIADB_INSTALLED    PHP: $PHP_INSTALLED"
echo "  Firewalld: $FIREWALLD_INSTALLED    Icecast: $ICECAST_INSTALLED"
echo "  Liquidsoap: $LIQUIDSOAP_INSTALLED    ezstream: $EZSTREAM_INSTALLED"
echo "  FFmpeg: $FFMPEG_INSTALLED    phpMyAdmin: $PHPMYADMIN_INSTALLED"
echo ""
echo " Services: Apache, MariaDB, Postfix, Dovecot,"
echo "           VSFTPD, Bind9, Icecast2, Firewalld, Fail2ban,"
echo "           SHOUTcast DNAS (port 8000), nginx (port 8080),"
echo "           .NET 8 Support Server (port 5000)"

log "INSTALLER" "finish" "OK" "Installation complete"
cleanup_and_exit 0
