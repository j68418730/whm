#!/bin/bash

# =========================================================
# Planet Hosts Master Panel - install2.sh
# AlmaLinux 9 Icecast/FFmpeg Dependency Installer
# =========================================================

set -eo pipefail

clear

echo "=================================================="
echo " Planet Hosts Master Panel - install2.sh"
echo " AlmaLinux 9 Dependency Installer"
echo "=================================================="
echo ""

# =========================================================
# Install Repositories
# =========================================================

echo "[1/10] Installing EPEL..."

sudo dnf install -y epel-release

echo ""
echo "[2/10] Enabling CRB..."

sudo dnf install -y dnf-plugins-core
sudo dnf config-manager --set-enabled crb

echo ""
echo "[3/10] Installing RPM Fusion..."

sudo dnf install -y \
https://download1.rpmfusion.org/free/el/rpmfusion-free-release-9.noarch.rpm

echo ""
echo "[4/10] Refreshing repositories..."

sudo dnf clean all
sudo dnf makecache

# =========================================================
# Check Icecast Availability
# =========================================================

echo ""
echo "[5/10] Checking for Icecast package..."

ICECAST_FOUND=$(dnf search icecast 2>/dev/null | grep -i "icecast.x86_64" || true)

if [[ -n "$ICECAST_FOUND" ]]; then

    echo ""
    echo "Icecast package found."
    echo ""
    echo "Installing Icecast..."

    sudo dnf install -y icecast

else

    echo ""
    echo "=================================================="
    echo " Icecast package not found in repositories."
    echo " Switching to source compilation..."
    echo "=================================================="
    echo ""

    # =====================================================
    # Development Tools
    # =====================================================

    echo "[6/10] Installing Development Tools..."

    sudo dnf groupinstall -y "Development Tools"

    # =====================================================
    # Dependencies
    # =====================================================

    echo ""
    echo "[7/10] Installing build dependencies..."

    sudo dnf install -y \
    pkgconf-pkg-config \
    glib2-devel \
    libxml2-devel \
    libxslt-devel \
    libshout-devel \
    libvorbis-devel \
    libtheora-devel \
    speex-devel \
    opus-devel \
    curl-devel \
    openssl-devel \
    sqlite-devel \
    git \
    gcc \
    gcc-c++ \
    make \
    automake \
    autoconf \
    libtool

    # =====================================================
    # Install libigloo
    # =====================================================

    echo ""
    echo "[8/10] Installing libigloo..."

    cd /usr/local/src

    rm -rf igloo

    git clone https://github.com/xiph/igloo.git

    cd igloo

    autoreconf -fi

    ./configure

    make -j$(nproc)

    sudo make install

    sudo ldconfig

    export PKG_CONFIG_PATH=/usr/local/lib/pkgconfig:$PKG_CONFIG_PATH

    echo ""
    echo "Installed libigloo successfully."

    echo ""
    echo "Installed igloo version:"
    pkg-config --modversion igloo || true

    # =====================================================
    # Build Icecast from Source
    # =====================================================

    echo ""
    echo "[9/10] Building Icecast from source..."

    if [ -f /var/www/radiohosting/scripts/icecast_install_source.sh ]; then

        chmod +x /var/www/radiohosting/scripts/icecast_install_source.sh

        sudo bash /var/www/radiohosting/scripts/icecast_install_source.sh

    else

        echo ""
        echo "ERROR:"
        echo "/var/www/radiohosting/scripts/icecast_install_source.sh not found."
        echo ""

        exit 1

    fi

fi

# =========================================================
# Continue Main Installer
# =========================================================

echo ""
echo "[10/10] Continuing main panel installer..."

if [ -f ./install.sh ]; then

    chmod +x ./install.sh

    sudo ./install.sh

else

    echo ""
    echo "ERROR: install.sh not found in current directory."
    echo ""

    exit 1

fi

echo ""
echo "=================================================="
echo " Installation Complete"
echo "=================================================="
