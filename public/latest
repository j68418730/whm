#!/bin/bash
# ============================================================================
# Planet Hosts — Official Installer Bootstrap ("latest")
#
#   Usage (as root on a fresh supported server):
#       cd /home && curl -o latest -L https://planet-hosts.com/latest && sh latest
#
# What it does:
#   1. Detects the OS from /etc/os-release.
#   2. Fetches the current release manifest (VERSION.json) from GitHub.
#   3. Downloads the official release tarball (kept on GitHub).
#   4. Extracts it and runs the correct installer for this OS.
#
# Supported now:  Debian / Ubuntu
# RHEL family:    AlmaLinux / Rocky / CentOS / Fedora  (via installer/install.sh)
# Later:          FreeBSD
# ============================================================================
set -e

BASE_URL="https://raw.githubusercontent.com/j68418730/whm/master"
LATEST_URL="${LATEST_URL:-$BASE_URL/VERSION.json}"
WORKDIR="${WORKDIR:-/home}"
LOG_FILE="$WORKDIR/planet-hosts-install.log"

log() { echo "$(date '+%H:%M:%S') | $1"; echo "$(date '+%Y-%m-%d %H:%M:%S') | $1" >> "$LOG_FILE"; }

# --- must be root ---
if [ "$(id -u)" != "0" ]; then
    echo "ERROR: Planet Hosts installer must run as root (or via sudo)."
    echo "  Try: sudo sh latest   or   sudo -i && sh latest"
    exit 1
fi

# --- OS detection ---
detect_os() {
    if [ -f /etc/os-release ]; then
        . /etc/os-release
    fi
    ID="${ID:-linux}"
    ID_LIKE="${ID_LIKE:-}"
    case "$ID" in
        debian|ubuntu) OS_FAMILY="debian";;
        almalinux|rocky|centos|rhel|fedora) OS_FAMILY="rhel";;
        freebsd) OS_FAMILY="freebsd";;
        *) OS_FAMILY="unsupported";;
    esac
}
detect_os

echo "==========================================================="
echo " Planet Hosts Panel — Installer"
echo " OS: ${PRETTY_NAME:-$ID}"
echo " Family: $OS_FAMILY"
echo "==========================================================="

if [ "$OS_FAMILY" = "unsupported" ]; then
    echo "ERROR: $ID is not yet supported."
    echo "Supported: Debian, Ubuntu, AlmaLinux, Rocky, CentOS, Fedora (FreeBSD coming later)."
    exit 1
fi
if [ "$OS_FAMILY" = "freebsd" ]; then
    echo "FreeBSD support is planned for a later update. Use Debian/AlmaLinux for now."
    exit 1
fi

# --- Fetch release manifest (gets the exact tarball URL + checksum) ---
log "Fetching release manifest from $LATEST_URL"
if ! curl -fsSL -m 30 -o "$WORKDIR/planet-hosts-version.json" "$LATEST_URL"; then
    echo "ERROR: Could not reach release manifest at $LATEST_URL"
    exit 1
fi
# No jq dependency — parse with sed
DOWNLOAD_URL=$(sed -n 's/.*"download": *"\([^"]*\)".*/\1/p' "$WORKDIR/planet-hosts-version.json" | head -1)
TARGET_SHA=$(sed -n 's/.*"target_sha": *"\([^"]*\)".*/\1/p' "$WORKDIR/planet-hosts-version.json" | head -1)
EXPECTED_SHA256=$(sed -n 's/.*"checksum": *"\([^"]*\)".*/\1/p' "$WORKDIR/planet-hosts-version.json" | head -1)
VERSION=$(sed -n 's/.*"version": *"\([^"]*\)".*/\1/p' "$WORKDIR/planet-hosts-version.json" | head -1)

if [ -z "$DOWNLOAD_URL" ]; then
    echo "ERROR: Release manifest is invalid (no download URL)."
    exit 1
fi

# --- Download the release tarball (kept on GitHub, not the server) ---
TARBALL="$WORKDIR/planet-hosts-$VERSION.tar.gz"
EXTRACT_DIR="$WORKDIR/planet-hosts-src"

log "Downloading Planet Hosts $VERSION package..."
if ! curl -fsSL -m 600 -o "$TARBALL" "$DOWNLOAD_URL"; then
    echo "ERROR: Package download failed from $DOWNLOAD_URL"
    exit 1
fi
echo "Downloaded $(du -h "$TARBALL" | cut -f1)."

# --- Verify checksum (if the manifest provides one) ---
if [ -n "$EXPECTED_SHA256" ]; then
    ACTUAL=$(sha256sum "$TARBALL" | awk '{print $1}')
    if [ "$ACTUAL" != "$EXPECTED_SHA256" ]; then
        echo "ERROR: Checksum mismatch. Expected $EXPECTED_SHA256, got $ACTUAL."
        echo "The package is not trusted. Aborting."
        exit 1
    fi
    log "Checksum verified: $ACTUAL"
fi

# --- Extract ---
rm -rf "$EXTRACT_DIR"
mkdir -p "$EXTRACT_DIR"
log "Extracting package..."
tar xzf "$TARBALL" -C "$EXTRACT_DIR" --strip-components=1
if [ ! -f "$EXTRACT_DIR/install_debian.sh" ] && [ ! -f "$EXTRACT_DIR/installer/install.sh" ]; then
    echo "ERROR: Extracted package is missing the installer scripts."
    exit 1
fi

log "Extracted to $EXTRACT_DIR"

# --- Run the correct installer ---
cd "$EXTRACT_DIR"
case "$OS_FAMILY" in
    debian)
        log "Running install_debian.sh (Debian/Ubuntu)..."
        bash install_debian.sh
        ;;
    rhel)
        log "Running installer/install.sh (AlmaLinux/Rocky/CentOS/Fedora)..."
        bash installer/install.sh
        ;;
esac

echo "==========================================================="
echo " Planet Hosts installation complete."
echo " Log: $LOG_FILE"
echo "==========================================================="
exit 0