#!/bin/bash
# Radio Hosting Panel Installer
# This script installs the radio hosting panel as a core system service
# For RHEL/CentOS/Fedora systems (yum/dnf)

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
echo "Setting up repositories..."
dnf install dnf-plugins-core -y
/usr/bin/crb enable
dnf install epel-release -y
dnf install -y https://download1.rpmfusion.org/free/el/rpmfusion-free-release-9.noarch.rpm
dnf clean all
dnf makecache

# Update system
echo "Updating system packages..."
yum update -y

# Set up firewall (firewalld)
echo "Setting up firewall with firewalld..."
yum install -y firewalld || { echo "Failed to install firewalld"; exit 1; }
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
yum install -y httpd mariadb-server php php-mysqlnd php-cli php-curl php-gd php-mbstring php-xml php-zip unzip || { echo "Failed to install base packages"; exit 1; }

# Install Icecast (removed Shoutcast per user request)
echo "Installing Icecast..."
# Enable EPEL repository for additional packages
yum install -y epel-release || { echo "Warning: Failed to install epel-release"; }
# Install icecast, but continue if it fails (may not be available in all repos)
if ! yum install -y icecast -y; then
    echo ""
    echo "===== WARNING: ICECAST NOT INSTALLED VIA YUM ====="
    echo "The Icecast package was not found in your repositories."
    echo "This can happen on some RHEL/CentOS/Fedora versions."
    echo ""
    echo "The installer will continue with all other components."
    echo "You will need to manually install Icecast after this script completes."
    echo "See the 'MANUAL ICECAST INSTALLATION' section at the end of this script."
    echo "===================================================="
    echo ""
else
    echo "Icecast installed successfully via yum."
fi

# Install Liquidsoap for advanced automation (modern stack)
echo "Installing Liquidsoap..."
yum install -y liquidsoap -y || { echo "Warning: Liquidsoap not available"; }

# Install ezstream for AutoDJ (kept for compatibility; can be replaced by Liquidsoap in future)
echo "Installing ezstream..."
yum install -y ezstream -y || { echo "Warning: ezstream not available"; }

# Install ffmpeg for transcoding
echo "Installing ffmpeg..."
yum install -y ffmpeg ffmpeg-devel -y || { echo "Failed to install ffmpeg"; exit 1; }

# Install phpMyAdmin
echo "Installing phpMyAdmin..."
yum install -y phpMyAdmin -y || { echo "Warning: phpMyAdmin not available"; }

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
    
    'default' => 'mysql',
    
    'connections' => [
    
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', '$DB_NAME'),
            'username' => env('DB_USERNAME', '$DB_USER'),
            'password' => env('DB_PASSWORD', '$DB_PASSWORD'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],
    
    ],
    
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
echo "3. After installation, visit http://radiohosting.local/ in a web browser to access the panel."
echo "4. Log in with the system user 'radiopanel' and the password shown above."
echo "   IMPORTANT: Change the password after first login using 'passwd radiopanel' and then update the hash file."
echo "   To update the hash file after changing the password, run:"
echo "   sudo /path/to/whm/update_panel_hash.sh"
echo ""
echo "Firewall configured: firewalld is active with ports 80 (HTTP), 443 (HTTPS), 8000 (Icecast HTTP), 8001 (Icecast HTTPS), and 8080 (alternative web panel) open."
echo ""
echo "The radio hosting services (Icecast, Liquidsoap, ezstream, ffmpeg) are installed and ready to be managed by the panel."
echo ""
echo "Note: Shoutcast was removed per user request; Liquidsoap is installed for automation."
echo ""
echo "==== IMPORTANT NOTE ABOUT ICECAST ===="
echo "If you saw 'No package icecast available' above, Icecast was not installed via yum."
echo "This can happen on some RHEL/CentOS/Fedora versions where Icecast isn't in the default/EPEL repos."
echo ""
echo "To enable radio streaming features, you must manually install Icecast after this installer completes:"
echo ""
echo "1. Install build dependencies:"
echo "   sudo yum groupinstall \"Development Tools\" -y"
echo "   sudo yum install -y gcc gcc-c++ make autoconf automake libtool libxml2-devel curl-devel openssl-devel libxslt-devel pcre2-devel libvorbis-devel sqlite-devel wget tar"
echo ""
echo "2. Download and compile Icecast:"
echo "   cd /usr/local/src"
echo "   wget https://downloads.xiph.org/releases/icecast/icecast-2.5.0.tar.gz"
echo "   tar -xzf icecast-2.5.0.tar.gz"
echo "   cd icecast-2.5.0"
echo "   ./configure"
echo "   make -j$(nproc)"
echo "   sudo make install"
echo ""
echo "3. Configure Icecast:"
echo "   sudo mkdir -p /usr/local/etc/icecast"
echo "   sudo cp conf/icecast.xml.dist /usr/local/etc/icecast/icecast.xml"
echo ""
echo "4. Start Icecast:"
echo "   /usr/local/bin/icecast -c /usr/local/etc/icecast/icecast.xml &"
echo ""
echo "5. Open firewall port (if not already open):"
echo "   sudo firewall-cmd --permanent --add-port=8000/tcp"
echo "   sudo firewall-cmd --reload"
echo ""
echo "Once Icecast is manually installed, all radio streaming features will work in the panel."
echo "The panel will continue to work for all other hosting features while you set up Icecast manually."
echo "================================"
echo ""
echo "To access phpMyAdmin, visit http://radiohosting.local/phpmyadmin"
echo ""
echo "=== End of Installation ==="