#!/bin/bash
# Planet Hosts — Security Hardening
# Usage: security.sh <setup|scan|clean> [username]
set -eo pipefail

ACTION="${1:-setup}"
USERNAME="$2"
MYSQL="mysql -u root -pSkylinehosting171"
LOGDIR="/var/log/radiohosting/security"
F2B_LOCAL="/etc/fail2ban/jail.local"

mkdir -p "$LOGDIR"

log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $*" >> "${LOGDIR}/security.log"
    echo "$*"
}

case "$ACTION" in
    setup)
        log "Setting up security hardening..."

        # === Fail2Ban: Create per-account jail configuration ===
        cat > "$F2B_LOCAL" << 'F2BEOF'
[DEFAULT]
bantime = 3600
findtime = 600
maxretry = 5

[planet-ssh]
enabled = true
port = ssh
filter = sshd
logpath = /var/log/auth.log
maxretry = 3
bantime = 86400

[planet-apache]
enabled = true
port = http,https
filter = apache-auth
logpath = /var/log/apache2/*_error.log
maxretry = 5
bantime = 3600

[planet-apache-overload]
enabled = true
port = http,https
filter = apache-noscript
logpath = /var/log/apache2/*_error.log
maxretry = 3
bantime = 86400

[planet-apache-badbots]
enabled = true
port = http,https
filter = apache-badbots
logpath = /var/log/apache2/*_access.log
maxretry = 1
bantime = 86400

[planet-php-url-fopen]
enabled = true
port = http,https
filter = php-url-fopen
logpath = /var/log/apache2/*_error.log
maxretry = 3
bantime = 86400

[planet-pureftpd]
enabled = true
port = ftp,ftps
filter = pure-ftpd
logpath = /var/log/syslog
maxretry = 5
bantime = 3600

[planet-postfix]
enabled = true
port = smtp,ssmtp,submission
filter = postfix
logpath = /var/log/mail.log
maxretry = 3
bantime = 86400

[planet-dovecot]
enabled = true
port = pop3,pop3s,imap,imaps
filter = dovecot
logpath = /var/log/mail.log
maxretry = 5
bantime = 3600
F2BEOF

        # === Ensure rsyslog + auth.log exist (needed for fail2ban) ===
        if ! command -v rsyslogd &>/dev/null; then
            apt-get install -y rsyslog 2>/dev/null || true
        fi
        if [ ! -f /var/log/auth.log ]; then
            touch /var/log/auth.log
            chmod 640 /var/log/auth.log
            chown root:adm /var/log/auth.log
            grep -q "authpriv" /etc/rsyslog.conf 2>/dev/null || echo "authpriv.* /var/log/auth.log" >> /etc/rsyslog.conf
        fi
        if [ ! -f /var/log/mail.log ]; then
            touch /var/log/mail.log
            chmod 640 /var/log/mail.log
            chown root:adm /var/log/mail.log
            grep -q "mail.* /var/log/mail.log" /etc/rsyslog.conf 2>/dev/null || echo "mail.* /var/log/mail.log" >> /etc/rsyslog.conf
        fi
        systemctl restart rsyslog 2>/dev/null || true

        if command -v fail2ban-client &>/dev/null; then
            fail2ban-client reload 2>/dev/null || systemctl restart fail2ban 2>/dev/null || true
            log "OK Fail2Ban configured with planet-* jails"
        else
            log "WARN Fail2Ban not installed, skipping"
        fi

        # === ModSecurity: Enable OWASP CRS if not already ===
        if [ -f "/etc/modsecurity/modsecurity.conf" ]; then
            sed -i 's/SecRuleEngine DetectionOnly/SecRuleEngine On/' /etc/modsecurity/modsecurity.conf 2>/dev/null || true
            log "OK ModSecurity: DetectionOnly→On"
        fi

        # === Disable dangerous PHP functions globally ===
        PHP_INI="/etc/php/*/cli/conf.d/99-planet-security.ini"
        for ini_path in /etc/php/*/cli/conf.d/; do
            cat > "${ini_path}99-planet-security.ini" << 'PHPEOF'
; Planet Hosts Security Hardening
disable_functions = exec,passthru,shell_exec,system,proc_open,popen,curl_multi_exec,show_source,phpinfo
allow_url_fopen = Off
allow_url_include = Off
expose_php = Off
display_errors = Off
register_globals = Off
PHPEOF
        done
        log "OK PHP security hardening applied"

        # === SSH hardening ===
        if [ -f "/etc/ssh/sshd_config" ]; then
            sed -i 's/#PermitRootLogin.*/PermitRootLogin prohibit-password/' /etc/ssh/sshd_config 2>/dev/null || true
            sed -i 's/#PasswordAuthentication.*/PasswordAuthentication no/' /etc/ssh/sshd_config 2>/dev/null || true
            sed -i 's/#MaxAuthTries.*/MaxAuthTries 3/' /etc/ssh/sshd_config 2>/dev/null || true
            sed -i 's/#ClientAliveInterval.*/ClientAliveInterval 300/' /etc/ssh/sshd_config 2>/dev/null || true
            sed -i 's/#ClientAliveCountMax.*/ClientAliveCountMax 0/' /etc/ssh/sshd_config 2>/dev/null || true
            systemctl reload sshd 2>/dev/null || true
            log "OK SSH hardened (root login=key-only, password auth disabled)"
        fi

        log "Security setup complete"
        ;;

    scan)
        # Malware scan for a specific user or all users
        if [ -n "$USERNAME" ]; then
            HOMEDIR="/home/${USERNAME}"
            [ ! -d "$HOMEDIR" ] && echo "Not found: $HOMEDIR" && exit 1
            SCAN_DIRS="$HOMEDIR"
        else
            SCAN_DIRS="/home/*/public_html"
        fi

        log "Starting malware scan for ${USERNAME:-all users}"

        # Scan for common malware patterns
        find $SCAN_DIRS -type f \( -name "*.php" -o -name "*.phtml" -o -name "*.php5" -o -name "*.suspected" \) -newer "/tmp" 2>/dev/null | while read -r file; do
            ISSUES=""
            # Check for base64_decode with eval
            if grep -l 'eval(base64_decode\|gzinflate(base64_decode\|str_rot13\|preg_replace.*\/e' "$file" 2>/dev/null; then
                ISSUES="${ISSUES} encoded_malware "
            fi
            # Check for system commands
            if grep -l 'system(\|shell_exec(\|exec(\|passthru(\|popen(\|proc_open(' "$file" 2>/dev/null; then
                ISSUES="${ISSUES} remote_exec "

                # Check for anonymous PHP shells
                if grep -l 'c99shell\|r57shell\|b374k\|webshell\|backdoor' "$file" 2>/dev/null; then
                    ISSUES="${ISSUES} known_shell "
                fi
            fi
            # Check for iframe injections
            if grep -l '<iframe\|document\.write.*<script' "$file" 2>/dev/null; then
                ISSUES="${ISSUES} iframe_inject "
            fi

            if [ -n "$ISSUES" ]; then
                log "MALWARE ${file}:${ISSUES}"
                # Quarantine the file
                QUAR_DIR="${HOMEDIR}/.quarantine"
                mkdir -p "$QUAR_DIR"
                cp "$file" "${QUAR_DIR}/$(basename ${file}).quarantine.$(date +%s)"
                chmod 000 "${QUAR_DIR}/$(basename ${file}).quarantine.$(date +%s)"
                log "QUARANTINED ${file} → ${QUAR_DIR}/"
            fi
        done

        # === Symlink protection ===
        find /home/*/public_html -type l 2>/dev/null | while read -r link; do
            TARGET=$(readlink -f "$link")
            if [[ "$TARGET" != /home/* ]]; then
                log "WARN Symlink escapes jail: ${link} → ${TARGET}"
            fi
        done

        log "Malware scan complete"
        ;;

    quarantine)
        # List/restore quarantined files
        QUAR_DIR="/home/${USERNAME}/.quarantine" 2>/dev/null || QUAR_DIR="/home/*/.quarantine"
        echo "=== Quarantined Files ==="
        find /home/*/.quarantine -type f 2>/dev/null | while read -r f; do
            ls -lh "$f" 2>/dev/null
        done || echo "No quarantined files"
        ;;

    *)
        echo "Usage: $0 <setup|scan|quarantine> [username]"
        exit 1
        ;;
esac
exit 0
