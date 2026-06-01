#!/bin/bash

# =========================================================
# Planet Hosts Master Panel FULL Uninstaller
# AlmaLinux / RockyLinux / CentOS / RHEL
# =========================================================

set -eo pipefail

# =========================================================
# Get Server IP
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

SERVER_IP=$(get_server_ip)

clear

echo "=================================================="
echo " Planet Hosts Master Panel FULL Uninstaller"
echo "=================================================="
echo ""
echo "Server IP: $SERVER_IP"
echo ""
echo "WARNING:"
echo "This script will:"
echo ""
echo " - Stop services"
echo " - Remove panel files"
echo " - Remove Apache configs"
echo " - Remove database"
echo " - Remove firewall rules"
echo " - Remove installed packages"
echo " - Remove development tools"
echo " - Remove ffmpeg/liquidsoap/icecast"
echo " - Remove repositories"
echo ""
echo "THIS CANNOT BE UNDONE."
echo ""

read -p "Continue? (y/N): " -n 1 -r
echo

if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Cancelled."
    exit 1
fi

# =========================================================
# Stop Services
# =========================================================

echo ""
echo "Stopping services..."

SERVICES=(
    httpd
    mariadb
    firewalld
    xrdp
    sshd
)

for service in "${SERVICES[@]}"; do

    if systemctl list-unit-files | grep -q "$service"; then

        systemctl stop "$service" 2>/dev/null || true
        systemctl disable "$service" 2>/dev/null || true

        echo "Stopped: $service"

    fi

done

# =========================================================
# Remove Apache Configs
# =========================================================

echo ""
echo "Removing Apache configs..."

rm -f /etc/httpd/conf.d/radiohosting.conf

# =========================================================
# Remove Cron Jobs
# =========================================================

echo ""
echo "Removing cron jobs..."

rm -f /etc/cron.d/radiohosting

# =========================================================
# Remove Panel Files
# =========================================================

echo ""
echo "Removing panel files..."

rm -rf /var/www/radiohosting
rm -rf /tmp/radiohosting_panel
rm -rf /tmp/whm

# =========================================================
# Remove Logs
# =========================================================

echo ""
echo "Removing logs..."

rm -rf /var/log/radiohosting

# =========================================================
# Remove Database
# =========================================================

echo ""
read -p "Remove MariaDB database and user? (y/N): " -n 1 -r
echo

if [[ $REPLY =~ ^[Yy]$ ]]; then

    DB_NAME="radiohosting"
    DB_USER="radiouser"

    echo ""
    echo "Removing database..."

    if mysqladmin -u root ping >/dev/null 2>&1; then

        MYSQL_ROOT_OPTS="-u root"

    else

        echo ""
        read -s -p "Enter MariaDB root password: " MYSQL_ROOT_PASSWORD
        echo ""

        MYSQL_ROOT_OPTS="-u root -p$MYSQL_ROOT_PASSWORD"

    fi

    mysql $MYSQL_ROOT_OPTS -e "DROP DATABASE IF EXISTS $DB_NAME;" || true
    mysql $MYSQL_ROOT_OPTS -e "DROP USER IF EXISTS '$DB_USER'@'localhost';" || true
    mysql $MYSQL_ROOT_OPTS -e "FLUSH PRIVILEGES;" || true

    echo "Database removed."

else

    echo "Skipping database removal."

fi

# =========================================================
# Remove Firewall Rules
# =========================================================

echo ""
echo "Removing firewall rules..."

firewall-cmd --permanent --remove-service=http 2>/dev/null || true
firewall-cmd --permanent --remove-service=https 2>/dev/null || true
firewall-cmd --permanent --remove-port=8000/tcp 2>/dev/null || true
firewall-cmd --permanent --remove-port=8001/tcp 2>/dev/null || true
firewall-cmd --permanent --remove-port=8080/tcp 2>/dev/null || true
firewall-cmd --reload 2>/dev/null || true

# =========================================================
# Remove Packages
# =========================================================

echo ""
echo "Removing packages..."

yum remove -y \
httpd \
httpd-tools \
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
phpMyAdmin \
mariadb \
mariadb-server \
mariadb-backup \
ffmpeg \
ffmpeg-devel \
ffmpeg-libs \
liquidsoap \
ezstream \
icecast \
wget \
curl \
git \
nano \
unzip \
net-tools \
bash-completion \
hyperv-daemons \
hyperv-tools \
qemu-guest-agent \
cloud-utils-growpart \
gdisk \
xrdp || true

# =========================================================
# Remove Development Tools
# =========================================================

echo ""
echo "Removing development tools..."

yum groupremove -y "Development Tools" || true

# =========================================================
# Remove Repositories
# =========================================================

echo ""
echo "Removing repositories..."

yum remove -y epel-release || true

rm -f /etc/yum.repos.d/rpmfusion-*.repo

# =========================================================
# Cleanup
# =========================================================

echo ""
echo "Cleaning package cache..."

yum autoremove -y || true
yum clean all || true

# =========================================================
# Optional Full Web Root Cleanup
# =========================================================

echo ""
read -p "Remove ALL web files in /var/www/html ? (y/N): " -n 1 -r
echo

if [[ $REPLY =~ ^[Yy]$ ]]; then

    rm -rf /var/www/html/*

    echo "Web root cleaned."

fi

# =========================================================
# Remove rc.local Entries
# =========================================================

echo ""
echo "Cleaning rc.local..."

if [ -f /etc/rc.d/rc.local ]; then

    sed -i '/radiohosting/d' /etc/rc.d/rc.local || true

fi

# =========================================================
# Final
# =========================================================

echo ""
echo "=================================================="
echo " Uninstallation Complete"
echo "=================================================="
echo ""
echo "Planet Hosts Master Panel has been removed."
echo ""
echo "Recommended:"
echo " - reboot server"
echo " - verify Apache removed"
echo " - verify MariaDB removed"
echo " - verify firewall rules removed"
echo ""
echo "Reboot with:"
echo ""
echo "reboot"
echo ""
