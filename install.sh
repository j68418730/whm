#!/bin/bash
# =========================================================
# Planet Hosts Master Panel - Installer Entry Point
# AlmaLinux 9 / RHEL 9 / RockyLinux 9
# =========================================================
# Downloads and runs the modular installer from the
# installer/ directory.

set -eo pipefail

INSTALLER_VERSION="1.0.0"
INSTALLER_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/installer"

if [ "$EUID" -ne 0 ]; then
    echo "Please run as root or sudo."
    exit 1
fi

clear
echo "=================================================="
echo " Planet Hosts Master Panel Installer v$INSTALLER_VERSION"
echo " AlmaLinux 9 / RHEL-like"
echo "=================================================="
echo ""

if [ ! -d "$INSTALLER_DIR" ]; then
    echo "ERROR: installer/ directory not found."
    echo "Please ensure the full package is downloaded."
    exit 1
fi

if [ ! -f "$INSTALLER_DIR/install.sh" ]; then
    echo "ERROR: installer/install.sh not found."
    exit 1
fi

echo "Starting modular installer..."
echo ""

bash "$INSTALLER_DIR/install.sh" "$@"

exit $?
