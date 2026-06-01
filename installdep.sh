nano install2.sh

````

Paste everything below into the file:

```bash
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

echo "[1/9] Installing EPEL..."

sudo dnf install -y epel-release

echo ""
echo "[2/9] Installing DNF plugins..."

sudo dnf install -y dnf-plugins-core

echo ""
echo "[3/9] Enabling CRB..."

sudo dnf config-manager --set-enabled crb

echo ""
echo "[4/9] Installing RPM Fusion..."

sudo dnf install -y \
https://download1.rpmfusion.org/free/el/rpmfusion-free-release-9.noarch.rpm

echo ""
echo "[5/9] Refreshing repositories..."

sudo dnf clean all
sudo dnf makecache

# =========================================================
# Install Icecast
# =========================================================

echo ""
echo "[6/9] Checking for Icecast package..."

ICECAST_FOUND=$(dnf search icecast 2>/dev/null | grep -i "icecast.x86_64" || true)

if [[ -n "$ICECAST_FOUND" ]]; then

    echo ""
    echo "Icecast package found."

    sudo dnf install -y icecast

else

    echo ""
    echo "Icecast package NOT found."
    echo "Installing build dependencies instead..."

    sudo dnf groupinstall -y "Development Tools"

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
    autoconf-archive \
    m4 \
    gettext \
    gettext-devel \
    git \
    gcc \
    gcc-c++ \
    make \
    automake \
    autoconf \
    libtool

    echo ""
    echo "Dependencies installed."
    echo ""
    echo "NOTE:"
    echo "Compile Icecast manually or use Docker."
    echo ""

fi

# =========================================================
# Install FFmpeg
# =========================================================

echo ""
echo "[7/9] Installing FFmpeg..."

sudo dnf install -y ffmpeg ffmpeg-devel || true

# =========================================================
# Continue Main Installer
# =========================================================

echo ""
echo "[8/9] Continuing main panel installer..."

if [ -f ./install.sh ]; then

    chmod +x ./install.sh

    sudo ./install.sh

else

    echo ""
    echo "ERROR: install.sh not found in current directory."
    echo ""

    exit 1

fi

# =========================================================
# Done
# =========================================================

echo ""
echo "[9/9] Installation Complete"
echo ""
echo "=================================================="
echo " Planet Hosts Installation Finished"
echo "=================================================="

