#!/bin/bash
# Planet Hosts - Account Provisioning Script
# Called via sudo by the panel to create system users and setup directories

set -eo pipefail

USERNAME="$1"
DOMAIN="$2"
HOMEDIR="$3"
PASSWORD="$4"

if [ -z "$USERNAME" ] || [ -z "$HOMEDIR" ]; then
    echo "Usage: $0 <username> <domain> <homedir> [password]"
    exit 1
fi

# Create system user if not exists
if ! id "$USERNAME" &>/dev/null; then
    if [ -n "$PASSWORD" ]; then
        useradd -m -d "$HOMEDIR" -s /bin/bash "$USERNAME"
        echo "$USERNAME:$PASSWORD" | chpasswd
    else
        useradd -m -d "$HOMEDIR" -s /bin/bash "$USERNAME"
    fi
fi

# Add to www-data group for FTP/web permissions
usermod -a -G www-data "$USERNAME" 2>/dev/null || true

# Ensure home directory exists
mkdir -p "$HOMEDIR"

# Create public_html
mkdir -p "$HOMEDIR/public_html"

# Create default index page
if [ ! -f "$HOMEDIR/public_html/index.html" ]; then
    cat > "$HOMEDIR/public_html/index.html" << EOF
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Welcome - $DOMAIN</title>
<style>body{font-family:Arial,sans-serif;background:#020817;color:#fff;display:flex;justify-content:center;align-items:center;height:100vh;margin:0}h1{color:#0A84FF}</style>
</head>
<body><h1>Welcome to $DOMAIN</h1><p style="color:#94a3b8">Your website is ready.</p></body>
</html>
EOF
fi

# Create additional directories
mkdir -p "$HOMEDIR/logs"
mkdir -p "$HOMEDIR/ssl"
mkdir -p "$HOMEDIR/backups"
mkdir -p "$HOMEDIR/tmp"

# Set ownership
chown -R "$USERNAME:$USERNAME" "$HOMEDIR"
chmod 755 "$HOMEDIR"
chmod 755 "$HOMEDIR/public_html"

# Create PHP info file for testing
if [ ! -f "$HOMEDIR/public_html/info.php" ]; then
    echo "<?php phpinfo(); ?>" > "$HOMEDIR/public_html/info.php"
    chown "$USERNAME:$USERNAME" "$HOMEDIR/public_html/info.php"
fi

exit 0
