#!/bin/bash
# =========================================================
# Planet Hosts - PHP Module
# =========================================================

set -eo pipefail

LOG_DIR="/var/log/planethosts"

log() { local m="$1"; echo "$(date '+%Y-%m-%d %H:%M:%S') | PHP | $m" >> "$LOG_DIR/php.log"; echo "[PHP] $m"; }

install_php() {
    local version="${1:-8.2}"
    log "Installing PHP $version..."
    dnf install -y \
        php php-cli php-common php-curl php-gd php-intl php-mbstring \
        php-mysqlnd php-pdo php-process php-xml php-zip php-bcmath php-bz2 \
        php-calendar php-ctype php-exif php-fileinfo php-ftp php-gettext \
        php-imap php-ldap php-opcache php-pear php-redis php-shmop \
        php-sockets php-sodium php-sysvmsg php-sysvsem php-sysvshm \
        php-tokenizer php-wddx php-xmlreader php-xmlwriter php-xsl \
        php-pecl-apcu php-pecl-imagick || true
    log "PHP $version installed."
}

configure_php() {
    local ini_file="/etc/php.ini"
    log "Configuring PHP..."
    sed -i 's/^max_execution_time.*/max_execution_time = 300/' "$ini_file"
    sed -i 's/^max_input_time.*/max_input_time = 300/' "$ini_file"
    sed -i 's/^memory_limit.*/memory_limit = 256M/' "$ini_file"
    sed -i 's/^post_max_size.*/post_max_size = 128M/' "$ini_file"
    sed -i 's/^upload_max_filesize.*/upload_max_filesize = 128M/' "$ini_file"
    log "PHP configured."
}

case "${1:-install}" in
    install) install_php "$2" ;;
    configure) configure_php ;;
    *) echo "Usage: $0 {install|configure}" ;;
esac
