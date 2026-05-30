#!/bin/bash
# Radio Hosting Panel Update Script
# This script checks for updates from the GitHub repository and applies them.

set -e

# Get the directory where this script is located
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "$SCRIPT_DIR"

echo "=== Radio Hosting Panel Update Checker ==="
echo "Checking for updates from the remote repository..."
echo ""

# Fetch the latest from the remote
git fetch origin

# Check if the local master branch is behind the remote master branch
if git rev-parse --abbrev-ref HEAD | grep -q "master"; then
    LOCAL=$(git rev-parse master)
    REMOTE=$(git rev-parse origin/master)
    BASE=$(git merge-base master origin/master)

    if [ "$LOCAL" = "$REMOTE" ]; then
        echo "Your panel is up to date."
    elif [ "$LOCAL" = "$BASE" ]; then
        echo "Update available! Pulling the latest changes..."
        git pull origin master
        echo ""
        echo "Update applied successfully."
        echo ""
        echo "=== Post-Update Instructions ==="
        echo "1. If there were any database schema changes, you may need to run:"
        echo "   mysql -u [username] -p[password] [database_name] < database/schema.sql"
        echo "   (Note: This will not overwrite existing data, but will add new tables/columns if needed.)"
        echo ""
        echo "2. Clear any cached data if applicable (the panel currently does not use caching)."
        echo ""
        echo "3. Restart the web server to ensure all changes are loaded:"
        echo "   sudo systemctl restart httpd   # For RHEL/CentOS/Fedora"
        echo "   sudo systemctl restart apache2  # For Debian/Ubuntu"
        echo ""
        echo "4. Log out and log back into the panel to ensure you have the latest permissions and settings."
        echo ""
    elif [ "$REMOTE" = "$BASE" ]; then
        echo "Your local branch is ahead of the remote. Consider pushing your changes."
    else
        echo "Local and remote have diverged. Please resolve manually."
    fi
else
    echo "You are not on the master branch. Please switch to master to use this update script."
    echo "Current branch: $(git rev-parse --abbrev-ref HEAD)"
fi

echo ""
echo "=== Update Check Complete ==="
EOF