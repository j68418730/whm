#!/bin/bash
# icecast2.sh
# Icecast 2 Installer for AlmaLinux 10

set -e

echo "====================================="
echo " Icecast 2 Installer for AlmaLinux "
echo "====================================="
echo ""

# Check for root
if [ "$EUID" -ne 0 ]; then
    echo "Please run as root or with sudo."
    exit 1
fi

echo "[1/8] Updating system..."
yum update -y

echo "[2/8] Installing build tools and dependencies..."
yum groupinstall "Development Tools" -y

yum install -y \
    gcc \
    gcc-c++ \
    make \
    autoconf \
    automake \
    libtool \
    wget \
    tar \
    curl \
    libxml2-devel \
    curl-devel \
    openssl-devel \
    libxslt-devel \
    pcre2-devel \
    libvorbis-devel \
    sqlite-devel \
    firewalld

echo "[3/8] Starting firewall..."
systemctl enable firewalld
systemctl start firewalld

echo "[4/8] Downloading Icecast..."
cd /usr/local/src

ICECAST_VERSION="2.5.0"
ICECAST_FILE="icecast-${ICECAST_VERSION}.tar.gz"

wget -O $ICECAST_FILE \
https://downloads.xiph.org/releases/icecast/$ICECAST_FILE

echo "[5/8] Extracting source..."
tar -xzf $ICECAST_FILE

cd icecast-${ICECAST_VERSION}

echo "[6/8] Compiling Icecast..."
./configure
make -j$(nproc)

echo "[7/8] Installing Icecast..."
make install

echo "[8/8] Configuring Icecast..."

mkdir -p /usr/local/etc/icecast

cp conf/icecast.xml.dist \
/usr/local/etc/icecast/icecast.xml

# Set passwords
SOURCEPASS=$(openssl rand -hex 8)
ADMINPASS=$(openssl rand -hex 8)
RELAYPASS=$(openssl rand -hex 8)

sed -i "s/hackme/$SOURCEPASS/g" \
/usr/local/etc/icecast/icecast.xml

# Create icecast user
useradd -r -s /sbin/nologin icecast || true

mkdir -p /usr/local/var/log/icecast
mkdir -p /usr/local/var/run/icecast

chown -R icecast:icecast /usr/local/var/log/icecast
chown -R icecast:icecast /usr/local/var/run/icecast

# Create systemd service
cat > /etc/systemd/system/icecast.service <<EOF
[Unit]
Description=Icecast Streaming Media Server
After=network.target

[Service]
Type=simple
User=icecast
Group=icecast
ExecStart=/usr/local/bin/icecast -c /usr/local/etc/icecast/icecast.xml
Restart=always

[Install]
WantedBy=multi-user.target
EOF

# Reload systemd
systemctl daemon-reload

# Enable and start service
systemctl enable icecast
systemctl start icecast

# Open firewall port
firewall-cmd --permanent --add-port=8000/tcp
firewall-cmd --reload

SERVER_IP=$(hostname -I | awk '{print $1}')

echo ""
echo "====================================="
echo " Icecast Installation Complete "
echo "====================================="
echo ""
echo "Server URL:"
echo "http://$SERVER_IP:8000"
echo ""
echo "Source Password: $SOURCEPASS"
echo "Relay Password : $RELAYPASS"
echo "Admin Password : $ADMINPASS"
echo ""
echo "Icecast service status:"
echo "systemctl status icecast"
echo ""
echo "Restart Icecast:"
echo "systemctl restart icecast"
echo ""
echo "Config file:"
echo "/usr/local/etc/icecast/icecast.xml"
echo ""
