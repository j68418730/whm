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
echo "[1/8] Updating system..."
apt update -qq && apt upgrade -y -qq

# 2. Full LAMP + services
echo "[2/8] Installing Apache, PHP, MariaDB, and services..."
apt install -y -qq apache2 mariadb-server \
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

# 4. phpMyAdmin + ModSecurity
echo "[4/8] Installing phpMyAdmin and ModSecurity..."
apt install -y -qq phpmyadmin libapache2-mod-security2

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

# Copy theme into public
rm -rf "$PANEL_DIR/public/theme" 2>/dev/null
cp -r "$PANEL_DIR/theme" "$PANEL_DIR/public/theme" 2>/dev/null || true

echo ""
echo "=============================================="
echo " Installation Complete"
echo "=============================================="
echo " Panel: http://$SERVER_IP/"
echo " phpMyAdmin: http://$SERVER_IP/phpmyadmin"
echo ""
echo " Admin Login: root"
echo " Admin Password: $ADMIN_PASS"
echo " DB Password: $DB_PASS"
echo ""
echo " Services: Apache, MariaDB, Postfix, Dovecot,"
echo "           VSFTPD, Bind9, Icecast2, Firewalld, Fail2ban"
echo ""
