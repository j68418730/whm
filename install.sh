#!/bin/bash

# =========================================================
# Planet Hosts Master Panel - Unified Installer
# AlmaLinux 9 / RHEL 9 / RockyLinux 9
# =========================================================

set -eo pipefail

# =========================================================
# Variables
# =========================================================

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PANEL_DIR="/var/www/radiohosting"

HTTPD_INSTALLED=0
MARIADB_INSTALLED=0
PHP_INSTALLED=0
FIREWALLD_INSTALLED=0
ICECAST_INSTALLED=0
LIQUIDSOAP_INSTALLED=0
EZSTREAM_INSTALLED=0
FFMPEG_INSTALLED=0
PHPMYADMIN_INSTALLED=0

# =========================================================
# Package Manager
# =========================================================

PKG_MGR="yum"

if command -v dnf >/dev/null 2>&1; then
    PKG_MGR="dnf"
fi

# =========================================================
# Helper Functions
# =========================================================

get_server_ip() {

    PUBLIC_IP=$(curl -s --max-time 5 https://ifconfig.me/ip 2>/dev/null || \
    curl -s --max-time 5 https://icanhazip.com 2>/dev/null)

    if [ -n "$PUBLIC_IP" ] && [[ "$PUBLIC_IP" =~ ^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
        echo "$PUBLIC_IP"
        return
    fi

    LOCAL_IPS=$(hostname -I | tr ' ' '\n' | grep -v '^127\.' | head -1)

    if [ -n "$LOCAL_IPS" ]; then
        echo "$LOCAL_IPS"
        return
    fi

    echo "unknown"
}

install_optional_package() {

    local label="$1"
    shift

    echo "Installing $label..."

    if ! "$PKG_MGR" install -y "$@"; then
        echo "Warning: $label not available"
        return 1
    fi

    return 0
}

install_required_package() {

    local label="$1"
    shift

    echo "Installing $label..."

    "$PKG_MGR" install -y "$@" || {
        echo "Failed to install $label"
        exit 1
    }
}

# =========================================================
# Root Check
# =========================================================

if [ "$EUID" -ne 0 ]; then
    echo "Please run as root or sudo."
    exit 1
fi

# =========================================================
# Intro
# =========================================================

SERVER_IP=$(get_server_ip)

clear

echo "=================================================="
echo " Planet Hosts Master Panel Installer"
echo " AlmaLinux 9 / RHEL-like"
echo "=================================================="
echo ""
echo "Server IP: $SERVER_IP"
echo ""

# =========================================================
# Repository Setup
# =========================================================

echo "[1/12] Installing repositories..."

dnf install -y epel-release || true
dnf install -y dnf-plugins-core || true

dnf config-manager --set-enabled crb 2>/dev/null || \
dnf config-manager --set-enabled powertools 2>/dev/null || true

dnf install -y \
https://download1.rpmfusion.org/free/el/rpmfusion-free-release-9.noarch.rpm || true

dnf clean all || true
dnf makecache || true

# =========================================================
# System Update
# =========================================================

echo ""
echo "[2/12] Updating system..."

"$PKG_MGR" update -y

# =========================================================
# Firewall
# =========================================================

echo ""
echo "[3/12] Configuring firewall..."

install_required_package "firewalld" firewalld

systemctl enable --now firewalld

firewall-cmd --permanent --add-service=http || true
firewall-cmd --permanent --add-service=https || true
firewall-cmd --permanent --add-service=ssh || true
firewall-cmd --permanent --add-port=8000/tcp || true
firewall-cmd --permanent --add-port=8001/tcp || true
firewall-cmd --permanent --add-port=8080/tcp || true
firewall-cmd --reload || true

FIREWALLD_INSTALLED=1

# =========================================================
# Apache / PHP / MariaDB
# =========================================================

echo ""
echo "[4/12] Installing Apache, PHP, MariaDB..."

install_required_package "Web stack" \
httpd \
mariadb-server \
php \
php-cli \
php-common \
php-curl \
php-gd \
php-intl \
php-mbstring \
php-mysqlnd \
php-pdo \
php-process \
php-xml \
php-zip \
unzip

systemctl enable --now httpd
systemctl enable --now mariadb

HTTPD_INSTALLED=1
MARIADB_INSTALLED=1
PHP_INSTALLED=1

# =========================================================
# Icecast
# =========================================================

echo ""
echo "[5/12] Installing Icecast..."

ICECAST_FOUND=$(dnf search icecast 2>/dev/null | grep -i "icecast.x86_64" || true)

if [[ -n "$ICECAST_FOUND" ]]; then

    dnf install -y icecast
    ICECAST_INSTALLED=1

else

    echo "Icecast not available in repos."
    echo "Installing build dependencies..."

    dnf groupinstall -y "Development Tools"

    dnf install -y \
    pkgconf-pkg-config \
    glib2-devel \
    libxml2-devel \
    libxslt-devel \
    libshout-devel \
    libvorbis-devel \
    libtheora-devel \
    speex-devel \
    opus-devel \
    curl-devel \
    openssl-devel \
    sqlite-devel \
    autoconf-archive \
    m4 \
    gettext \
    gettext-devel \
    git \
    gcc \
    gcc-c++ \
    make \
    automake \
    autoconf \
    libtool || true

    echo ""
    echo "Icecast source dependencies installed."
    echo "Run manual source installer if needed."

fi

# =========================================================
# Liquidsoap / ezstream
# =========================================================

echo ""
echo "[6/12] Installing Liquidsoap and ezstream..."

install_optional_package "Liquidsoap" liquidsoap && LIQUIDSOAP_INSTALLED=1
install_optional_package "ezstream" ezstream && EZSTREAM_INSTALLED=1

# =========================================================
# FFmpeg
# =========================================================

echo ""
echo "[7/12] Installing FFmpeg..."

if dnf install -y ladspa rubberband ffmpeg ffmpeg-devel; then
    FFMPEG_INSTALLED=1
else
    dnf install -y --allowerasing --nobest \
    ladspa rubberband ffmpeg ffmpeg-devel || true
fi

# =========================================================
# phpMyAdmin
# =========================================================

echo ""
echo "[8/12] Installing phpMyAdmin..."

install_optional_package "phpMyAdmin" phpMyAdmin && PHPMYADMIN_INSTALLED=1

# =========================================================
# Panel Files
# =========================================================

echo ""
echo "[9/12] Installing panel files..."

mkdir -p "$PANEL_DIR"

cp -r "$SCRIPT_DIR"/* "$PANEL_DIR"/ || true

chown -R apache:apache "$PANEL_DIR" || true
chmod -R 755 "$PANEL_DIR" || true

# =========================================================
# Apache Virtual Host
# =========================================================

echo ""
echo "[10/12] Configuring Apache..."

cat > /etc/httpd/conf.d/radiohosting.conf <<EOF
<VirtualHost *:80>
    ServerAdmin webmaster@localhost
    DocumentRoot $PANEL_DIR/public
    ServerName radiohosting.local
    ServerAlias www.radiohosting.local localhost

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

if [ -f /etc/httpd/conf.d/welcome.conf ]; then
    mv /etc/httpd/conf.d/welcome.conf \
    /etc/httpd/conf.d/welcome.conf.disabled || true
fi

systemctl restart httpd

# =========================================================
# Database Setup
# =========================================================

echo ""
echo "[11/12] Configuring database..."

DB_PASSWORD=$(openssl rand -base64 12)
DB_NAME="radiohosting"
DB_USER="radiouser"

if mysqladmin -u root ping >/dev/null 2>&1; then
    MYSQL_ROOT_OPTS="-u root"
else
    echo "Enter MariaDB root password:"
    read -s MYSQL_ROOT_PASSWORD
    MYSQL_ROOT_OPTS="-u root -p$MYSQL_ROOT_PASSWORD"
fi

mysql $MYSQL_ROOT_OPTS -e \
"CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" || true

mysql $MYSQL_ROOT_OPTS -e \
"CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASSWORD';" || true

mysql $MYSQL_ROOT_OPTS -e \
"GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';" || true

mysql $MYSQL_ROOT_OPTS -e \
"FLUSH PRIVILEGES;" || true

# Create .env file with database credentials
echo "Creating .env file..."
cat > "$PANEL_DIR/.env" <<EOF
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=$DB_NAME
DB_USERNAME=$DB_USER
DB_PASSWORD=$DB_PASSWORD
EOF
chown apache:apache "$PANEL_DIR/.env" 2>/dev/null || true
chmod 600 "$PANEL_DIR/.env"
echo ".env file created."

# Import database schema
echo "Importing database schema..."
if [ -f "$SCRIPT_DIR/database/schema.sql" ]; then
    mysql $MYSQL_ROOT_OPTS "$DB_NAME" < "$SCRIPT_DIR/database/schema.sql" || true
    echo "Core schema imported."
fi

# Import plugin schemas
for schema in "$SCRIPT_DIR"/plugins/*/database/schema.sql; do
    if [ -f "$schema" ]; then
        mysql $MYSQL_ROOT_OPTS "$DB_NAME" < "$schema" || true
        echo "  Schema imported: $schema"
    fi
done

# =========================================================
# Cron Jobs
# =========================================================

echo ""
echo "[12/12] Creating cron jobs..."

mkdir -p "$PANEL_DIR/logs"

cat > /etc/cron.d/radiohosting <<EOF
*/5 * * * * apache php $PANEL_DIR/artisan radio:analytics >> $PANEL_DIR/logs/cron.log 2>&1
0 * * * * apache php $PANEL_DIR/artisan radio:restart-stopped-streams >> $PANEL_DIR/logs/cron.log 2>&1
EOF

chmod 644 /etc/cron.d/radiohosting

# =========================================================
# Generate License Key
# =========================================================

echo ""
echo "[13/12] Generating license key..."
chmod +x "$SCRIPT_DIR/keygen.sh"
"$SCRIPT_DIR/keygen.sh" --auto
# Copy license files to panel dir
if [ -f "$SCRIPT_DIR/license.key" ]; then
    cp "$SCRIPT_DIR/license.key" "$PANEL_DIR/license.key"
    echo "License key generated and deployed."
fi
if [ -f "$SCRIPT_DIR/config/license_public.pem" ]; then
    cp "$SCRIPT_DIR/config/license_public.pem" "$PANEL_DIR/config/license_public.pem"
fi

# =========================================================
# Final Output
# =========================================================

echo ""
echo "=================================================="
echo " Installation Complete"
echo "=================================================="
echo ""
echo "Panel Directory: $PANEL_DIR"
echo "Server IP: $SERVER_IP"
echo ""
echo "Database:"
echo "  Name: $DB_NAME"
echo "  User: $DB_USER"
echo "  Password: $DB_PASSWORD"
echo ""
echo "Installed Components:"
echo "  Apache: $HTTPD_INSTALLED"
echo "  MariaDB: $MARIADB_INSTALLED"
echo "  PHP: $PHP_INSTALLED"
echo "  Firewalld: $FIREWALLD_INSTALLED"
echo "  Icecast: $ICECAST_INSTALLED"
echo "  Liquidsoap: $LIQUIDSOAP_INSTALLED"
echo "  ezstream: $EZSTREAM_INSTALLED"
echo "  FFmpeg: $FFMPEG_INSTALLED"
echo "  phpMyAdmin: $PHPMYADMIN_INSTALLED"
echo ""
echo "Access panel:"
echo "http://$SERVER_IP/"
echo ""
echo "phpMyAdmin:"
echo "http://$SERVER_IP/phpmyadmin"
echo ""
