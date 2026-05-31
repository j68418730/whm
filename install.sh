#!/bin/bash
# Radio Hosting Panel Installer
# This script installs the radio hosting panel as a core system service
# For RHEL/CentOS/Fedora systems (yum/dnf)

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PKG_MGR="yum"
if command -v dnf >/dev/null 2>&1; then
    PKG_MGR="dnf"
fi

# Function to get server IP address
get_server_ip() {
    # Try to get public IP from external service
    PUBLIC_IP=$(curl -s --max-time 5 https://ifconfig.me/ip 2>/dev/null || curl -s --max-time 5 https://icanhazip.com 2>/dev/null)
    
    if [ -n "$PUBLIC_IP" ] && [[ "$PUBLIC_IP" =~ ^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
        echo "$PUBLIC_IP"
        return
    fi
    
    # Fallback to local IP addresses
    LOCAL_IPS=$(hostname -I | tr ' ' '\n' | grep -v '^127\.' | head -1)
    if [ -n "$LOCAL_IPS" ]; then
        echo "$LOCAL_IPS"
        return
    fi
    
    # Last resort
    echo "your_server_ip"
}

install_optional_package() {
    local label="$1"
    shift

    echo "Installing $label..."
    if ! "$PKG_MGR" install -y "$@"; then
        echo "Warning: $label not available from enabled repositories"
        return 1
    fi

    return 0
}

install_required_package() {
    local label="$1"
    shift

    echo "Installing $label..."
    "$PKG_MGR" install -y "$@" || { echo "Failed to install $label"; exit 1; }
}

setup_repositories() {
    echo "Setting up repositories..."

    if [ "$PKG_MGR" = "dnf" ]; then
        dnf install -y dnf-plugins-core || echo "Warning: Failed to install dnf plugins"

        if [ -f /etc/os-release ]; then
            . /etc/os-release
        fi

        if [ "${ID:-}" = "fedora" ]; then
            dnf install -y \
                "https://download1.rpmfusion.org/free/fedora/rpmfusion-free-release-$(rpm -E %fedora).noarch.rpm" \
                || echo "Warning: Failed to install RPM Fusion for Fedora"
        else
            /usr/bin/crb enable 2>/dev/null || dnf config-manager --set-enabled crb 2>/dev/null || dnf config-manager --set-enabled powertools 2>/dev/null || true
            dnf install -y epel-release || echo "Warning: Failed to install epel-release"
            dnf install -y \
                "https://download1.rpmfusion.org/free/el/rpmfusion-free-release-$(rpm -E %rhel).noarch.rpm" \
                || echo "Warning: Failed to install RPM Fusion for Enterprise Linux"
        fi

        dnf clean all || true
        dnf makecache || true
    else
        yum install -y epel-release || echo "Warning: Failed to install epel-release"
    fi
}

install_icecast_from_source() {
    local source_installer="$SCRIPT_DIR/scripts/icecast_install_source.sh"

    if [ ! -f "$source_installer" ]; then
        echo "Warning: Icecast source installer not found at $source_installer"
        return 1
    fi

    echo "Installing Icecast from source because no repository package was available..."
    bash "$source_installer"
}

install_ffmpeg_stack() {
    echo "Installing ffmpeg..."

    "$PKG_MGR" install -y ladspa rubberband ffmpeg ffmpeg-devel && return 0

    echo "Initial ffmpeg install failed; retrying with Enterprise Linux resolver options..."
    if [ "$PKG_MGR" = "dnf" ]; then
        dnf install -y --allowerasing --nobest ladspa rubberband ffmpeg ffmpeg-devel && return 0
        dnf install -y --allowerasing --nobest ladspa rubberband ffmpeg && return 0
    else
        yum install -y ladspa rubberband ffmpeg && return 0
    fi

    echo "Failed to install ffmpeg after retrying repository dependency fixes"
    exit 1
}

echo "=== Radio Hosting Panel Installer ==="
echo "This script will install the panel and required dependencies."
echo "It must be run as root or with sudo."
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then
  echo "Please run as root (use sudo)."
  exit 1
fi

# ===== REPOSITORY SETUP - MUST RUN FIRST =====
setup_repositories

# Update system
echo "Updating system packages..."
"$PKG_MGR" update -y

# Set up firewall (firewalld)
echo "Setting up firewall with firewalld..."
install_required_package "firewalld" firewalld
FIREWALLD_INSTALLED=1
systemctl start firewalld || { echo "Failed to start firewalld"; exit 1; }
systemctl enable firewalld || { echo "Failed to enable firewalld"; exit 1; }

# Open required ports for radio hosting panel
firewall-cmd --permanent --add-service=http || echo "Warning: Could not add HTTP service"
firewall-cmd --permanent --add-service=https || echo "Warning: Could not add HTTPS service"
firewall-cmd --permanent --add-port=8000/tcp  # Icecast HTTP
firewall-cmd --permanent --add-port=8001/tcp  # Icecast HTTPS (if used)
firewall-cmd --permanent --add-port=8080/tcp  # Common alternative for web panels
firewall-cmd --reload || { echo "Warning: Could not reload firewalld"; }

# Install required packages
echo "Installing Apache, MariaDB, PHP, and dependencies..."
install_required_package "base packages" httpd mariadb-server php php-mysqlnd php-cli php-curl php-gd php-mbstring php-xml php-zip unzip
   HTTPD_INSTALLED=1
   MARIADB_INSTALLED=1
   PHP_INSTALLED=1

# Install Icecast (removed Shoutcast per user request)
echo "Installing Icecast..."
# Install icecast from repos first, then compile from the bundled source installer if needed.
ICECAST_INSTALLED=0
if "$PKG_MGR" install -y icecast; then
    echo "Icecast installed successfully from repositories."
    ICECAST_INSTALLED=1
elif install_icecast_from_source; then
    echo "Icecast installed successfully from source."
    ICECAST_INSTALLED=1
else
    echo ""
    echo "===== WARNING: ICECAST NOT INSTALLED ====="
    echo "Icecast was not found in your repositories and the source installer failed."
    echo ""
    echo "The installer will continue with all other components."
    echo "You will need to manually install Icecast after this script completes."
    echo "See the 'MANUAL ICECAST INSTALLATION' section at the end of this script."
    echo "=========================================="
    echo ""
fi

# Install Liquidsoap for advanced automation (modern stack)
LIQUIDSOAP_INSTALLED=0
install_optional_package "Liquidsoap" liquidsoap && LIQUIDSOAP_INSTALLED=1

# Install ezstream for AutoDJ (kept for compatibility; can be replaced by Liquidsoap in future)
EZSTREAM_INSTALLED=0
install_optional_package "ezstream" ezstream && EZSTREAM_INSTALLED=1

# Install ffmpeg for transcoding
FFMPEG_INSTALLED=0
install_ffmpeg_stack
FFMPEG_INSTALLED=1

# Install phpMyAdmin
echo "Installing phpMyAdmin..."
install_optional_package "phpMyAdmin" phpMyAdmin

# Enable Apache modules (httpd)
echo "Enabling Apache modules..."
# For httpd, rewrite and headers modules are usually available by default
# We'll ensure they're enabled in the config if needed
# No explicit enabling needed for most modules in httpd

# Set up directory for our panel
PANEL_DIR="/var/www/radiohosting"
echo "Setting up panel directory at $PANEL_DIR..."
mkdir -p $PANEL_DIR || { echo "Failed to create panel directory"; exit 1; }

# Copy the panel files
SOURCE_DIR="/tmp/radiohosting_panel/whm"

if [ -d "$SOURCE_DIR" ]; then
  echo "Copying panel files from $SOURCE_DIR to $PANEL_DIR..."
  cp -r $SOURCE_DIR/* $PANEL_DIR/ || { echo "Failed to copy panel files"; exit 1; }
else
  echo "Source directory $SOURCE_DIR not found."
  echo "Please ensure the panel code is available at $SOURCE_DIR"
  echo "Exiting."
  exit 1
fi

# Set permissions
echo "Setting permissions..."
chown -R apache:apache $PANEL_DIR || { echo "Warning: Could not set ownership"; }
chmod -R 755 $PANEL_DIR || { echo "Warning: Could not set permissions"; }
find $PANEL_DIR -type d -exec chmod 755 {} \; || { echo "Warning: Could not set directory permissions"; }
find $PANEL_DIR -type f -exec chmod 644 {} \; || { echo "Warning: Could not set file permissions"; }

# Set up Apache virtual host
echo "Setting up Apache virtual host..."
VHOST_FILE="/etc/httpd/conf.d/radiohosting.conf"

cat > $VHOST_FILE <<EOF
<VirtualHost *:80>
    ServerAdmin webmaster@localhost
    DocumentRoot $PANEL_DIR
    ServerName radiohosting.local
    ServerAlias www.radiohosting.local

    <Directory $PANEL_DIR>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog /var/log/httpd/radiohosting_error.log
    CustomLog /var/log/httpd/radiohosting_access.log combined
</VirtualHost>
EOF

# Disable the default site (by renaming or overriding)
# For httpd, we'll just make sure our config is loaded
# The default welcome page is in /etc/httpd/conf.d/welcome.conf
# We can disable it by renaming or overriding
if [ -f /etc/httpd/conf.d/welcome.conf ]; then
    mv /etc/httpd/conf.d/welcome.conf /etc/httpd/conf.d/welcome.conf.disabled || { echo "Warning: Could not disable welcome.conf"; }
fi

# Restart Apache
echo "Restarting Apache..."
systemctl restart httpd || { echo "Failed to restart Apache"; exit 1; }
systemctl enable httpd || { echo "Failed to enable Apache"; exit 1; }

# Set up MariaDB database and user
echo "Setting up MariaDB database..."
# Start and enable MariaDB
systemctl start mariadb || { echo "Failed to start MariaDB"; exit 1; }
systemctl enable mariadb || { echo "Failed to enable MariaDB"; exit 1; }

# Generate a random password for the database user
DB_PASSWORD=$(openssl rand -base64 12)
DB_NAME="radiohosting"
DB_USER="radiouser"

# Secure MariaDB installation (set root password if not set)
# We'll check if we can connect as root without password
if mysqladmin -u root ping >/dev/null 2>&1; then
  MYSQL_ROOT_OPTS="-u root"
else
  echo "Cannot connect to MariaDB as root without password."
  echo "Please enter the MariaDB root password:"
  read -s MYSQL_ROOT_PASSWORD
  MYSQL_ROOT_OPTS="-u root -p$MYSQL_ROOT_PASSWORD"
fi

# Create database and user
echo "Creating database and user..."
mysql $MYSQL_ROOT_OPTS -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" || { echo "Failed to create database"; exit 1; }
mysql $MYSQL_ROOT_OPTS -e "CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASSWORD';" || { echo "Failed to create user"; exit 1; }
mysql $MYSQL_ROOT_OPTS -e "GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';" || { echo "Failed to grant privileges"; exit 1; }
mysql $MYSQL_ROOT_OPTS -e "FLUSH PRIVILEGES;" || { echo "Failed to flush privileges"; exit 1; }

# Save the database credentials to a configuration file
echo "Saving database configuration..."
CONFIG_FILE="$PANEL_DIR/config/database.php"
mkdir -p $(dirname $CONFIG_FILE) || { echo "Failed to create config directory"; exit 1; }
cat > $CONFIG_FILE <<EOF
<?php
return [
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '3306'),
    'database' => env('DB_DATABASE', '$DB_NAME'),
    'username' => env('DB_USERNAME', '$DB_USER'),
    'password' => env('DB_PASSWORD', '$DB_PASSWORD'),
    'charset' => env('DB_CHARSET', 'utf8mb4'),
];
EOF

# Set permissions on the config file
chown apache:apache $CONFIG_FILE || { echo "Warning: Could not set config ownership"; }
chmod 640 $CONFIG_FILE || { echo "Warning: Could not set config permissions"; }

# Initialize the database schema
# We assume there is a schema.sql file in the panel code.
SCHEMA_FILE="$PANEL_DIR/database/schema.sql"
if [ -f "$SCHEMA_FILE" ]; then
  echo "Importing database schema..."
  mysql $MYSQL_ROOT_OPTS $DB_NAME < $SCHEMA_FILE || { echo "Warning: Failed to import schema"; }
else
  echo "Schema file not found at $SCHEMA_FILE. Skipping database schema import."
fi

# Enable the radio hosting service in the configuration (by default)
echo "Enabling radio hosting in configuration..."
CONFIG_FILE="$PANEL_DIR/config/radio.php"
if [ -f "$CONFIG_FILE" ]; then
  # We'll ensure global_enabled is true
  sed -i "s/'global_enabled' => false/'global_enabled' => true/" $CONFIG_FILE || { echo "Warning: Could not update radio config"; }
else
  echo "Radio config file not found. Skipping."
fi

# Set up cron jobs for listener analytics and other periodic tasks
echo "Setting up cron jobs..."
CRON_FILE="/etc/cron.d/radiohosting"
cat > $CRON_FILE <<EOF
# Radio Hosting Panel Cron Jobs
# Run listener analytics every 5 minutes
*/5 * * * * apache php $PANEL_DIR/artisan radio:analytics >> $PANEL_DIR/logs/cron.log 2>&1

# Check for stopped streams and restart them if needed (example)
0 * * * * apache php $PANEL_DIR/artisan radio:restart-stopped-streams >> $PANEL_DIR/logs/cron.log 2>&1
EOF
chmod 644 $CRON_FILE || { echo "Warning: Could not set cron file permissions"; }

# Create log directory and set permissions
mkdir -p $PANEL_DIR/logs || { echo "Failed to create logs directory"; exit 1; }
chown -R apache:apache $PANEL_DIR/logs || { echo "Warning: Could not set logs ownership"; }
chmod -R 755 $PANEL_DIR/logs || { echo "Warning: Could not set logs permissions"; }

# Get server IP for display
SERVER_IP=$(get_server_ip)

# Initialize installation tracking variables
HTTPD_INSTALLED=0
MARIADB_INSTALLED=0
PHP_INSTALLED=0
FIREWALLD_INSTALLED=0
ICECAST_INSTALLED=0
LIQUIDSOAP_INSTALLED=0
EZSTREAM_INSTALLED=0
FFMPEG_INSTALLED=0
PHPMYADMIN_INSTALLED=0

# Initialize installation tracking variables
HTTPD_INSTALLED=0
MARIADB_INSTALLED=0
PHP_INSTALLED=0
FIREWALLD_INSTALLED=0
ICECAST_INSTALLED=0
LIQUIDSOAP_INSTALLED=0
EZSTREAM_INSTALLED=0
FFMPEG_INSTALLED=0
PHPMYADMIN_INSTALLED=0

# Final instructions
echo ""
echo "=== Installation Complete ==="
echo ""
echo "Panel installed at: $PANEL_DIR"
echo "Database name: $DB_NAME"
echo "Database user: $DB_USER"
echo "Database password: $DB_PASSWORD"
echo ""
echo "Please note the database credentials above. You will need them for the panel configuration."
echo ""
echo "Next steps:"
echo "1. Transfer the panel code to the server if you haven't already (to /tmp/radiohosting_panel/whm)."
echo "2. Run this installer again to copy the files and complete the setup."
echo "3. After installation, visit http://$SERVER_IP/ in a web browser to access the panel."
echo "4. Log in with the system user 'radiopanel' and the password shown above."
echo "   IMPORTANT: Change the password after first login using 'passwd radiopanel' and then update the hash file."
echo "   To update the hash file after changing the password, run:"
echo "   sudo /path/to/whm/update_panel_hash.sh"
echo ""
echo "Firewall configured: firewalld is active with ports 80 (HTTP), 443 (HTTPS), 8000 (Icecast HTTP), 8001 (Icecast HTTPS), and 8080 (alternative web panel) open."
echo ""
echo "=== INSTALLATION STATUS ==="
echo "The following components have been processed:"
echo "  [${HTTPD_INSTALLED:-0}] Apache Web Server (httpd)"
echo "  [${MARIADB_INSTALLED:-0}] MariaDB Database Server"
echo "  [${PHP_INSTALLED:-0}] PHP and Extensions"
echo "  [${FIREWALLD_INSTALLED:-0}] FirewallD (firewalld)"
echo "  [${ICECAST_INSTALLED:-0}] Icecast Streaming Server"
echo "  [${LIQUIDSOAP_INSTALLED:-0}] Liquidsoap Automation Engine"
echo "  [${EZSTREAM_INSTALLED:-0}] ezstream AutoDJ Tool"
echo "  [${FFMPEG_INSTALLED:-0}] FFmpeg Transcoding Suite"
echo "  [${PHPMYADMIN_INSTALLED:-0}] phpMyAdmin Web Interface"
echo ""
echo "Note: [1] indicates installed/configured, [0] indicates not installed or optional component not available."
echo ""
echo ""
echo "Radio service install status:"
if [ "$ICECAST_INSTALLED" -eq 1 ]; then
    echo " - Icecast: installed"
else
    echo " - Icecast: not installed"
fi
if [ "$LIQUIDSOAP_INSTALLED" -eq 1 ]; then
    echo " - Liquidsoap: installed"
else
    echo " - Liquidsoap: not available from enabled repositories"
fi
if [ "$EZSTREAM_INSTALLED" -eq 1 ]; then
    echo " - ezstream: installed"
else
    echo " - ezstream: not available from enabled repositories"
fi
if [ "$FFMPEG_INSTALLED" -eq 1 ]; then
    echo " - ffmpeg: installed"
fi
echo ""
echo "Note: Shoutcast was removed per user request; Liquidsoap is used for automation when available."
echo ""
echo "==== IMPORTANT NOTE ABOUT ICECAST ===="
if [ "$ICECAST_INSTALLED" -eq 1 ]; then
    echo "Icecast is installed and ready for radio streaming."
else
    echo "Icecast could not be installed automatically."
    echo "This can happen on some RHEL/CentOS/Fedora versions where Icecast isn't in the default/EPEL repos."
    echo ""
    echo "To enable radio streaming features, manually run the bundled source installer:"
    echo "   sudo bash $PANEL_DIR/scripts/icecast_install_source.sh"
    echo ""
    echo "The panel will continue to work for all other hosting features while you set up Icecast manually."
fi
echo "================================"
echo ""
echo "To access phpMyAdmin, visit http://$SERVER_IP/phpmyadmin"
echo ""
echo "=== End of Installation ==="
