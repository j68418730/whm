#!/bin/bash
# Radio Hosting Panel Installer
# This script installs the radio hosting panel as a core system service

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

# Ask for domain name (needed for SSL)
echo "Please enter your domain name (e.g., radio.example.com):"
read DOMAIN_NAME
if [ -z "$DOMAIN_NAME" ]; then
    DOMAIN_NAME="radiohosting.local"
    echo "No domain entered. Using default: $DOMAIN_NAME"
fi

# Ask if they want to enable SSL
echo "Do you want to enable SSL with Let's Encrypt? (y/n):"
read ENABLE_SSL
ENABLE_SSL=${ENABLE_SSL:-n}

# Ask about Cloudflare
echo "Are you using Cloudflare for DNS? (y/n):"
read USE_CLOUDFLARE
USE_CLOUDFLARE=${USE_CLOUDFLARE:-n}

# Update system
echo "Updating system packages..."
apt-get update -y

# Install required packages
echo "Installing Apache, MySQL, PHP, and dependencies..."
apt-get install -y apache2 mysql-server php php-mysql libapache2-mod-php php-cli php-curl php-gd php-mbstring php-xml php-zip unzip

# Install Icecast and Shoutcast for radio streaming
echo "Installing Icecast and Shoutcast..."
apt-get install -y icecast2 shoutcast

# Install ezstream for AutoDJ
echo "Installing ezstream..."
apt-get install -y ezstream

# Install ffmpeg for transcoding
echo "Installing ffmpeg..."
apt-get install -y ffmpeg

# Install Certbot for Let's Encrypt (if SSL enabled)
if [ "$ENABLE_SSL" = "y" ] || [ "$ENABLE_SSL" = "Y" ]; then
    echo "Installing Certbot for Let's Encrypt..."
    apt-get install -y certbot python3-certbot-apache
fi

# Install phpMyAdmin (non-interactive)
echo "Installing phpMyAdmin..."
debconf-set-selections <<< "phpmyadmin phpmyadmin/reconfigure-webserver multiselect apache2"
debconf-set-selections <<< "phpmyadmin phpmyadmin/dbconfig-install boolean true"
debconf-set-selections <<< "phpmyadmin phpmyadmin/mysql/admin-user string root"
debconf-set-selections <<< "phpmyadmin phpmyadmin/mysql/admin-password password $MYSQL_ROOT_PASSWORD"
debconf-set-selections <<< "phpmyadmin phpmyadmin/mysql/app-pass password $MYSQL_ROOT_PASSWORD"
debconf-set-selections <<< "phpmyadmin phpmyadmin/app-password-confirm password $MYSQL_ROOT_PASSWORD"
apt-get install -y phpmyadmin

# Enable Apache modules
echo "Enabling Apache modules..."
a2enmod rewrite
a2enmod headers
a2enmod ssl  # Enable SSL module

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
chown -R www-data:www-data $PANEL_DIR
chmod -R 755 $PANEL_DIR
find $PANEL_DIR -type d -exec chmod 755 {} \;
find $PANEL_DIR -type f -exec chmod 644 {} \;

# Set up Apache virtual host
echo "Setting up Apache virtual host..."
VHOST_FILE="/etc/apache2/sites-available/radiohosting.conf"
cat > $VHOST_FILE <<EOF
<VirtualHost *:80>
    ServerAdmin webmaster@localhost
    DocumentRoot $PANEL_DIR
    ServerName $DOMAIN_NAME
    ServerAlias www.$DOMAIN_NAME

    <Directory $PANEL_DIR>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/radiohosting_error.log
    CustomLog ${APACHE_LOG_DIR}/radiohosting_access.log combined
</VirtualHost>
EOF

# Enable the site
a2ensite radiohosting.conf
a2dissite 000-default.conf  # Disable the default site

# If SSL is enabled, obtain and configure Let's Encrypt certificate
if [ "$ENABLE_SSL" = "y" ] || [ "$ENABLE_SSL" = "Y" ]; then
    echo "Obtaining SSL certificate from Let's Encrypt for $DOMAIN_NAME..."
    
    # Stop Apache temporarily for certbot standalone mode
    systemctl stop apache2
    
    # Obtain certificate
    certbot certonly --standalone -d $DOMAIN_NAME --non-interactive --agree-tos --email admin@$DOMAIN_NAME
    
    # Start Apache again
    systemctl start apache2
    
    # Update virtual host for SSL
    cat > $VHOST_FILE <<EOF
<VirtualHost *:80>
    ServerAdmin webmaster@localhost
    DocumentRoot $PANEL_DIR
    ServerName $DOMAIN_NAME
    ServerAlias www.$DOMAIN_NAME
    
    # Redirect HTTP to HTTPS
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
    
    <Directory $PANEL_DIR>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/radiohosting_error.log
    CustomLog ${APACHE_LOG_DIR}/radiohosting_access.log combined
</VirtualHost>

<VirtualHost *:443>
    ServerAdmin webmaster@localhost
    DocumentRoot $PANEL_DIR
    ServerName $DOMAIN_NAME
    ServerAlias www.$DOMAIN_NAME
    
    <Directory $PANEL_DIR>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/$DOMAIN_NAME/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/$DOMAIN_NAME/privkey.pem
    SSLCertificateChainFile /etc/letsencrypt/live/$DOMAIN_NAME/chain.pem
    
    # SSL Security Settings
    SSLProtocol all -SSLv2 -SSLv3
    SSLCipherSuite ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-AES256-GCM-SHA384
    SSLHonorCipherOrder on
    
    # HSTS (15768000 seconds = 6 months)
    Header always set Strict-Transport-Security "max-age=15768000"
    
    ErrorLog ${APACHE_LOG_DIR}/radiohosting_ssl_error.log
    CustomLog ${APACHE_LOG_DIR}/radiohosting_ssl_access.log combined
</VirtualHost>
EOF
    
    # Enable SSL site
    a2enmod ssl
    systemctl restart apache2
    
    echo "SSL certificate obtained and configured successfully!"
    echo "Certificate will auto-renew. To test renewal: sudo certbot renew --dry-run"
fi

# Restart Apache
echo "Restarting Apache..."
systemctl restart apache2

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
# We'll assume we set it during installation via debconf-set-selections.

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
echo "3. After installation, visit http://$DOMAIN_NAME/ in a web browser to access the panel."
if [ "$ENABLE_SSL" = "y" ] || [ "$ENABLE_SSL" = "Y" ]; then
    echo "   (HTTPS is enforced via Let's Encrypt SSL certificate)"
fi
echo "4. Log in with the default administrator credentials (to be set in the panel installer or via database)."
echo ""
echo "Important: For security, please change the default passwords and consider setting up a firewall."
echo ""
echo "The radio hosting services (Icecast, Shoutcast) are installed and ready to be managed by the panel."
echo ""
if [ "$USE_CLOUDFLARE" = "y" ] || [ "$USE_CLOUDFLARE" = "Y" ]; then
    echo ""
    echo "=== Cloudflare Setup Instructions ==="
    echo "1. Log in to your Cloudflare account"
    echo "2. Add an A record for $DOMAIN_NAME pointing to $SERVER_IP"
    echo "3. Make sure the DNS record is NOT proxied (DNS only) for now to allow SSL validation"
    echo "4. After SSL is active, you can enable proxying if desired"
    echo "5. Consider enabling SSL/TLS encryption mode: Full (strict) if you have origin certificate"
    echo "6. For maximum security, consider using Origin CA certificates from Cloudflare"
fi
echo ""
echo "To access phpMyAdmin, visit http://$DOMAIN_NAME/phpmyadmin"
if [ "$ENABLE_SSL" = "y" ] || [ "$ENABLE_SSL" = "Y" ]; then
    echo "   or https://$DOMAIN_NAME/phpmyadmin"
fi
echo ""
echo "=== End of Installation ==="
EOF