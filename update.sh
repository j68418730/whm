#!/bin/bash

# =========================================================
# Planet Hosts Master Panel Update Script
# Checks for updates and applies them safely.
# =========================================================

set -eo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

VERSION_FILE="$SCRIPT_DIR/VERSION"
PANEL_DIR="/var/www/radiohosting"
CURRENT_VERSION="1.0.0"
REMOTE_VERSION=""
GIT_AVAILABLE=0

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

# =========================================================
# Load current version
# =========================================================

if [ -f "$VERSION_FILE" ]; then
    CURRENT_VERSION=$(cat "$VERSION_FILE" | tr -d ' \n')
fi

# =========================================================
# Check if git is available and this is a git repo
# =========================================================

if command -v git >/dev/null 2>&1 && [ -d .git ]; then
    GIT_AVAILABLE=1
fi

# =========================================================
# Helper: compare versions (semver)
# =========================================================

ver_gt() {
    test "$(printf '%s\n' "$@" | sort -V | head -n 1)" != "$1"
}

# =========================================================
# Check for updates via git
# =========================================================

check_git() {
    echo -e "${CYAN}Checking remote repository...${NC}"
    git fetch origin 2>/dev/null || {
        echo -e "${RED}Failed to reach remote repository. Check your internet connection.${NC}"
        return 1
    }

    local branch
    branch=$(git rev-parse --abbrev-ref HEAD 2>/dev/null || echo "unknown")

    if [ "$branch" != "master" ]; then
        echo -e "${YELLOW}You are on branch '$branch'. Switch to master to receive updates.${NC}"
        return 1
    fi

    LOCAL=$(git rev-parse master 2>/dev/null)
    REMOTE=$(git rev-parse origin/master 2>/dev/null)
    BASE=$(git merge-base master origin/master 2>/dev/null)

    if [ "$LOCAL" = "$REMOTE" ]; then
        echo -e "${GREEN}Up to date.${NC}"
        return 0
    elif [ "$LOCAL" = "$BASE" ]; then
        # Get remote commit info
        REMOTE_VERSION=$(git log -1 --format="%s" origin/master 2>/dev/null | grep -oP 'v?\d+\.\d+\.\d+' || echo "latest")
        echo -e "${YELLOW}Update available:${NC} $REMOTE_VERSION"
        return 2
    elif [ "$REMOTE" = "$BASE" ]; then
        echo -e "${YELLOW}Local branch is ahead of remote. No updates to pull.${NC}"
        return 0
    else
        echo -e "${RED}Local and remote have diverged. Resolve manually.${NC}"
        return 3
    fi
}

# =========================================================
# Apply git update
# =========================================================

apply_git_update() {
    echo ""
    echo -e "${YELLOW}Pulling latest changes...${NC}"
    git pull origin master || {
        echo -e "${RED}Git pull failed. Check for conflicts.${NC}"
        exit 1
    }
    echo -e "${GREEN}Code updated.${NC}"
}

# =========================================================
# Check version from VERSION file or git tag
# =========================================================

check_version() {
    if [ "$GIT_AVAILABLE" -eq 1 ]; then
        local tag
        tag=$(git ls-remote --tags origin 2>/dev/null | tail -1 | grep -oP 'v?\d+\.\d+\.\d+' || echo "")
        if [ -n "$tag" ]; then
            REMOTE_VERSION="$tag"
            if ver_gt "${tag#v}" "${CURRENT_VERSION#v}"; then
                return 2
            fi
        fi
        check_git
        return $?
    else
        # Without git, try GitHub API
        local api
        api=$(curl -s --max-time 5 "https://api.github.com/repos/j68418730/whm/releases/latest" 2>/dev/null || echo "")
        if [ -n "$api" ]; then
            REMOTE_VERSION=$(echo "$api" | grep -oP '"tag_name":\s*"\K[^"]+' || echo "")
            if [ -n "$REMOTE_VERSION" ]; then
                if ver_gt "${REMOTE_VERSION#v}" "${CURRENT_VERSION#v}"; then
                    return 2
                else
                    echo -e "${GREEN}Up to date.${NC}"
                    return 0
                fi
            fi
        fi
        echo -e "${YELLOW}Cannot check for updates (no git repo, no network).${NC}"
        return 1
    fi
}

# =========================================================
# Apply update without git (download and extract)
# =========================================================

apply_download_update() {
    echo ""
    echo -e "${YELLOW}Downloading latest version...${NC}"
    local tmpdir
    tmpdir=$(mktemp -d)
    cd "$tmpdir"

    curl -sL "https://github.com/j68418730/whm/archive/refs/heads/master.zip" -o update.zip || {
        echo -e "${RED}Download failed.${NC}"
        cd "$SCRIPT_DIR"
        rm -rf "$tmpdir"
        exit 1
    }

    unzip -q update.zip || {
        echo -e "${RED}Extraction failed.${NC}"
        cd "$SCRIPT_DIR"
        rm -rf "$tmpdir"
        exit 1
    }

    cd whm-master

    # Copy files, preserving config
    echo "Updating panel files..."
    rsync -a --delete \
        --exclude='config/database.php' \
        --exclude='config/app.php' \
        --exclude='config/plugins.php' \
        --exclude='.env' \
        --exclude='storage/' \
        --exclude='logs/' \
        ./ "$SCRIPT_DIR/" 2>/dev/null || cp -r ./* "$SCRIPT_DIR/" 2>/dev/null || true

    cd "$SCRIPT_DIR"
    rm -rf "$tmpdir"

    echo -e "${GREEN}Code updated.${NC}"
}

# =========================================================
# Backup current installation
# =========================================================

do_backup() {
    local backup_dir="$SCRIPT_DIR/backups/backup_$(date +%Y%m%d_%H%M%S)"
    mkdir -p "$backup_dir"
    echo "Backing up current files to $backup_dir..."
    rsync -a --exclude='storage/' --exclude='logs/' --exclude='backups/' "$SCRIPT_DIR/" "$backup_dir/" 2>/dev/null || {
        cp -r "$SCRIPT_DIR" "$backup_dir" 2>/dev/null || true
    }
    # Backup database config
    if [ -f "$PANEL_DIR/config/database.php" ]; then
        cp "$PANEL_DIR/config/database.php" "$backup_dir/database.php.backup"
    fi
    echo -e "${GREEN}Backup saved.${NC}"
}

# =========================================================
# Post-update instructions
# =========================================================

post_update() {
    echo ""
    echo "=============================================="
    echo -e "${GREEN} Update Complete${NC}"
    echo "=============================================="
    echo ""
    echo "Version: $CURRENT_VERSION"

    if [ -n "$REMOTE_VERSION" ]; then
        echo "Updated to: $REMOTE_VERSION"
    fi

    echo ""
    echo "Next steps:"
    echo ""
    echo " 1. Update database schema (if needed):"
    echo "    mysql -u radiouser -p radiohosting < database/schema.sql"

    if [ -f plugins/Radio/database/schema.sql ]; then
        echo "    mysql -u radiouser -p radiohosting < plugins/Radio/database/schema.sql"
    fi
    if [ -f plugins/Billing/database/schema.sql ]; then
        echo "    mysql -u radiouser -p radiohosting < plugins/Billing/database/schema.sql"
    fi

    echo ""
    echo " 2. Restart web server:"
    echo "    sudo systemctl restart httpd"
    echo ""
    echo " 3. Clear browser cache and log back in."
    echo ""
    echo "Backup saved to: $SCRIPT_DIR/backups/"
    echo ""
}

# =========================================================
# Main
# =========================================================

clear

echo "=============================================="
echo " Planet Hosts Master Panel Update"
echo "=============================================="
echo ""
echo "Current version: $CURRENT_VERSION"
echo ""

# --check flag: just check, don't update
if [ "$1" = "--check" ]; then
    echo "Checking for updates..."
    check_version
    case $? in
        0) echo -e "${GREEN}No updates available.${NC}" ;;
        2) echo -e "${YELLOW}Update available: $REMOTE_VERSION${NC}" ;;
    esac
    exit 0
fi

# Check for updates
echo "Checking for updates..."
check_version
STATUS=$?

case $STATUS in
    0)
        echo ""
        echo -e "${GREEN}Your panel is up to date (v$CURRENT_VERSION).${NC}"
        echo ""
        exit 0
        ;;
    1)
        echo ""
        echo -e "${YELLOW}Skipping update check.${NC}"
        echo ""
        ;;
    2)
        echo ""
        echo -e "${YELLOW}Update available!${NC}"
        echo -e "  Current: ${CYAN}v$CURRENT_VERSION${NC}"
        echo -e "  Latest:  ${CYAN}$REMOTE_VERSION${NC}"
        echo ""
        ;;
    3)
        echo ""
        echo -e "${RED}Branches diverged. Cannot update automatically.${NC}"
        exit 1
        ;;
esac

# Ask to proceed
if [ $STATUS -eq 2 ]; then
    read -p "Apply update? (y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo "Update cancelled."
        exit 0
    fi

    # Backup
    echo ""
    read -p "Create backup before updating? (Y/n): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Nn]$ ]]; then
        do_backup
    fi

    # Apply
    if [ "$GIT_AVAILABLE" -eq 1 ]; then
        apply_git_update
    else
        apply_download_update
    fi

    # Update version file
    if [ -n "$REMOTE_VERSION" ]; then
        echo "$REMOTE_VERSION" > "$VERSION_FILE"
        CURRENT_VERSION="$REMOTE_VERSION"
    fi

    post_update

else
    read -p "Force re-apply current version? (y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo "Cancelled."
        exit 0
    fi

    do_backup
    if [ "$GIT_AVAILABLE" -eq 1 ]; then
        git pull origin master || true
    fi
    post_update
fi

echo ""
echo "Done."
