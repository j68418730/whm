#!/bin/bash
# Planet Hosts - Complete Uninstaller
# Reverses EVERYTHING install_debian.sh set up so you can test a fresh install
# again WITHOUT reinstalling the OS.
#
# Usage:
#   bash uninstall_debian.sh              # interactive (asks for confirmation)
#   bash uninstall_debian.sh --force      # non-interactive (no prompt)
#   bash uninstall_debian.sh --force --keep-packages  # keep apt packages, remove only config/state
#   bash uninstall_debian.sh --dry-run    # show what WOULD be removed, change nothing
#
# The default (full) mode also purges every package the installer installed
# (apache2, mariadb, php* , icecast2, ...) returning the box to near-fresh OS
# state. Use --keep-packages to keep software but wipe all Planet Hosts config,
# databases, users, and services.

set -u

INSTALLER_VERSION="1.1.0"
START_TIME=$(date +%s)
LOG_DIR="/var/log/planethosts"

DRY_RUN=0
FORCE=0
KEEP_PACKAGES=0
PANEL_DIR="/var/www/Planethostpanel"

log() {
    local status="$1" msg="$2"
    local ts=$(date '+%Y-%m-%d %H:%M:%S')
    mkdir -p "$LOG_DIR" 2>/dev/null || true
    echo "$ts | $status | $msg" >> "$LOG_DIR/uninstall.log" 2>/dev/null || true
    echo "[$status] $msg"
}

run() {
    if [ "$DRY_RUN" -eq 1 ]; then
        echo "  [dry-run] $*"
        return 0
    fi
    "$@" >/dev/null 2>&1
}

for arg in "$@"; do
    case "$arg" in
        --dry-run) DRY_RUN=1;;
        --force)   FORCE=1;;
        --keep-packages) KEEP_PACKAGES=1;;
        *) echo "Unknown option: $arg"; exit 2;;
    esac
done

if [ "$(id -u)" -ne 0 ]; then
    echo "Run as root."; exit 1
fi

echo ""
echo "=============================================="
echo "  Planet Hosts - Complete Uninstaller v$INSTALLER_VERSION"
echo "  Panel dir : $PANEL_DIR"
echo "  Mode      : $([ "$DRY_RUN" -eq 1 ] && echo DRY-RUN || echo LIVE)"
echo "  Packages  : $([ "$KEEP_PACKAGES" -eq 1 ] && echo KEEP || echo PURGE)"
echo "=============================================="
echo ""

if [ "$DRY_RUN" -eq 0 ] && [ "$FORCE" -ne 1 ]; then
    echo "WARNING: This removes the Planet Hosts panel, databases,"
    echo "streaming servers, systemd services, and ALMOST every package."
    echo "This is destructive and cannot be undone."
    echo ""
    read -r -p "Type 'UNINSTALL' to continue: " CONFIRM
    if [ "$CONFIRM" != "UNINSTALL" ]; then
        echo "Aborted."; exit 1
    fi
fi

if [ "$DRY_RUN" -eq 1 ]; then
    log "MODE" "DRY-RUN - no changes will be made"
else
    log "START" "Uninstaller started (keep-packages=$KEEP_PACKAGES)"
fi

# ============================================================
# 1. Stop + disable systemd units/timers
# ============================================================
log "SYSTEMD" "Stopping Planet Hosts services"
for unit in \
    planet-support \
    planet-security.timer planet-security.service \
    planet-quota.timer planet-quota.service \
    planet-monitor.timer planet-monitor.service \
    planet-backup.timer planet-backup.service \
    ph-dj-listener planet-push \
    liquidsoap shoutcast-v1 shoutcast \
    ; do
    [ -z "$unit" ] && continue
    name="$unit"
    run systemctl disable --now "$name" 2>/dev/null || true
    run systemctl stop "$name" 2>/dev/null || true
done

# ============================================================
# 2. Remove Planet Hosts systemd unit files
# ============================================================
log "SYSTEMD" "Removing unit files"
for f in \
    /etc/systemd/system/planet-support.service \
    /etc/systemd/system/planet-security.timer /etc/systemd/system/planet-security.service \
    /etc/systemd/system/planet-quota.timer /etc/systemd/system/planet-quota.service \
    /etc/systemd/system/planet-monitor.timer /etc/systemd/system/planet-monitor.service \
    /etc/systemd/system/planet-backup.timer /etc/systemd/system/planet-backup.service \
    /etc/systemd/system/ph-dj-listener.service \
    /etc/systemd/system/planet-push.service \
    /etc/systemd/system/liquidsoap.service \
    /etc/systemd/system/shoutcast-v1.service \
    /etc/systemd/system/shoutcast.service \
    /etc/systemd/journald.conf.d/planet-hosts.conf ; do
    [ -f "$f" ] && { run rm -f "$f"; log "SYSTEMD" "removed $f"; }
done
run systemctl daemon-reload

# ============================================================
# 3. Apache vhosts + module config
# ============================================================
log "APACHE" "Disabling panel vhosts"
for s in radiohosting panel-ports remote.planet-hosts.com dj-panel chat-panel; do
    run a2dissite -q "$s" 2>/dev/null || true
    [ -f "/etc/apache2/sites-available/$s.conf" ] && { run rm -f "/etc/apache2/sites-available/$s.conf"; log "APACHE" "removed $s.conf"; }
done
[ -f /etc/apache2/sites-available/dj-panel.conf ]   && { run rm -f /etc/apache2/sites-available/dj-panel.conf; }
[ -f /etc/apache2/sites-available/chat-panel.conf ] && { run rm -f /etc/apache2/sites-available/chat-panel.conf; }
[ -f /etc/apache2/conf-enabled/proxy-signalr.conf ] && { run rm -f /etc/apache2/conf-enabled/proxy-signalr.conf; log "APACHE" "removed proxy-signalr.conf"; }
run a2dismod -q security2 proxy proxy_http proxy_wstunnel 2>/dev/null || true
run a2dissite -q 000-default 2>/dev/null || true
# Listen ports added by installer
if [ -f /etc/apache2/ports.conf ]; then
    for port in 2082 2086 2087 2089 2096 2100 2101; do
        sed -i "/^Listen $port$/d" /etc/apache2/ports.conf
    done
    log "APACHE" "removed extra Listen ports from ports.conf"
fi
run systemctl reload apache2 2>/dev/null || true

# ============================================================
# 4. nginx proxy (port 8080)
# ============================================================
log "NGINX" "Removing planet-proxy site"
run rm -f /etc/nginx/sites-enabled/planet-proxy /etc/nginx/sites-available/planet-proxy 2>/dev/null || true
run systemctl stop nginx 2>/dev/null || true
run systemctl disable nginx 2>/dev/null || true
log "NGINX" "nginx stopped and disabled (it was installed purely as the 8080 reverse proxy)"

# ============================================================
# 5. Config files (rsyslog, logrotate, fail2ban, iptables, sudoers, cron)
# ============================================================
log "CONFIG" "Removing config files"
for f in \
    /etc/rsyslog.d/40-shoutcast.conf \
    /etc/logrotate.d/syslog-rotate \
    /etc/logrotate.d/shoutcast \
    /etc/logrotate.d/planethosts \
    /etc/fail2ban/jail.local \
    /etc/iptables.rules \
    /etc/sudoers.d/www-data-firewall \
    /etc/sudoers.d/www-data-radio \
    /etc/sudoers.d/radiohosting-terminal \
    /etc/sudoers.d/radiohosting-update \
    /etc/cron.d/planet-hosts-automation \
    /etc/cron.d/planet-hosts-updates \
    /etc/cron.d/planet-hosts-autossl \
    /etc/apt/sources.list.d/sury-php.list \
    /etc/apt/trusted.gpg.d/php-archive-keyring.gpg ; do
    [ -f "$f" ] && { run rm -f "$f"; log "CONFIG" "removed $f"; }
done
run systemctl restart rsyslog fail2ban 2>/dev/null || true

# ============================================================
# 6. App + data directories
# ============================================================
log "FILES" "Removing application and data directories"
for d in \
    "$PANEL_DIR" \
    /opt/planethosts \
    /opt/PlanetHosts.Support.Server \
    /usr/share/modsecurity-crs \
    /etc/liquidsoap \
    /home/gameservers ; do
    [ -d "$d" ] && { run rm -rf "$d"; log "FILES" "removed $d"; }
done
[ -f /swapfile ] && { run swapoff /swapfile 2>/dev/null || true; run rm -f /swapfile; sed -i '\|^/swapfile|d' /etc/fstab; log "FILES" "removed swapfile + fstab entry"; }
run rm -rf /var/log/shoutcast /var/log/liquidsoap /var/log/radiohosting /var/log/planethosts $PANEL_DIR 2>/dev/null || true

# ============================================================
# 7. Firewall: remove every port the installer opened
# ============================================================
log "FIREWALL" "Removing Planet Hosts firewall ports"
if command -v firewall-cmd >/dev/null 2>&1; then
    for p in 80/tcp 443/tcp 8080/tcp 8443/tcp \
        20/tcp 21/tcp 22/tcp 26/tcp 990/tcp \
        2082/tcp 2083/tcp 2086/tcp 2087/tcp 2089/tcp 2096/tcp 2097/tcp 2100/tcp 2101/tcp \
        53/tcp 53/udp \
        25/tcp 465/tcp 587/tcp 110/tcp 143/tcp 993/tcp 995/tcp 4190/tcp \
        3306/tcp 5000/tcp 5001/tcp \
        8000/tcp 8001/tcp 8002/tcp 8004/tcp 8081/tcp \
        10000-10999/tcp 11000-11999/tcp 12000-13999/tcp 14000-15999/tcp \
        16000-16499/tcp 17000-17999/tcp 18000-18999/tcp 19000-19999/tcp \
        20000-20999/tcp 25560-25660/tcp 27000-28000/tcp 30000-50000/tcp \
        50000-55000/udp ; do
        run firewall-cmd --permanent --remove-port="$p" 2>/dev/null || true
    done
    run firewall-cmd --reload 2>/dev/null || true
    log "FIREWALL" "Planet Hosts ports removed"
fi

# ============================================================
# 8. Database + users
# ============================================================
log "DATABASE" "Dropping radiohosting database and radiouser"
if command -v mysql >/dev/null 2>&1; then
    run mysql -u root -e "DROP DATABASE IF EXISTS radiohosting;" 2>/dev/null || true
    run mysql -u root -e "DROP USER IF EXISTS 'radiouser'@'localhost';" 2>/dev/null || true
    run mysql -u root -e "FLUSH PRIVILEGES;" 2>/dev/null || true
    # Restore root unix_socket auth so reinstall behaves like a fresh box
    run mysql -u root -e "ALTER USER 'root'@'localhost' IDENTIFIED VIA unix_socket;" 2>/dev/null || true
    run mysql -u root -e "FLUSH PRIVILEGES;" 2>/dev/null || true
    log "DATABASE" "database/user dropped, root auth reset to unix_socket"
fi

# ============================================================
# 9. Remove iptables DDoS rules (restore default ACCEPT/policy-free)
# ============================================================
log "IPTABLES" "Flushing installer iptables rules"
run iptables -F INPUT 2>/dev/null || true
run iptables -P INPUT ACCEPT 2>/dev/null || true
run iptables-save >/dev/null 2>&1 || true
log "IPTABLES" "INPUT flushed to default ACCEPT"

# ============================================================
# 10. Purge packages (unless --keep-packages)
# ============================================================
if [ "$KEEP_PACKAGES" -eq 1 ]; then
    log "PACKAGES" "Skipping package purge (--keep-packages)"
else
    log "PACKAGES" "Purging Planet Hosts packages"
    # Only purge packages that are actually installed - a single unresolvable
    # name in one apt invocation aborts the WHOLE batch (apt exits 100 and leaves
    # everything installed).
    CANDIDATES="apache2 apache2-bin apache2-data apache2-utils libapache2-mod-security2 \
          mariadb-server mariadb-client mariadb-common \
          php8.5 php8.4 php8.3 php8.2 php8.1 php8.0 php7.4 php7.3 php7.2 php7.1 php7.0 php5.6 \
          php php-cli php-common php-fpm php-mysql php-curl php-gd php-intl php-mbstring php-xml php-zip \
          icecast2 liquidsoap ezstream ezstream-ffmpeg ffmpeg \
          postfix dovecot-imapd dovecot-pop3d dovecot-core vsftpd bind9 bind9utils \
          firewalld fail2ban clamav-daemon rspamd aide rkhunter chkrootkit lynis \
          certbot python3-certbot-apache nginx nginx-common \
          jailkit quota quotatool steamcmd nodejs npm \
          phpmyadmin snappymail goaccess logwatch opendkim \
          spamassassin clamav \
          trivy"
    TO_PURGE=""
    for p in $CANDIDATES; do
        if dpkg -l "$p" 2>/dev/null | grep -qE "^ii\s+$p\s"; then
            TO_PURGE="$TO_PURGE $p"
        else
            log "PACKAGES" "skip" "$p not installed"
        fi
    done
    if [ -n "$TO_PURGE" ]; then
        run apt-get purge -y $TO_PURGE 2>/dev/null || log "PACKAGES" "WARNING" "apt purge failed for some packages"
    fi
    run apt-get autoremove -y -qq 2>/dev/null || true
    run apt-get clean 2>/dev/null || true
    log "PACKAGES" "Packages purged"
fi

# ============================================================
# 11. Final
# ============================================================
echo ""
echo "=============================================="
if [ "$DRY_RUN" -eq 1 ]; then
    echo " DRY RUN COMPLETE - nothing was changed"
else
    echo " Uninstall Complete"
    echo "=============================================="
    echo " The server is now back to near-fresh OS state."
    echo " You can now re-run:"
    echo "   cd /home && curl -o latest -L https://planet-hosts.com/latest && sh latest"
    echo ""
    echo " Remaining (not removed, OS-level, will persist):"
    echo "   - User accounts created by panel/accounts (login system users)"
    echo "   - Certbot certificates under /etc/letsencrypt/"
    echo "   - Mail store under /var/vmail/ (if your setup created one)"
fi
echo "=============================================="
echo ""
log "DONE" "Uninstaller finished"
exit 0