#!/bin/bash
# Radio Hosting Panel Installer
# This script installs the radio hosting panel as a core system service
# Supports both Debian/Ubuntu (apt) and RHEL/CentOS/Fedora (yum/dnf)

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

# Function to detect Linux distribution and package manager
detect_package_manager() {
    if [ -f /etc/os-release ]; then
        # freedesktop.org and systemd
        . /etc/os-release
        OS=$NAME
        VER=$VERSION_ID
    elif type lsb_release >/dev/null 2>&1; then
        # linuxbase.org
        OS=$(lsb_release -si)
        VER=$(lsb_release -sr)
    elif [ -f /etc/lsb-release ]; then
        # For some versions of Debian/Ubuntu without lsb_release command
        . /etc/lsb-release
        OS=$DISTRIB_ID
        VER=$DISTRIB_RELEASE
    elif [ -f /etc/debian_version ]; then
        # Older Debian/Ubuntu/etc.
        OS=Debian
        VER=$(cat /etc/debian_version)
    elif [ -f /etc/SuSe-release ]; then
        # Older SuSE/etc.
        ...
    elif [ -f /etc/redhat-release ]; then
        # Older Red Hat, CentOS, etc.
        ...
    else
        # Fall back to uname, e.g. "Linux <version>", also works for BSD, etc.
        OS=$(uname -s)
        VER=$(uname -r)
    fi

    # Determine package manager
    if [[ "$OS" == *"Ubuntu"* ]] || [[ "$OS" == *"Debian"* ]] || [[ "$OS" == *"Linux Mint"* ]]; then
        PM="apt"
        PM_UPDATE="update"
        PM_INSTALL="install -y"
        PM_REMOVE="remove -y"
    elif [[ "$OS" == *"CentOS"* ]] || [[ "$OS" == *"Red Hat"* ]] || [[ "$OS" == *"Fedora"* ]] || [[ "$OS" == *"Amazon"* ]]; then
        if [ -f /usr/bin/dnf ]; then
            PM="dnf"
        else
            PM="yum"
        fi
        PM_UPDATE="check-update || true"  # yum check-update returns non-zero if updates available
        PM_INSTALL="install -y"
        PM_REMOVE="remove -y"
    else
        # Default to apt (assume Debian/Ubuntu)
        PM="apt"
        PM_UPDATE="update"
        PM_INSTALL="install -y"
        PM_REMOVE="remove -y"
    fi

    echo "$PM"
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

# Detect package manager
PM=$(detect_package_manager)
echo "Detected package manager: $PM"

# Update system
echo "Updating system packages..."
if [ "$PM" = "apt" ]; then
    apt-get $PM_UPDATE
elif [ "$PM" = "yum" ] || [ "$PM" = "dnf" ]; then
    $PM $PM_UPDATE
fi

# Install required packages
echo "Installing Apache, MySQL, PHP, and dependencies..."
if [ "$PM" = "apt" ]; then
    apt-get $PM_INSTALL apache2 mysql-server php php-mysql libapache2-mod-php php-cli php-curl php-gd php-mbstring php-xml php-zip unzip
elif [ "$PM" = "yum" ]; then
    yum $PM_INSTALL httpd mysql-server php php-mysql php-cli php-curl php-gd php-mbstring php-xml php-zip unzip
elif [ "$PM" = "dnf" ]; then
    dnf $PM_INSTALL httpd mysql-server php php-mysql php-cli php-curl php-gd php-mbstring php-xml php-zip unzip
fi

# Install Icecast and Shoutcast for radio streaming
echo "Installing Icecast and Shoutcast..."
if [ "$PM" = "apt" ]; then
    apt-get $PM_INSTALL icecast2 shoutcast
elif [ "$PM" = "yum" ]; then
    # For CentOS/RHEL, might need EPEL repository
    yum $PM_INSTALL epel-release -y
    yum $PM_INSTALL icecast shoutcast
elif [ "$PM" = "dnf" ]; then
    dnf $PM_INSTALL icecast shoutcast
fi

# Install ezstream for AutoDJ
echo "Installing ezstream..."
if [ "$PM" = "apt" ]; then
    apt-get $PM_INSTALL ezstream
elif [ "$PM" = "yum" ]; then
    yum $PM_INSTALL ezstream
elif [ "$PM" = "dnf" ]; then
    dnf $PM_INSTALL ezstream
fi

# Install ffmpeg for transcoding
echo "Installing ffmpeg..."
if [ "$PM" = "apt" ]; then
    apt-get $PM_INSTALL ffmpeg
elif [ "$PM" = "yum" ]; then
    yum $PM_INSTALL ffmpeg ffmpeg-devel
elif [ "$PM" = "dnf" ]; then
    dnf $PM_INSTALL ffmpeg ffmpeg-devel
fi

# Install phpMyAdmin (non-interactive)
echo "Installing phpMyAdmin..."
if [ "$PM" = "apt" ]; then
    debconf-set-selections <<< "phpmyadmin phpmyadmin/reconfigure-webserver multiselect apache2"
    debconf-set-selections <<< "phpmyadmin phpmyadmin/dbconfig-install boolean true"
    debconf-set-selections <<< "phpmyadmin phpmyadmin/mysql/admin-user string root"
    debconf-set-selections <<< "phpmyadmin phpmyadmin/mysql/admin-password password $MYSQL_ROOT_PASSWORD"
    debconf-set-selections <<< "phpmyadmin phpmyadmin/mysql/app-pass password $MYSQL_ROOT_PASSWORD"
    debconf-set-selections <<< "phpmyadmin phpmyadmin/app-password-confirm password $MYSQL_ROOT_PASSWORD"
    apt-get $PM_INSTALL phpmyadmin
elif [ "$PM" = "yum" ] || [ "$PM" = "dnf" ]; then
    # For yum/dnf, we might need to enable additional repos or use different package name
    # For simplicity, we'll try the standard approach
    $PM $PM_INSTALL phpMyAdmin
fi

# Enable Apache modules
echo "Enabling Apache modules..."
if [ "$PM" = "apt" ]; then
    a2enmod rewrite
    a2enmod headers
elif [ "$PM" = "yum" ] || [ "$PM" = "dnf" ]; then
    # For httpd, modules are usually enabled by default or via config
    # We'll ensure rewrite and headers are available
    :
fi

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
chown -R apache:apache $PANEL_DIR  # Changed to apache for RHEL/CentOS
if [ "$PM" = "apt" ]; then
    chown -R www-data:www-data $PANEL_DIR
fi
chmod -R 755 $PANEL_DIR
find $PANEL_DIR -type d -exec chmod 755 {} \;
find $PANEL_DIR -type f -exec chmod 644 {} \;

# Set up Apache virtual host
echo "Setting up Apache virtual host..."
VHOST_FILE="/etc/httpd/conf.d/radiohosting.conf"  # Changed path for RHEL/CentOS
if [ "$PM" = "apt" ]; then
    VHOST_FILE="/etc/apache2/sites-available/radiohosting.conf"
fi

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

    ErrorLog ${APACHE_LOG_DIR:-/var/log/httpd}/radiohosting_error.log
    CustomLog ${APACHE_LOG_DIR:-/var/log/httpd}/radiohosting_access.log combined
</VirtualHost>
EOF

# Enable the site (different for Apache vs httpd)
if [ "$PM" = "apt" ]; then
    a2ensite radiohosting.conf
    a2dissite 000-default.conf  # Disable the default site
else
    # For httpd, just ensure the config is included (it should be by default in conf.d/)
    :
fi

# Restart web server
echo "Restarting web server..."
if [ "$PM" = "apt" ]; then
    systemctl restart apache2
else
    systemctl restart httpd
fi

# Set up MySQL database and user
echo "Setting up MySQL database..."
# We'll create a database and user for the panel
# In a real scenario, we would ask for credentials or use a secure method.
# For simplicity, we'll create a database 'radiohosting' and user 'radiouser' with a random password.

# Generate a random password for the database user
DB_PASSWORD=$(openssl rand -base64 12)
DB_NAME="radiohosting"
DB_USER="radiouser"

# MySQL root password - we assume it's not set or we can set it.
# We'll prompt the user for the MySQL root password or use a default.
# Since we are in an automated script, we'll set the MySQL root password if not set.
# However, note that the MySQL installation might have prompted for a password.
# We'll check if we can connect without a password (if the setup was non-interactive).

# Let's try to set the MySQL root password to empty for simplicity in this script.
# In production, you should set a strong password.

# Stop MySQL service to reset the root password if needed
# But note: we just installed MySQL, so we can set the root password during installation.
# We'll assume we set it during installation via debconf-set-selections (for apt) or similar.

# We'll reset the MySQL root password to something known for the script.
# For simplicity, we'll use an empty password for root in this script (not recommended for production).
# Alternatively, we can ask the user.

# We'll create the database and user with the root account (assuming we can connect as root without password for now)
# If the installation prompted for a root password, we would need to use it.

# Let's attempt to connect as root without password and if it fails, we'll prompt.
if mysqladmin -u root ping >/dev/null 2>&1; then
  MYSQL_ROOT_OPTS="-u root"
else
  echo "Cannot connect to MySQL as root without password."
  echo "Please enter the MySQL root password:"
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
chown www-data:www-data $CONFIG_FILE
chmod 640 $CONFIG_FILE

# Initialize the database schema (we would have migrations, but for simplicity we'll run a schema script)
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
*/5 * * * * www-data php $PANEL_DIR/artisan radio:analytics >> $PANEL_DIR/logs/cron.log 2>&1

# Check for stopped streams and restart them if needed (example)
0 * * * * www-data php $PANEL_DIR/artisan radio:restart-stopped-streams >> $PANEL_DIR/logs/cron.log 2>&1
EOF
chmod 644 $CRON_FILE

# Create log directory and set permissions
mkdir -p $PANEL_DIR/logs
chown -R www-data:www-data $PANEL_DIR/logs
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
echo "4. Log in with the default administrator credentials (to be set in the panel installer or via database)."
echo ""
echo "Important: For security, please change the default passwords and consider setting up a firewall."
echo ""
echo "The radio hosting services (Icecast, Shoutcast) are installed and ready to be managed by the panel."
echo ""
echo "To access phpMyAdmin, visit http://radiohosting.local/phpmyadmin"
echo ""
echo "=== End of Installation ==="
EOF