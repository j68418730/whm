#!/bin/bash
# Radio Hosting Panel Installer
# This script installs the radio hosting panel as a core system service
# For RHEL/CentOS/Fedora systems (yum/dnf)

set -e

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

# Update system
echo "Updating system packages..."
yum check-update || true  # yum check-update returns non-zero if updates available

# Install required packages
echo "Installing Apache, MariaDB, PHP, and dependencies..."
yum install -y httpd mariadb-server php php-mysqlnd php-cli php-curl php-gd php-mbstring php-xml php-zip unzip

# Install Icecast (removed Shoutcast per user request)
echo "Installing Icecast..."
# Enable EPEL repository for additional packages
yum install -y epel-release -y
yum install -y icecast -y

# Install Liquidsoap for advanced automation (modern stack)
echo "Installing Liquidsoap..."
yum install -y liquidsoap -y

# Install ezstream for AutoDJ (kept for compatibility; can be replaced by Liquidsoap in future)
echo "Installing ezstream..."
yum install -y ezstream -y

# Install ffmpeg for transcoding
echo "Installing ffmpeg..."
yum install -y ffmpeg ffmpeg-devel -y

# Install phpMyAdmin
echo "Installing phpMyAdmin..."
yum install -y phpMyAdmin -y

# Enable Apache modules (httpd)
echo "Enabling Apache modules..."
# For httpd, rewrite and headers modules are usually available by default
# We'll ensure they're enabled in the config if needed
# No explicit enabling needed for most modules in httpd

# Set up directory for our panel
PANEL_DIR="/var/www/radiohosting"
echo "Setting up panel directory at $PANEL_DIR..."
mkdir -p $PANEL_DIR

# Copy the panel files
SOURCE_DIR="/tmp/radiohosting_panel/whm"

if [ -d "$SOURCE_DIR" ]; then
  echo "Copying panel files from $SOURCE_DIR to $PANEL_DIR..."
  cp -r $SOURCE_DIR/* $PANEL_DIR/
else
  echo "Source directory $SOURCE_DIR not found."
  echo "Please ensure the panel code is available at $SOURCE_DIR"
  echo "Exiting."
  exit 1
fi

# Set permissions
echo "Setting permissions..."
chown -R apache:apache $PANEL_DIR
chmod -R 755 $PANEL_DIR
find $PANEL_DIR -type d -exec chmod 755 {} \;
find $PANEL_DIR -type f -exec chmod 644 {} \;

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
    mv /etc/httpd/conf.d/welcome.conf /etc/httpd/conf.d/welcome.conf.disabled
fi

# Restart Apache
echo "Restarting Apache..."
systemctl restart httpd
systemctl enable httpd

# Set up MariaDB database and user
echo "Setting up MariaDB database..."
# Start and enable MariaDB
systemctl start mariadb
systemctl enable mariadb

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
mysql $MYSQL_ROOT_OPTS -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql $MYSQL_ROOT_OPTS -e "CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASSWORD';"
mysql $MYSQL_ROOT_OPTS -e "GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';"
mysql $MYSQL_ROOT_OPTS -e "FLUSH PRIVILEGES;"

# Save the database credentials to a configuration file
echo "Saving database configuration..."
CONFIG_FILE="$PANEL_DIR/config/database.php"
mkdir -p $(dirname $CONFIG_FILE)
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
            'password' = env('DB_PASSWORD', '$DB_PASSWORD'),
            'charset' = 'utf8mb4',
            'collation' = 'utf8mb4_unicode_ci',
            'prefix' = '',
            'strict' = true,
            'engine' = null,
        ],

    ],

];
EOF

# Set permissions on the config file
chown www-data:www-data $CONFIG_FILE
chmod 640 $CONFIG_FILE

# Initialize the database schema
# We assume there is a schema.sql file in the panel code.
SCHEMA_FILE="$PANEL_DIR/database/schema.sql"
if [ -f "$SCHEMA_FILE" ]; then
  echo "Importing database schema..."
  mysql $MYSQL_ROOT_OPTS $DB_NAME < $SCHEMA_FILE
else
  echo "Schema file not found at $SCHEMA_FILE. Skipping database schema import."
fi

# Enable the radio hosting service in the configuration (by default)
echo "Enabling radio hosting in configuration..."
CONFIG_FILE="$PANEL_DIR/config/radio.php"
if [ -f "$CONFIG_FILE" ]; then
  # We'll ensure global_enabled is true
  sed -i "s/'global_enabled' => false/'global_enabled' => true/" $CONFIG_FILE
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
chmod 644 $CRON_FILE

# Create log directory and set permissions
mkdir -p $PANEL_DIR/logs
chown -R apache:apache $PANEL_DIR/logs
chmod -R 755 $PANEL_DIR/logs

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
echo "Important: For security, please consider setting up a firewall (e.g., CSF, firewalld)."
echo ""
echo "The radio hosting services (Icecast, Liquidsoap, ezstream, Shoutcast not installed) are installed and ready to be managed by the panel."
echo ""
echo "Note: Shoutcast was removed per user request; Icecast and Liquidsoap are installed for modern stack."
echo ""
echo "To access phpMyAdmin, visit http://radiohosting.local/phpmyadmin"
echo ""
echo "=== End of Installation ==="
EOF