#!/bin/bash
# =========================================================
# Planet Hosts - Email Module
# =========================================================

set -eo pipefail

LOG_DIR="/var/log/planethosts"

log() { local m="$1"; echo "$(date '+%Y-%m-%d %H:%M:%S') | EMAIL | $m" >> "$LOG_DIR/email.log"; echo "[EMAIL] $m"; }

install_postfix() {
    log "Installing Postfix..."
    dnf install -y postfix mailx || yum install -y postfix mailx
    systemctl enable --now postfix
    log "Postfix installed."
}

install_dovecot() {
    log "Installing Dovecot..."
    dnf install -y dovecot dovecot-mysql || yum install -y dovecot dovecot-mysql
    systemctl enable --now dovecot
    log "Dovecot installed."
}

configure_postfix() {
    local hostname="${1:-$(hostname -f)}"
    log "Configuring Postfix for $hostname..."
    postconf -e "myhostname = $hostname"
    postconf -e "mydomain = ${hostname#*.}"
    postconf -e "myorigin = \$mydomain"
    postconf -e "inet_interfaces = all"
    postconf -e "mydestination = \$myhostname, localhost.\$mydomain, localhost, \$mydomain"
    postconf -e "home_mailbox = Maildir/"
    systemctl restart postfix
    log "Postfix configured."
}

configure_dovecot() {
    log "Configuring Dovecot..."
    sed -i 's/^!include conf.d\/10-mail.conf/#!include conf.d\/10-mail.conf/' /etc/dovecot/dovecot.conf 2>/dev/null || true
    echo "mail_location = maildir:~/Maildir" >> /etc/dovecot/conf.d/10-mail.conf
    systemctl restart dovecot
    log "Dovecot configured."
}

case "${1:-install}" in
    install)
        install_postfix
        install_dovecot
        configure_postfix "$2"
        configure_dovecot
        ;;
    install-postfix) install_postfix; configure_postfix "$2" ;;
    install-dovecot) install_dovecot; configure_dovecot ;;
    configure) configure_postfix "$2"; configure_dovecot ;;
    *) echo "Usage: $0 {install|install-postfix|install-dovecot|configure}" ;;
esac
