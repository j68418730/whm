#!/bin/bash
# =========================================================
# Planet Hosts - FTP Module
# =========================================================

set -eo pipefail

LOG_DIR="/var/log/planethosts"

log() { local m="$1"; echo "$(date '+%Y-%m-%d %H:%M:%S') | FTP | $m" >> "$LOG_DIR/ftp.log"; echo "[FTP] $m"; }

install_vsftpd() {
    log "Installing vsftpd..."
    dnf install -y vsftpd || yum install -y vsftpd
    systemctl enable --now vsftpd
    log "vsftpd installed."
}

install_pureftpd() {
    log "Installing Pure-FTPd..."
    dnf install -y pure-ftpd || yum install -y pure-ftpd
    systemctl enable --now pure-ftpd
    log "Pure-FTPd installed."
}

configure_vsftpd() {
    log "Configuring vsftpd..."
    cat > /etc/vsftpd/vsftpd.conf <<EOF
anonymous_enable=NO
local_enable=YES
write_enable=YES
local_umask=022
dirmessage_enable=YES
xferlog_enable=YES
connect_from_port_20=YES
chroot_local_user=YES
allow_writeable_chroot=YES
pasv_enable=YES
pasv_min_port=30000
pasv_max_port=31000
EOF
    systemctl restart vsftpd
    log "vsftpd configured."
}

case "${1:-install}" in
    install) install_vsftpd; configure_vsftpd ;;
    install-vsftpd) install_vsftpd; configure_vsftpd ;;
    install-pureftpd) install_pureftpd ;;
    configure) configure_vsftpd ;;
    *) echo "Usage: $0 {install|install-vsftpd|install-pureftpd|configure}" ;;
esac
