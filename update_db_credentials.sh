#!/bin/bash
# Radio Hosting Panel Database Credentials Updater
# This script updates the MySQL/MariaDB database credentials for the panel

set -e

# Get the directory where this script is located
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "$SCRIPT_DIR"

# Panel directory
PANEL_DIR="/var/www/radiohosting"
CONFIG_FILE="$PANEL_DIR/config/database.php"

echo "=== Radio Hosting Panel Database Credentials Updater ==="
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then
  echo "Please run as root (use sudo)."
  exit 1
fi

# Check if panel is installed
if [ ! -d "$PANEL_DIR" ]; then
  echo "Error: Panel directory not found at $PANEL_DIR"
  echo "Please ensure the panel is installed before running this script."
  exit 1
fi

# Check if config file exists
if [ ! -f "$CONFIG_FILE" ]; then
  echo "Error: Database config file not found at $CONFIG_FILE"
  exit 1
fi

# Get current credentials from config file (for display)
if [ -f "$CONFIG_FILE" ]; then
  # Extract current values (simple parsing)
  DB_NAME=$(grep -oP "(?<='database' => env\('DB_DATABASE', ')[^']*" "$CONFIG_FILE" || echo "radiohosting")
  DB_USER=$(grep -oP "(?<='username' => env\('DB_USERNAME', ')[^']*" "$CONFIG_FILE" || echo "radiouser")
fi

echo "Current database configuration:"
echo "  Database: $DB_NAME"
echo "  Username: $DB_USER"
echo ""

# Ask if user wants to generate new credentials or enter custom ones
echo "How would you like to update the database credentials?"
echo "1. Generate new random credentials (recommended)"
echo "2. Enter custom credentials"
echo "3. Cancel"
read -p "Enter your choice (1-3): " choice

case $choice in
  1)
    # Generate new random credentials
    NEW_DB_PASSWORD=$(openssl rand -base64 12)
    NEW_DB_USER="radiouser"
    NEW_DB_NAME="radiohosting"
    ;;
  2)
    # Get custom credentials from user
    read -p "Enter new database name [$DB_NAME]: " input_db_name
    NEW_DB_NAME=${input_db_name:-$DB_NAME}
    
    read -p "Enter new database username [$DB_USER]: " input_db_user
    NEW_DB_USER=${input_db_user:-$DB_USER}
    
    read -s -p "Enter new database password: " input_db_pass
    echo ""
    if [ -z "$input_db_pass" ]; then
      # Generate random if empty
      NEW_DB_PASSWORD=$(openssl rand -base64 12)
    else
      NEW_DB_PASSWORD="$input_db_pass"
    fi
    ;;
  3)
    echo "Operation cancelled."
    exit 0
    ;;
  *)
    echo "Invalid choice. Exiting."
    exit 1
    ;;
esac

echo ""
echo "New database configuration:"
echo "  Database: $NEW_DB_NAME"
echo "  Username: $NEW_DB_USER"
echo "  Password: $NEW_DB_PASSWORD"
echo ""

# Confirm before proceeding
read -p "Do you want to proceed with updating the database credentials? (y/N): " confirm
if [[ ! "$confirm" =~ ^[Yy]$ ]]; then
  echo "Operation cancelled."
  exit 0
fi

# Get MariaDB root credentials
echo ""
echo "To update the database user password, we need MariaDB root access."
echo "If you can connect as root without password, press Enter."
echo "Otherwise, enter the MariaDB root password:"
read -s MYSQL_ROOT_PASSWORD
echo ""

if [ -z "$MYSQL_ROOT_PASSWORD" ]; then
  MYSQL_ROOT_OPTS="-u root"
else
  MYSQL_ROOT_OPTS="-u root -p$MYSQL_ROOT_PASSWORD"
fi

# Test connection
if ! mysqladmin $MYSQL_ROOT_OPTS ping >/dev/null 2>&1; then
  echo "Error: Cannot connect to MariaDB with provided credentials."
  exit 1
fi

# Update database user password
echo "Updating database user password..."
mysql $MYSQL_ROOT_OPTS -e "ALTER USER '$NEW_DB_USER'@'localhost' IDENTIFIED BY '$NEW_DB_PASSWORD';" || {
  # If user doesn't exist, create it
  mysql $MYSQL_ROOT_OPTS -e "CREATE USER IF NOT EXISTS '$NEW_DB_USER'@'localhost' IDENTIFIED BY '$NEW_DB_PASSWORD';"
}

# Ensure user has privileges on the database
echo "Granting privileges..."
mysql $MYSQL_ROOT_OPTS -e "GRANT ALL PRIVILEGES ON $NEW_DB_NAME.* TO '$NEW_DB_USER'@'localhost';"
mysql $MYSQL_ROOT_OPTS -e "FLUSH PRIVILEGES;"

# Update the config file
echo "Updating configuration file..."
# Backup original config
cp "$CONFIG_FILE" "$CONFIG_FILE.backup.$(date +%Y%m%d_%H%M%S)"

# Update the config file with new values
sed -i "s/env('DB_DATABASE', '[^']*'$/env('DB_DATABASE', '$NEW_DB_NAME')/g" "$CONFIG_FILE"
sed -i "s/env('DB_USERNAME', '[^']*'$/env('DB_USERNAME', '$NEW_DB_USER')/g" "$CONFIG_FILE"
sed -i "s/env('DB_PASSWORD', '[^']*'$/env('DB_PASSWORD', '$NEW_DB_PASSWORD')/g" "$CONFIG_FILE"

# Set proper permissions
chown apache:apache "$CONFIG_FILE"
chmod 640 "$CONFIG_FILE"

echo ""
echo "=== Database Credentials Updated Successfully ==="
echo ""
echo "Updated configuration:"
echo "  Database: $NEW_DB_NAME"
echo "  Username: $NEW_DB_USER"
echo "  Password: $NEW_DB_PASSWORD"
echo ""
echo "Next steps:"
echo "1. Restart the web server to ensure all changes are loaded:"
echo "   sudo systemctl restart httpd   # For RHEL/CentOS/Fedora"
echo "   sudo systemctl restart apache2  # For Debian/Ubuntu"
echo ""
echo "2. Test the new credentials by logging into the panel"
echo ""
echo "=== Update Complete ==="