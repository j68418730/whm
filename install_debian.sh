#!/bin/bash
# Planet Hosts - Complete Debian Installer
# Fully repeatable. Run on a fresh Debian 12 system.
set -e

if [ "$EUID" -ne 0 ]; then echo "Run as root."; exit 1; fi

SERVER_IP=$(curl -s --max-time 5 https://ifconfig.me/ip 2>/dev/null || echo "127.0.0.1")
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PANEL_DIR="/var/www/radiohosting"

echo "=============================================="
echo " Planet Hosts - Debian Installer"
echo " Server IP: $SERVER_IP"
echo "=============================================="

# Pre-seed Icecast passwords so it doesn't prompt
echo "icecast2 icecast2/icecast2 boolean true" | debconf-set-selections
echo "icecast2 icecast2/sourcepassword password $(hostname)" | debconf-set-selections
echo "icecast2 icecast2/relaypassword password $(hostname)" | debconf-set-selections
echo "icecast2 icecast2/adminpassword password $(hostname)" | debconf-set-selections
export DEBIAN_FRONTEND=noninteractive

# 1. System update
echo "[1/8] Updating system..."
apt update -qq && apt upgrade -y -qq

# 2. Apache + PHP + MariaDB
echo "[2/8] Installing Apache, PHP, MariaDB..."
apt install -y -qq apache2 mariadb-server php php-cli php-common php-curl \
  php-gd php-intl php-mbstring php-mysql php-xml php-zip php-bcmath php-bz2 \
  php-ctype php-exif php-fileinfo php-ftp php-imap php-ldap \
  php-opcache php-redis php-sockets php-tokenizer php-xmlreader \
  php-xsl php-apcu php-imagick postfix vsftpd bind9 unzip wget curl git openssl

systemctl enable --now apache2 mariadb postfix vsftpd named

# 3. Icecast + Liquidsoap + Ezstream + FFmpeg
echo "[3/8] Installing streaming stack..."
apt install -y -qq icecast2 liquidsoap ezstream-ffmpeg ffmpeg

# 4. phpMyAdmin
echo "[4/8] Installing phpMyAdmin..."
apt install -y -qq phpmyadmin

# 5. Panel files
echo "[5/8] Installing panel files..."
mkdir -p "$PANEL_DIR"
cp -r "$SCRIPT_DIR"/. "$PANEL_DIR"/ 2>/dev/null || true
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

# Import schemas
mysql -u root radiohosting < "$SCRIPT_DIR/database/schema.sql" 2>/dev/null || true
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

# Add username column if missing
mysql -u root radiohosting -e "ALTER TABLE admins ADD COLUMN username VARCHAR(50) DEFAULT '' AFTER id;" 2>/dev/null || true

# 8. License + admin user
echo "[8/8] Generating license..."
sed -i 's/\r$//' "$SCRIPT_DIR/keygen.sh" 2>/dev/null || true
cd "$SCRIPT_DIR" && bash keygen.sh --auto 2>/dev/null
[ -f "$SCRIPT_DIR/license.key" ] && cp "$SCRIPT_DIR/license.key" "$PANEL_DIR/license.key" 2>/dev/null || true
[ -f "$SCRIPT_DIR/config/license_public.pem" ] && cp "$SCRIPT_DIR/config/license_public.pem" "$PANEL_DIR/config/license_public.pem" 2>/dev/null || true

# Set admin: username=root, password=ADMIN_PASS
php -r "
\$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4','radiouser','$DB_PASS');
\$hash = password_hash('$ADMIN_PASS', PASSWORD_DEFAULT);
\$pdo->exec(\"UPDATE admins SET username='root', password_hash='\$hash', email='root@planet-hosts.com' WHERE id=1\");
echo \"Admin set.\n\";
"

# Copy theme into public
rm -rf "$PANEL_DIR/public/theme" 2>/dev/null; cp -r "$PANEL_DIR/theme" "$PANEL_DIR/public/theme" 2>/dev/null || true

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
echo " Streaming: Icecast2 Liquidsoap Ezstream FFmpeg"
echo ""
