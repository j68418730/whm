#!/bin/bash
# Planet Hosts Master Panel Uninstaller
# This script removes the Planet Hosts Master Panel and associated configurations.
# It stops services, removes files, and optionally removes the database and user.

# Function to get server IP address (for display only)
get_server_ip() {
    PUBLIC_IP=$(curl -s --max-time 5 https://ifconfig.me/ip 2>/dev/null || curl -s --max-time 5 https://icanhazip.com 2>/dev/null)
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

echo "=== Planet Hosts Master Panel Uninstaller ==="
echo "This script will remove the Planet Hosts Master Panel installation."
echo "Server IP: $SERVER_IP"
echo ""
echo "WARNING: This will stop services and remove configuration files."
echo "The panel directory (/var/www/radiohosting) will be deleted."
echo "The database and user can be optionally removed."
echo ""
read -p "Are you sure you want to proceed? (y/N): " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Uninstallation cancelled."
    exit 1
fi

# Stop and disable services
echo "Stopping services..."
systemctl stop httpd 2>/dev/null || echo "Warning: Failed to stop httpd"
systemctl disable httpd 2>/dev/null || echo "Warning: Failed to disable httpd"
systemctl stop mariadb 2>/dev/null || echo "Warning: Failed to stop mariadb"
systemctl disable mariadb 2>/dev/null || echo "Warning: Failed to disable mariadb"
systemctl stop firewalld 2>/dev/null || echo "Warning: Failed to stop firewalld"
systemctl disable firewalld 2>/dev/null || echo "Warning: Failed to disable firewalld"

# Remove Apache virtual host
echo "Removing Apache virtual host..."
if [ -f /etc/httpd/conf.d/radiohosting.conf ]; then
    rm -f /etc/httpd/conf.d/radiohosting.conf
    echo "Removed /etc/httpd/conf.d/radiohosting.conf"
else
    echo "Apache virtual host file not found."
fi

# Remove cron job
echo "Removing cron job..."
if [ -f /etc/cron.d/radiohosting ]; then
    rm -f /etc/cron.d/radiohosting
    echo "Removed /etc/cron.d/radiohosting"
else
    echo "Cron job file not found."
fi

# Remove panel directory
echo "Removing panel directory..."
PANEL_DIR="/var/www/radiohosting"
if [ -d "$PANEL_DIR" ]; then
    rm -rf "$PANEL_DIR"
    echo "Removed $PANEL_DIR"
else
    echo "Panel directory not found."
fi

# Remove log directory
LOG_DIR="/var/log/radiohosting"
if [ -d "$LOG_DIR"]; then
    rm -rf "$LOG_DIR"
    echo "Removed $LOG_DIR"
else
    echo "Log directory not found."
fi

# Optionally remove database and user
echo ""
read -p "Do you want to remove the database and user as well? (y/N): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo "Removing database and user..."
    # Check if we can connect as root without password
    if mysqladmin -u root ping >/dev/null 2>&1; then
        MYSQL_ROOT_OPTS="-u root"
    else
        echo "Enter MariaDB root password:"
        read -s MYSQL_ROOT_PASSWORD
        MYSQL_ROOT_OPTS="-u root -p$MYSQL_ROOT_PASSWORD"
    fi
    DB_NAME="radiohosting"
    DB_USER="radiouser"
    mysql $MYSQL_ROOT_OPTS -e "DROP DATABASE IF EXISTS $DB_NAME;" && echo "Database $DB_NAME dropped."
    mysql $MYSQL_ROOT_OPTS -e "DROP USER IF EXISTS '$DB_USER'@'localhost';" && echo "User $DB_USER dropped."
    mysql $MYSQL_ROOT_OPTS -e "FLUSH PRIVILEGES;" && echo "Privileges flushed."
else
    echo "Skipping database and user removal."
fi

# Optionally remove firewall rules
echo ""
read -p "Do you want to remove firewall rules added by the installer? (y/N): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo "Removing firewall rules..."
    firewall-cmd --permanent --remove-service=http 2>/dev/null || echo "Warning: Could not remove HTTP service"
    firewall-cmd --permanent --remove-service=https 2>/dev/null || echo "Warning: Could not remove HTTPS service"
    firewall-cmd --permanent --remove-port=8000/tcp 2>/dev/null || echo "Warning: Could not remove port 8000/tcp"
    firewall-cmd --permanent --remove-port=8001/tcp 2>/dev/null || echo "Warning: Could not remove port 8001/tcp"
    firewall-cmd --permanent --remove-port=8080/tcp 2>/dev/null || echo "Warning: Could not remove port 8080/tcp"
    firewall-cmd --reload 2>/dev/null || echo "Warning: Could not reload firewalld"
    echo "Firewall rules removed."
else
    echo "Skipping firewall rule removal."
fi

echo ""
echo "=== Uninstallation Complete ==="
echo "The Planet Hosts Master Panel has been removed."
echo "Note: Packages installed by the installer (httpd, mariadb, php, etc.) are not removed."
echo "If you wish to remove them, you can do so manually with your package manager."
echo ""
echo "To remove packages, you might run:"
echo "   sudo yum remove httpd mariadb-server php php-mysqlnd php-cli php-curl php-gd php-mbstring php-xml php-zip unzip"
echo "   sudo yum remove firewalld"
echo "   sudo yum remove liquidsoap ezstream ffmpeg ffmpeg-devel phpMyAdmin"
echo ""
echo "=== End of Uninstallation ==="