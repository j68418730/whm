#!/bin/bash
# =========================================================
# Planet Hosts Master Panel - Installer
# AlmaLinux 9 / RHEL 9 / RockyLinux 9
# =========================================================
# This script installs repositories, packages, services,
# deploys panel files, creates the database, and calls
# license-activate.sh then setup.sh.

set -eo pipefail

# --- Variables ---
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PANEL_DIR="/var/www/radiohosting"
LOG_DIR="/var/log/planethosts"
INSTALLER_VERSION="1.0.0"
START_TIME=$(date +%s)

HTTPD_INSTALLED=0
MARIADB_INSTALLED=0
PHP_INSTALLED=0
FIREWALLD_INSTALLED=0
ICECAST_INSTALLED=0
LIQUIDSOAP_INSTALLED=0
EZSTREAM_INSTALLED=0
FFMPEG_INSTALLED=0
PHPMYADMIN_INSTALLED=0

PKG_MGR="yum"
if command -v dnf >/dev/null 2>&1; then
    PKG_MGR="dnf"
fi

# --- Logging ---
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
            rm -f /etc/httpd/conf.d/radiohosting.conf
            systemctl reload httpd 2>/dev/null || true
            log "ROLLBACK" "vhost" "OK" "Virtual host removed"
            ;;
        services)
            systemctl disable --now httpd 2>/dev/null || true
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

install_required() {
    local label="$1"; shift
    log "PACKAGES" "$label" "INSTALLING" "Installing $label..."
    "$PKG_MGR" install -y "$@" || {
        log "PACKAGES" "$label" "FAIL" "Failed to install $label"
        exit 1
    }
    log "PACKAGES" "$label" "OK" "Installed $label"
}

install_optional() {
    local label="$1"; shift
    log "PACKAGES" "$label" "INSTALLING" "Installing $label..."
    if ! "$PKG_MGR" install -y "$@"; then
        log "PACKAGES" "$label" "WARNING" "$label not available"
        return 1
    fi
    log "PACKAGES" "$label" "OK" "Installed $label"
    return 0
}

# --- Root Check ---
if [ "$EUID" -ne 0 ]; then
    echo "Please run as root or sudo."
    exit 1
fi

SERVER_IP=$(get_server_ip)

clear
echo "=================================================="
echo " Planet Hosts Master Panel Installer v$INSTALLER_VERSION"
echo " AlmaLinux 9 / RHEL-like"
echo "=================================================="
echo ""
echo "Server IP: $SERVER_IP"
echo ""
log "INSTALLER" "start" "OK" "Installer v$INSTALLER_VERSION started"

# --- Step 1: Repository Setup ---
echo ""
echo "[1/12] Installing repositories..."
log "REPOS" "setup" "RUNNING" "Configuring repositories"

"$PKG_MGR" install -y epel-release || true
"$PKG_MGR" install -y dnf-plugins-core || true
"$PKG_MGR" config-manager --set-enabled crb 2>/dev/null || \
"$PKG_MGR" config-manager --set-enabled powertools 2>/dev/null || true
"$PKG_MGR" install -y \
    https://download1.rpmfusion.org/free/el/rpmfusion-free-release-9.noarch.rpm || true
"$PKG_MGR" clean all || true
"$PKG_MGR" makecache || true

log "REPOS" "setup" "OK" "Repositories configured"

# --- Step 2: System Update ---
echo ""
echo "[2/12] Updating system..."
log "UPDATE" "system" "RUNNING" "Updating operating system"
"$PKG_MGR" update -y
log "UPDATE" "system" "OK" "System updated"

# --- Step 3: Firewall ---
echo ""
echo "[3/12] Configuring firewall..."
log "FIREWALL" "setup" "RUNNING" "Installing and configuring firewall"
install_required "firewalld" firewalld
systemctl enable --now firewalld
firewall-cmd --permanent --add-service=http || true
firewall-cmd --permanent --add-service=https || true
firewall-cmd --permanent --add-service=ssh || true
firewall-cmd --permanent --add-port=8000/tcp || true
firewall-cmd --permanent --add-port=8001/tcp || true
firewall-cmd --permanent --add-port=8080/tcp || true
firewall-cmd --reload || true
FIREWALLD_INSTALLED=1
log "FIREWALL" "setup" "OK" "Firewall configured"

# --- Step 4: Apache / PHP / MariaDB ---
echo ""
echo "[4/12] Installing Apache, PHP, MariaDB..."
log "STACK" "install" "RUNNING" "Installing web stack"
install_required "Web stack" \
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
    php-bcmath \
    php-bz2 \
    php-calendar \
    php-ctype \
    php-exif \
    php-fileinfo \
    php-ftp \
    php-gettext \
    php-imap \
    php-ldap \
    php-opcache \
    php-pear \
    php-redis \
    php-shmop \
    php-sockets \
    php-sodium \
    php-sysvmsg \
    php-sysvsem \
    php-sysvshm \
    php-tokenizer \
    php-wddx \
    php-xmlreader \
    php-xmlwriter \
    php-xsl \
    php-pecl-apcu \
    php-pecl-imagick \
    unzip

systemctl enable --now httpd
systemctl enable --now mariadb
HTTPD_INSTALLED=1
MARIADB_INSTALLED=1
PHP_INSTALLED=1
log "STACK" "install" "OK" "Web stack installed"

# --- Step 5: Icecast ---
echo ""
echo "[5/12] Installing Icecast..."
log "ICECAST" "install" "RUNNING" "Installing Icecast"
ICECAST_FOUND=$("$PKG_MGR" search icecast 2>/dev/null | grep -i "icecast.x86_64" || true)
if [[ -n "$ICECAST_FOUND" ]]; then
    "$PKG_MGR" install -y icecast
    ICECAST_INSTALLED=1
    log "ICECAST" "install" "OK" "Icecast installed from repo"
else
    log "ICECAST" "install" "WARNING" "Icecast not in repos, installing build deps"
    "$PKG_MGR" groupinstall -y "Development Tools"
    "$PKG_MGR" install -y \
        pkgconf-pkg-config glib2-devel libxml2-devel libxslt-devel \
        libshout-devel libvorbis-devel libtheora-devel speex-devel \
        opus-devel curl-devel openssl-devel sqlite-devel autoconf-archive \
        m4 gettext gettext-devel git gcc gcc-c++ make automake autoconf libtool || true
    log "ICECAST" "install" "OK" "Icecast build deps installed"
fi

# --- Step 6: Liquidsoap / ezstream ---
echo ""
echo "[6/12] Installing Liquidsoap and ezstream..."
log "MEDIA" "install" "RUNNING" "Installing Liquidsoap/ezstream"
install_optional "Liquidsoap" liquidsoap && LIQUIDSOAP_INSTALLED=1
install_optional "ezstream" ezstream && EZSTREAM_INSTALLED=1
log "MEDIA" "install" "OK" "Media tools processed"

# --- Step 7: FFmpeg ---
echo ""
echo "[7/12] Installing FFmpeg..."
log "FFMPEG" "install" "RUNNING" "Installing FFmpeg"
if "$PKG_MGR" install -y ladspa rubberband ffmpeg ffmpeg-devel; then
    FFMPEG_INSTALLED=1
else
    "$PKG_MGR" install -y --allowerasing --nobest ladspa rubberband ffmpeg ffmpeg-devel || true
fi
log "FFMPEG" "install" "OK" "FFmpeg processed"

# --- Step 8: phpMyAdmin ---
echo ""
echo "[8/12] Installing phpMyAdmin..."
log "PHPMYADMIN" "install" "RUNNING" "Installing phpMyAdmin"
install_optional "phpMyAdmin" phpMyAdmin && PHPMYADMIN_INSTALLED=1
log "PHPMYADMIN" "install" "OK" "phpMyAdmin processed"

# --- Step 9: Panel Files ---
echo ""
echo "[9/12] Deploying panel files..."
log "PANEL" "deploy" "RUNNING" "Copying panel files to $PANEL_DIR"
mkdir -p "$PANEL_DIR"
cp -r "$SCRIPT_DIR/.."/* "$PANEL_DIR"/ || true
chown -R apache:apache "$PANEL_DIR" || true
chmod -R 755 "$PANEL_DIR" || true
log "PANEL" "deploy" "OK" "Panel files deployed"

# --- Step 10: Apache Virtual Host ---
echo ""
echo "[10/12] Configuring Apache virtual host..."
log "APACHE" "vhost" "RUNNING" "Creating virtual host"
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
    mv /etc/httpd/conf.d/welcome.conf /etc/httpd/conf.d/welcome.conf.disabled || true
fi
systemctl restart httpd
log "APACHE" "vhost" "OK" "Virtual host created"

# --- Step 11: Database Setup ---
echo ""
echo "[11/12] Configuring database..."
log "DATABASE" "setup" "RUNNING" "Creating database and user"
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

mysql $MYSQL_ROOT_OPTS -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" || {
    log "DATABASE" "create" "FAIL" "Failed to create database"
    rollback "database"
    cleanup_and_exit 1
}

mysql $MYSQL_ROOT_OPTS -e "CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASSWORD';" || true
mysql $MYSQL_ROOT_OPTS -e "GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';" || true
mysql $MYSQL_ROOT_OPTS -e "FLUSH PRIVILEGES;" || true

cat > "$PANEL_DIR/.env" <<EOF
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=$DB_NAME
DB_USERNAME=$DB_USER
DB_PASSWORD=$DB_PASSWORD
APP_ENV=production
APP_DEBUG=false
APP_URL=http://$SERVER_IP
EOF
chown apache:apache "$PANEL_DIR/.env" 2>/dev/null || true
chmod 600 "$PANEL_DIR/.env"

if [ -f "$SCRIPT_DIR/../database/schema.sql" ]; then
    mysql $MYSQL_ROOT_OPTS "$DB_NAME" < "$SCRIPT_DIR/../database/schema.sql" || {
        log "DATABASE" "schema" "FAIL" "Failed to import schema"
        rollback "database"
        cleanup_and_exit 1
    }
    log "DATABASE" "schema" "OK" "Core schema imported"
fi

for schema in "$SCRIPT_DIR"/../plugins/*/database/schema.sql; do
    if [ -f "$schema" ]; then
        mysql $MYSQL_ROOT_OPTS "$DB_NAME" < "$schema" || true
        log "DATABASE" "plugin-schema" "OK" "Imported $(basename $(dirname $(dirname $schema)))"
    fi
done

log "DATABASE" "setup" "OK" "Database configured"

# --- Step 12: Cron Jobs ---
echo ""
echo "[12/12] Creating cron jobs..."
log "CRON" "setup" "RUNNING" "Creating cron jobs"
mkdir -p "$PANEL_DIR/logs"
cat > /etc/cron.d/radiohosting <<EOF
*/5 * * * * apache php $PANEL_DIR/artisan radio:analytics >> $PANEL_DIR/logs/cron.log 2>&1
0 * * * * apache php $PANEL_DIR/artisan radio:restart-stopped-streams >> $PANEL_DIR/logs/cron.log 2>&1
EOF
chmod 644 /etc/cron.d/radiohosting
log "CRON" "setup" "OK" "Cron jobs created"

# --- License Activation ---
echo ""
echo "------------------------------------------"
echo " License Activation Required"
echo "------------------------------------------"
log "LICENSE" "activate" "RUNNING" "Calling license-activate.sh"
if [ -f "$SCRIPT_DIR/license-activate.sh" ]; then
    bash "$SCRIPT_DIR/license-activate.sh" || {
        log "LICENSE" "activate" "FAIL" "License activation failed"
        echo "License activation failed. Run license-activate.sh manually to retry."
        cleanup_and_exit 1
    }
else
    log "LICENSE" "activate" "FAIL" "license-activate.sh not found"
    echo "ERROR: license-activate.sh not found in installer directory."
    cleanup_and_exit 1
fi
log "LICENSE" "activate" "OK" "License activated"

# --- Setup ---
echo ""
echo "------------------------------------------"
echo " Running Initial Setup"
echo "------------------------------------------"
log "SETUP" "run" "RUNNING" "Calling setup.sh"
if [ -f "$SCRIPT_DIR/setup.sh" ]; then
    bash "$SCRIPT_DIR/setup.sh" || {
        log "SETUP" "run" "FAIL" "Setup failed"
        echo "Setup failed. Run setup.sh manually to retry."
        cleanup_and_exit 1
    }
else
    log "SETUP" "run" "FAIL" "setup.sh not found"
    echo "ERROR: setup.sh not found."
    cleanup_and_exit 1
fi
log "SETUP" "run" "OK" "Setup completed"

# --- Final Output ---
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
echo "Access panel: http://$SERVER_IP/"
echo "phpMyAdmin: http://$SERVER_IP/phpmyadmin"
echo ""

log "INSTALLER" "finish" "OK" "Installation complete"
cleanup_and_exit 0
