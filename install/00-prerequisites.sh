#!/bin/bash
# 00-prerequisites — common packages + directory setup
set -e
DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$DIR/lib.sh"

sc_log "prerequisites" "install" "RUNNING" "Installing prerequisites"
export DEBIAN_FRONTEND=noninteractive
$PKG_UPDATE
$PKG_INSTALL curl wget git unzip gzip tar cron mailutils 2>/dev/null || \
    $PKG_INSTALL curl wget git unzip gzip tar cron

mkdir -p /var/log/planethosts /var/www/radiohosting/storage/security /usr/local/share/planethosts-security
sc_status prerequisites ok "packages ready"
sc_log "prerequisites" "install" "OK" "Prerequisites installed"
