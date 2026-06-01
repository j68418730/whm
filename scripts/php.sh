
# =========================================================
# PHP Extension Installer
# AlmaLinux 9 / RockyLinux 9 / RHEL 9
# =========================================================

set -eo pipefail

clear

echo "=================================================="
echo " Installing PHP Extensions"
echo "=================================================="
echo ""

# ---------------------------------------------------------
# Detect package manager
# ---------------------------------------------------------

PKG="yum"

if command -v dnf >/dev/null 2>&1; then
    PKG="dnf"
fi

# ---------------------------------------------------------
# Install EPEL (optional but useful)
# ---------------------------------------------------------

echo "[1/4] Installing repositories..."

sudo $PKG install -y epel-release || true

# ---------------------------------------------------------
# Install PHP + Extensions
# ---------------------------------------------------------

echo ""
echo "[2/4] Installing PHP packages..."

sudo $PKG install -y \
php \
php-cli \
php-common \
php-bcmath \
php-curl \
php-devel \
php-fpm \
php-gd \
php-intl \
php-mbstring \
php-mysqlnd \
php-opcache \
php-pdo \
php-process \
php-soap \
php-xml \
php-xmlrpc \
php-zip \
php-json

# ---------------------------------------------------------
# Restart Apache
# ---------------------------------------------------------

echo ""
echo "[3/4] Restarting Apache..."

sudo systemctl restart httpd || true

# ---------------------------------------------------------
# Verify Installed Modules
# ---------------------------------------------------------

echo ""
echo "[4/4] Installed PHP Modules:"
echo ""

php -m | sort

echo ""
echo "=================================================="
echo " PHP Installation Complete"
echo "=================================================="
echo "sudo systemctl restart httpd"
