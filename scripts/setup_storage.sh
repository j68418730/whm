#!/bin/bash
# Planet Hosts Storage Directory Setup
# Run after deployment to ensure upload directories exist with correct permissions

STORAGE_BASE="/var/www/radiohosting/storage"
WWW_USER="www-data"

echo "Setting up storage directories..."

# Create base storage directory
mkdir -p "$STORAGE_BASE"
chmod 755 "$STORAGE_BASE"

# Create subdirectories as needed
DIRS=(
    "radio_downloads"
    "radio"
    "radio/autodj"
    "radio/musicdatabase"
    "radio/streams"
    "radio/dj"
)

for dir in "${DIRS[@]}"; do
    full_path="$STORAGE_BASE/$dir"
    mkdir -p "$full_path"
    chown -R "$WWW_USER:$WWW_USER" "$full_path"
    chmod 755 "$full_path"
    echo "  Created: $full_path"
done

# Also fix liquidsoap config and log dirs
mkdir -p /var/www/radiohosting/config/liquidsoap
chmod 777 /var/www/radiohosting/config/liquidsoap
mkdir -p /var/log/liquidsoap
chmod 777 /var/log/liquidsoap

echo "Storage directories ready."
