#!/bin/bash
# =========================================================
# Planet Hosts - MariaDB Module
# =========================================================

set -eo pipefail

LOG_DIR="/var/log/planethosts"

log() { local m="$1"; echo "$(date '+%Y-%m-%d %H:%M:%S') | MARIADB | $m" >> "$LOG_DIR/mariadb.log"; echo "[MARIADB] $m"; }

install_mariadb() {
    log "Installing MariaDB..."
    dnf install -y mariadb-server mariadb || yum install -y mariadb-server mariadb
    systemctl enable --now mariadb
    log "MariaDB installed and started."
}

create_database() {
    local db="${1:-radiohosting}" user="${2:-radiouser}" pass="$3"
    if [ -z "$pass" ]; then pass=$(openssl rand -base64 12); fi
    log "Creating database: $db"
    mysql -u root -e "CREATE DATABASE IF NOT EXISTS \`$db\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    mysql -u root -e "CREATE USER IF NOT EXISTS '$user'@'localhost' IDENTIFIED BY '$pass';"
    mysql -u root -e "GRANT ALL PRIVILEGES ON \`$db\`.* TO '$user'@'localhost';"
    mysql -u root -e "FLUSH PRIVILEGES;"
    echo "$pass"
    log "Database $db created with user $user."
}

import_schema() {
    local db="${1:-radiohosting}" schema="$2"
    if [ -f "$schema" ]; then
        mysql -u root "$db" < "$schema"
        log "Schema imported: $schema"
    fi
}

secure_installation() {
    log "Securing MariaDB..."
    mysql -u root -e "DELETE FROM mysql.user WHERE User='';"
    mysql -u root -e "DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');"
    mysql -u root -e "DROP DATABASE IF EXISTS test;"
    mysql -u root -e "FLUSH PRIVILEGES;"
    log "MariaDB secured."
}

case "${1:-install}" in
    install) install_mariadb ;;
    create-db) create_database "$2" "$3" "$4" ;;
    import) import_schema "$2" "$3" ;;
    secure) secure_installation ;;
    *) echo "Usage: $0 {install|create-db|import|secure}" ;;
esac
