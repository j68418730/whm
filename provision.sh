#!/bin/bash
# Planet Hosts - Comprehensive Account Provisioning System v2
# Usage: provision_v2.sh <action> <username> <domain> <home_dir> <password> [package_id]
set -eo pipefail

ACTION="${1:-provision}"
USERNAME="$2"
DOMAIN="$3"
HOMEDIR="$4"
PASSWORD="$5"
PACKAGE_ID="$6"
LOGDIR="/var/log/radiohosting/provision"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
LOG_FILE="${LOGDIR}/${USERNAME}_${TIMESTAMP}.log"
STATUS_FILE="${LOGDIR}/${USERNAME}_status"

# Load package limits from DB
PACKAGE_DISK=0
PACKAGE_BW=0
PACKAGE_EMAILS=0
PACKAGE_FTP=0
PACKAGE_DBS=0
PACKAGE_SUBDOMAINS=0
PACKAGE_ADDONS=0
PACKAGE_PHP=""

log() {
    local level="$1"
    local msg="$2"
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] [${level}] ${msg}" | tee -a "$LOG_FILE"
}

set_status() {
    echo "$1" > "$STATUS_FILE"
    log "STATUS" "Status changed to: $1"
}

cleanup_and_rollback() {
    local failed_step="$1"
    log "ERROR" "Provisioning failed at step: ${failed_step}. Starting rollback..."

    # Delete Linux user
    if id "$USERNAME" &>/dev/null; then
        userdel -rf "$USERNAME" 2>/dev/null || true
        log "ROLLBACK" "Deleted Linux user: $USERNAME"
    fi

    # Remove home directory
    if [ -d "$HOMEDIR" ]; then
        rm -rf "$HOMEDIR" 2>/dev/null || true
        log "ROLLBACK" "Removed home directory: $HOMEDIR"
    fi

    # Remove Apache vhost
    if [ -f "/etc/apache2/sites-available/${USERNAME}.conf" ]; then
        a2dissite "${USERNAME}.conf" 2>/dev/null || true
        rm -f "/etc/apache2/sites-available/${USERNAME}.conf" 2>/dev/null || true
        log "ROLLBACK" "Removed Apache vhost"
    fi
    if [ -f "/etc/apache2/sites-available/${USERNAME}-le-ssl.conf" ]; then
        rm -f "/etc/apache2/sites-available/${USERNAME}-le-ssl.conf" 2>/dev/null || true
        log "ROLLBACK" "Removed Apache SSL vhost"
    fi

    # Remove DNS zone
    if [ -f "/etc/bind/zones/db.${DOMAIN}" ]; then
        rm -f "/etc/bind/zones/db.${DOMAIN}" 2>/dev/null || true
        sed -i "/zone \"${DOMAIN}\"/,/^};/d" /etc/bind/named.conf.local 2>/dev/null || true
        log "ROLLBACK" "Removed DNS zone: ${DOMAIN}"
    fi

    # Remove SSL certificates
    if [ -d "/etc/letsencrypt/live/${DOMAIN}" ]; then
        certbot delete --cert-name "${DOMAIN}" --non-interactive 2>/dev/null || true
        log "ROLLBACK" "Deleted SSL certificate: ${DOMAIN}"
    fi

    # Remove database and user if created
    if [ -n "$DB_CREATED" ]; then
        mysql -u root -pSkylinehosting171 -e "DROP DATABASE IF EXISTS \`${DB_NAME}\`;" 2>/dev/null || true
        mysql -u root -pSkylinehosting171 -e "DROP USER IF EXISTS '${DB_USER}'@'localhost';" 2>/dev/null || true
        log "ROLLBACK" "Removed database: ${DB_NAME}"
    fi

    # Remove FTP user
    if command -v pure-pw &>/dev/null; then
        pure-pw userdel "${USERNAME}" 2>/dev/null || true
        pure-pw mkdb 2>/dev/null || true
        log "ROLLBACK" "Removed FTP user"
    fi

    # Remove mail accounts
    if [ -d "/home/${USERNAME}/mail" ]; then
        rm -rf "/home/${USERNAME}/mail" 2>/dev/null || true
        log "ROLLBACK" "Removed mail directory"
    fi

    # Reload services
    systemctl reload apache2 2>/dev/null || true
    systemctl reload named 2>/dev/null || true

    set_status "failed"
    log "COMPLETE" "Rollback finished. Account provisioning failed at: ${failed_step}"
    exit 1
}

# Source package info if available
if [ -n "$PACKAGE_ID" ] && [ "$PACKAGE_ID" -gt 0 ]; then
    PKG_INFO=$(mysql -u root -pSkylinehosting171 -N -e "SELECT disk_space, bandwidth, email_accounts, ftp_accounts, databases, subdomains, addon_domains, php_version FROM radiohosting.hosting_packages WHERE id=${PACKAGE_ID};" 2>/dev/null || true)
    if [ -n "$PKG_INFO" ]; then
        read -r PACKAGE_DISK PACKAGE_BW PACKAGE_EMAILS PACKAGE_FTP PACKAGE_DBS PACKAGE_SUBDOMAINS PACKAGE_ADDONS PACKAGE_PHP <<< "$PKG_INFO"
    fi
fi

# Create log directory
mkdir -p "$LOGDIR"

case "$ACTION" in
    provision)
        log "START" "Beginning provision for ${USERNAME} (${DOMAIN})"

        # === STEP 1: Create Linux system user ===
        set_status "creating_user"
        if ! id "$USERNAME" &>/dev/null; then
            useradd -m -d "$HOMEDIR" -s /bin/bash "$USERNAME"
            if [ -n "$PASSWORD" ]; then
                echo "$USERNAME:$PASSWORD" | chpasswd
            fi
            log "OK" "Created Linux user: $USERNAME"
        else
            log "WARN" "Linux user $USERNAME already exists"
        fi
        usermod -a -G www-data "$USERNAME" 2>/dev/null || true

        # === STEP 2: Create filesystem structure ===
        set_status "creating_directories"
        mkdir -p "$HOMEDIR"/{public_html,logs,tmp,backups,mail,ssl,private,cgi-bin,.ssh}

        # Default index page
        if [ ! -f "$HOMEDIR/public_html/index.html" ]; then
            cat > "$HOMEDIR/public_html/index.html" << 'INDEXEOF'
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Welcome</title>
<style>body{font-family:Arial,sans-serif;background:#020817;color:#fff;display:flex;justify-content:center;align-items:center;height:100vh;margin:0}h1{color:#0A84FF}</style>
</head>
<body><h1>Welcome</h1><p style="color:#94a3b8">Your website is ready.</p></body>
</html>
INDEXEOF
        fi

        # Apply permissions
        chmod 755 "$HOMEDIR"
        chmod 755 "$HOMEDIR/public_html"
        chmod 750 "$HOMEDIR/logs"
        chmod 750 "$HOMEDIR/tmp"
        chmod 700 "$HOMEDIR/backups"
        chmod 700 "$HOMEDIR/private"
        chmod 750 "$HOMEDIR/ssl"
        chmod 755 "$HOMEDIR/cgi-bin"
        chmod 755 "$HOMEDIR/.ssh"
        chmod 755 "$HOMEDIR/mail"

        # chmod 1777 for tmp-like behavior but with sticky bit
        chmod 1777 "$HOMEDIR/tmp" 2>/dev/null || true

        # Create .ssh/authorized_keys
        touch "$HOMEDIR/.ssh/authorized_keys"
        chmod 600 "$HOMEDIR/.ssh/authorized_keys"

        chown -R "$USERNAME:$USERNAME" "$HOMEDIR"
        chown "$USERNAME:www-data" "$HOMEDIR/public_html" "$HOMEDIR/tmp" "$HOMEDIR/logs"
        chmod 755 "$HOMEDIR"
        log "OK" "Created filesystem structure with correct permissions"

        # === STEP 3: Create Apache vhost ===
        set_status "creating_vhost"
        PHP_FPM_CONFIG=""
        if [ -n "$PACKAGE_PHP" ]; then
            PHP_FPM_CONFIG="\n    <FilesMatch \\.php$>\n        SetHandler \"proxy:unix:/var/run/php/php${PACKAGE_PHP}-fpm-${USERNAME}.sock|fcgi://localhost/\"\n    </FilesMatch>"
        fi

        cat > "/etc/apache2/sites-available/${USERNAME}.conf" << VHOSTEOF
<VirtualHost *:80>
    ServerAdmin webmaster@${DOMAIN}
    ServerName ${DOMAIN}
    ServerAlias www.${DOMAIN}

    DocumentRoot ${HOMEDIR}/public_html
    <Directory ${HOMEDIR}/public_html>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # Logs
    ErrorLog ${HOMEDIR}/logs/error.log
    CustomLog ${HOMEDIR}/logs/access.log combined

    # PHP-FPM
    <FilesMatch \\.php$>
        SetHandler "proxy:unix:/var/run/php/php${PACKAGE_PHP}-fpm-${USERNAME}.sock|fcgi://localhost/"
    </FilesMatch>

    # Security headers
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"

    # Compression
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript application/json application/xml

    # Cache
    <FilesMatch "\\.(ico|pdf|flv|jpg|jpeg|png|gif|js|css|swf)$">
        Header set Cache-Control "max-age=2592000, public"
    </FilesMatch>

    # Deny access to hidden files
    <FilesMatch "^\.">
        Require all denied
    </FilesMatch>

    # CGI
    ScriptAlias /cgi-bin/ ${HOMEDIR}/cgi-bin/
    <Directory ${HOMEDIR}/cgi-bin>
        Options +ExecCGI
        AddHandler cgi-script .cgi .pl .py
        Require all granted
    </Directory>
</VirtualHost>
VHOSTEOF

        # Create PHP-FPM pool for this user if PHP version specified
        if [ -n "$PACKAGE_PHP" ] && [ -f "/etc/php/${PACKAGE_PHP}/fpm/pool.d/www.conf" ]; then
            cat > "/etc/php/${PACKAGE_PHP}/fpm/pool.d/${USERNAME}.conf" << FPMEOF
[${USERNAME}]
user = ${USERNAME}
group = ${USERNAME}
listen = /var/run/php/php${PACKAGE_PHP}-fpm-${USERNAME}.sock
listen.owner = ${USERNAME}
listen.group = www-data
listen.mode = 0660
pm = ondemand
pm.max_children = 10
pm.process_idle_timeout = 30s
pm.max_requests = 500
chdir = ${HOMEDIR}/public_html
php_admin_value[open_basedir] = ${HOMEDIR}:/tmp:/usr/share/php
php_admin_value[upload_tmp_dir] = ${HOMEDIR}/tmp
php_admin_value[session.save_path] = ${HOMEDIR}/tmp
php_admin_value[disable_functions] = exec,passthru,shell_exec,system,proc_open,popen,curl_multi_exec,parse_ini_file,show_source
FPMEOF
            systemctl reload "php${PACKAGE_PHP}-fpm" 2>/dev/null || true
            log "OK" "Created PHP-FPM pool for ${USERNAME} (PHP ${PACKAGE_PHP})"
        fi

        a2ensite "${USERNAME}.conf" 2>/dev/null || true
        log "OK" "Created Apache vhost: ${DOMAIN}"

        # === STEP 4: Create DNS zone ===
        set_status "creating_dns"
        ZONE_FILE="/etc/bind/zones/db.${DOMAIN}"
        if [ ! -f "$ZONE_FILE" ]; then
            # Generate DKIM key pair
            DKIM_SELECTOR="default"
            DKIM_DIR="${HOMEDIR}/ssl/dkim"
            mkdir -p "$DKIM_DIR"
            DKIM_PUB=""
            if command -v openssl &>/dev/null; then
                openssl genrsa -out "${DKIM_DIR}/dkim.pem" 2048 2>/dev/null || true
                openssl rsa -in "${DKIM_DIR}/dkim.pem" -pubout -out "${DKIM_DIR}/dkim.pub" 2>/dev/null || true
                DKIM_PUB=$(grep -v "PUBLIC KEY" "${DKIM_DIR}/dkim.pub" 2>/dev/null | tr -d '\n' || echo "MIGfMA0GCSqGSIb...")
                chown -R "${USERNAME}:${USERNAME}" "$DKIM_DIR"
                chmod 600 "${DKIM_DIR}/dkim.pem"
                chmod 644 "${DKIM_DIR}/dkim.pub"
            fi

            cat > "$ZONE_FILE" << ZONEEOF
\$TTL    604800
@       IN      SOA     ns1.planet-hosts.com. admin.${DOMAIN}. (
                  $(date +%Y%m%d)01     ; Serial
                  604800         ; Refresh
                  86400          ; Retry
                  2419200        ; Expire
                  604800         ; Negative Cache TTL
)
; Nameservers
@       IN      NS      ns1.planet-hosts.com.
@       IN      NS      ns2.planet-hosts.com.

; A records (IPv4)
@       IN      A       15.204.114.226
www     IN      A       15.204.114.226
ftp     IN      A       15.204.114.226
mail    IN      A       15.204.114.226
webmail IN      A       15.204.114.226

; AAAA records (IPv6)
@       IN      AAAA    2604:2dc0:101:200::1b7e
www     IN      AAAA    2604:2dc0:101:200::1b7e
mail    IN      AAAA    2604:2dc0:101:200::1b7e

; Mail
@       IN      MX 10   mail.${DOMAIN}.

; SPF
@       IN      TXT     "v=spf1 mx a ip4:15.204.114.226 include:_spf.planet-hosts.com ~all"

; DKIM
${DKIM_SELECTOR}._domainkey IN TXT "v=DKIM1; k=rsa; p=${DKIM_PUB}"

; DMARC
_dmarc  IN      TXT     "v=DMARC1; p=quarantine; rua=mailto:admin@${DOMAIN}; pct=100; sp=quarantine"

; Auto-discover
_autodiscover._tcp IN SRV 0 0 443 mail.${DOMAIN}.
_imap._tcp        IN SRV 0 0 143 mail.${DOMAIN}.
_imaps._tcp       IN SRV 0 0 993 mail.${DOMAIN}.
_pop3._tcp        IN SRV 0 0 110 mail.${DOMAIN}.
_pop3s._tcp       IN SRV 0 0 995 mail.${DOMAIN}.
_submission._tcp  IN SRV 0 0 587 mail.${DOMAIN}.
autoconfig        IN A    15.204.114.226
autodiscover      IN A    15.204.114.226
; cPanel-style service records
cpanel            IN A    15.204.114.226
ZONEEOF

            if ! grep -q "zone \"${DOMAIN}\"" /etc/bind/named.conf.local; then
                cat >> /etc/bind/named.conf.local << CONFEOF

zone "${DOMAIN}" {
    type master;
    file "/etc/bind/zones/db.${DOMAIN}";
};
CONFEOF
            fi

            if named-checkzone "${DOMAIN}" "$ZONE_FILE" 2>/dev/null && named-checkconf 2>/dev/null; then
                systemctl reload named 2>/dev/null || true
                log "OK" "Created DNS zone: ${DOMAIN}"
            else
                log "WARN" "DNS zone created but validation failed — check zone file"
            fi
        else
            log "WARN" "DNS zone already exists for ${DOMAIN}"
        fi

        # === STEP 5: SSL certificate ===
        set_status "creating_ssl"

        # Create Apache vhost for mail subdomain for SSL
        MAIL_VHOST="/etc/apache2/sites-available/mail.${DOMAIN}.conf"
        if [ ! -f "$MAIL_VHOST" ]; then
            cat > "$MAIL_VHOST" << MAILVHOST
<VirtualHost *:80>
    ServerName mail.${DOMAIN}
    DocumentRoot ${HOMEDIR}/public_html
    <Directory ${HOMEDIR}/public_html>
        Require all granted
    </Directory>
    ErrorLog ${HOMEDIR}/logs/mail_error.log
    CustomLog ${HOMEDIR}/logs/mail_access.log combined
</VirtualHost>
MAILVHOST
            a2ensite "mail.${DOMAIN}.conf" 2>/dev/null || true
        fi

        # Issue cert for main domain + www + mail subdomain
        if certbot --apache -d "${DOMAIN}" -d "www.${DOMAIN}" -d "mail.${DOMAIN}" --non-interactive --agree-tos --email "admin@${DOMAIN}" --redirect 2>/dev/null; then
            log "OK" "SSL certificate issued for ${DOMAIN} (+www, mail)"
        else
            log "WARN" "SSL certificate issuance failed — domain may not resolve yet"
            # Fallback: try without mail subdomain
            certbot --apache -d "${DOMAIN}" -d "www.${DOMAIN}" --non-interactive --agree-tos --email "admin@${DOMAIN}" --redirect 2>/dev/null || true
        fi

        # === STEP 6: Create database ===
        set_status "creating_database"
        DB_NAME=$(echo "${USERNAME}_db" | tr '-' '_' | cut -c1-64)
        DB_USER=$(echo "${USERNAME}_db" | tr '-' '_' | cut -c1-16)
        DB_PASS=$(openssl rand -base64 16 2>/dev/null || echo "${USERNAME}_db_pass_$(date +%s)")

        if mysql -u root -pSkylinehosting171 -e "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null; then
            mysql -u root -pSkylinehosting171 -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';" 2>/dev/null || true
            mysql -u root -pSkylinehosting171 -e "GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost'; FLUSH PRIVILEGES;" 2>/dev/null || true
            DB_CREATED=1
            log "OK" "Created database: ${DB_NAME} / user: ${DB_USER}"
        else
            log "WARN" "Database creation failed"
        fi

        # === STEP 7: Create FTP account ===
        set_status "creating_ftp"
        if command -v pure-pw &>/dev/null; then
            echo -e "${PASSWORD}\n${PASSWORD}" | pure-pw useradd "${USERNAME}" -u "$USERNAME" -g "$USERNAME" -d "$HOMEDIR" -m 2>/dev/null || true
            pure-pw mkdb 2>/dev/null || true
            log "OK" "Created FTP account: ${USERNAME}"
        elif command -v pure-pw &>/dev/null; then
            # pure-ftpd with MySQL auth
            log "OK" "FTP — pure-ftpd MySQL mode"
        else
            log "WARN" "FTP — pure-pw not available, skipping"
        fi

        # === STEP 8: Create default mailbox ===
        set_status "creating_mail"
        if command -v userdb &>/dev/null || [ -f "/etc/dovecot/passwd" ]; then
            MAIL_PASS=$(doveadm pw -s SHA512-CRYPT -p "${PASSWORD}" 2>/dev/null || echo "${PASSWORD}")
            echo "${USERNAME}@${DOMAIN}:${MAIL_PASS}:${USERNAME}:${USERNAME}::${HOMEDIR}/mail/${DOMAIN}::userdb_quota_rule=*:storage=${PACKAGE_DISK}M" >> "/etc/dovecot/passwd" 2>/dev/null || true
            if [ -d "/etc/dovecot" ]; then
                mkdir -p "${HOMEDIR}/mail/${DOMAIN}"
                chown -R "${USERNAME}:${USERNAME}" "${HOMEDIR}/mail"
            fi

            # Postfix virtual alias
            if [ -f "/etc/postfix/virtual" ]; then
                echo "${USERNAME}@${DOMAIN} ${USERNAME}@localhost" >> /etc/postfix/virtual
                postmap /etc/postfix/virtual 2>/dev/null || true
                systemctl reload postfix 2>/dev/null || true
            fi
            log "OK" "Created mailbox: admin@${DOMAIN}"
        else
            log "WARN" "Mail — Dovecot not fully configured, skipping"
        fi

        # === STEP 9: Apply quotas ===
        set_status "applying_quotas"
        if command -v setquota &>/dev/null; then
            setquota -u "$USERNAME" "$PACKAGE_DISK" "$PACKAGE_DISK" 0 0 "$HOMEDIR" 2>/dev/null || true
            log "OK" "Disk quota applied: ${PACKAGE_DISK}KB"
        fi

        # CPU/Memory limits via systemd
        mkdir -p "/etc/systemd/system/user@$(id -u "$USERNAME").service.d/"
        cat > "/etc/systemd/system/user@$(id -u "$USERNAME").service.d/limits.conf" << LIMITSEOF
[Service]
CPUQuota=${PACKAGE_CPU:-100}%
MemoryMax=${PACKAGE_RAM:-512}M
TasksMax=${PACKAGE_PROCESSES:-100}
LIMITSEOF
        systemctl daemon-reload 2>/dev/null || true
        log "OK" "Resource limits applied"

        # CGroups v2 — per-account cgroup for I/O and memory
        if [ -d "/sys/fs/cgroup" ]; then
            CG_PATH="/sys/fs/cgroup/planet-hosts/${USERNAME}"
            mkdir -p "$CG_PATH" 2>/dev/null || true
            if [ -d "$CG_PATH" ]; then
                # Memory limit
                echo "${PACKAGE_RAM:-512}M" > "${CG_PATH}/memory.max" 2>/dev/null || true
                echo "${PACKAGE_RAM:-512}M" > "${CG_PATH}/memory.high" 2>/dev/null || true
                # I/O weight (lower prio = less I/O)
                echo "${PACKAGE_IO_WEIGHT:-100}" > "${CG_PATH}/io.weight" 2>/dev/null || true
                # Process limit (pids)
                echo "${PACKAGE_PROCESSES:-100}" > "${CG_PATH}/pids.max" 2>/dev/null || true
                # Add user's PID to cgroup via a systemd service override
                mkdir -p "/etc/systemd/system/user@$(id -u "$USERNAME").service.d/"
                cat > "/etc/systemd/system/user@$(id -u "$USERNAME").service.d/cgroup.conf" << CGEOF
[Service]
Slice=planet-hosts.slice
CGEOF
                log "OK" "CGroups v2 limits applied for ${USERNAME}"
            fi
        fi

        # Create planet-hosts.slice for cgroup hierarchy
        if [ ! -f "/etc/systemd/system/planet-hosts.slice" ]; then
            cat > "/etc/systemd/system/planet-hosts.slice" << SLICEEOF
[Unit]
Description=Planet Hosts Account Slices
Before=slices.target

[Slice]
CPUAccounting=true
MemoryAccounting=true
IOAccounting=true
TasksAccounting=true
SLICEEOF
            systemctl daemon-reload 2>/dev/null || true
        fi

        # === STEP 10: Validation ===
        set_status "running_validation"
        VALIDATION_FAILED=0

        # Check Linux user exists
        if ! id "$USERNAME" &>/dev/null; then
            log "FAIL" "Validation: Linux user $USERNAME does not exist"
            VALIDATION_FAILED=1
        fi

        # Check home directory
        if [ ! -d "$HOMEDIR" ]; then
            log "FAIL" "Validation: Home directory $HOMEDIR does not exist"
            VALIDATION_FAILED=1
        fi

        # Check Apache config
        if ! apache2ctl configtest 2>/dev/null; then
            log "FAIL" "Validation: Apache config test failed"
            VALIDATION_FAILED=1
        fi

        # Check DNS zone
        if [ -f "$ZONE_FILE" ] && ! named-checkzone "${DOMAIN}" "$ZONE_FILE" 2>/dev/null; then
            log "FAIL" "Validation: DNS zone validation failed"
            VALIDATION_FAILED=1
        fi

        # Check SSL certificate
        if [ ! -d "/etc/letsencrypt/live/${DOMAIN}" ]; then
            log "WARN" "Validation: SSL certificate not found — may not have been issued yet"
        fi

        # Check database
        if [ -n "$DB_CREATED" ]; then
            if ! mysql -u "${DB_USER}" -p"${DB_PASS}" -e "SELECT 1;" "${DB_NAME}" 2>/dev/null; then
                log "FAIL" "Validation: Database login failed for ${DB_USER}"
                VALIDATION_FAILED=1
            else
                log "OK" "Validation: Database connection verified"
            fi
        fi

        if [ "$VALIDATION_FAILED" -eq 1 ]; then
            cleanup_and_rollback "validation_failed"
        fi

        # Store credentials for panel
        CREDENTIALS_FILE="${HOMEDIR}/.credentials.json"
        cat > "$CREDENTIALS_FILE" << CREDEOF
{
  "username": "${USERNAME}",
  "domain": "${DOMAIN}",
  "database_name": "${DB_NAME}",
  "database_user": "${DB_USER}",
  "database_password": "${DB_PASS}",
  "ftp_user": "${USERNAME}",
  "ftp_password": "${PASSWORD}",
  "mail_user": "admin@${DOMAIN}",
  "mail_password": "${PASSWORD}",
  "created_at": "$(date -Iseconds)",
  "provision_log": "${LOG_FILE}"
}
CREDEOF
        chmod 600 "$CREDENTIALS_FILE"
        chown "${USERNAME}:${USERNAME}" "$CREDENTIALS_FILE"

        # Reload Apache to apply all changes
        systemctl reload apache2 2>/dev/null || true

        set_status "completed"
        log "COMPLETE" "Account ${USERNAME} provisioned successfully"
        echo "PROVISION_COMPLETE:${USERNAME}:${DOMAIN}"
        ;;

    suspend)
        log "START" "Suspending account: ${USERNAME}"

        # Disable vhost
        if a2dissite "${USERNAME}.conf" 2>/dev/null; then
            a2dissite "${USERNAME}-le-ssl.conf" 2>/dev/null || true
            # Create suspension page
            cat > "${HOMEDIR}/public_html/.suspended.html" << SUSPENDEOF
<!DOCTYPE html>
<html><head><title>Account Suspended</title>
<style>body{font-family:Arial;background:#111;color:#fff;text-align:center;padding:80px 20px}h1{color:#f87171}</style>
</head><body><h1>Account Suspended</h1><p>This account has been suspended. Please contact support.</p></body></html>
SUSPENDEOF
            # Modify vhost to serve suspension page
            cat > "/etc/apache2/sites-available/${USERNAME}.conf" << VHOSTEOF
<VirtualHost *:80>
    ServerName ${DOMAIN}
    ServerAlias www.${DOMAIN}
    DocumentRoot ${HOMEDIR}/public_html
    <Directory ${HOMEDIR}/public_html>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    <FilesMatch "\.php$">
        Require all denied
    </FilesMatch>
    ErrorDocument 403 ${HOMEDIR}/public_html/.suspended.html
    ErrorDocument 404 ${HOMEDIR}/public_html/.suspended.html
    RewriteEngine On
    RewriteCond %{REQUEST_URI} !^/\.suspended\.html$
    RewriteRule ^(.*)$ /.suspended.html [L]
    ErrorLog ${HOMEDIR}/logs/error.log
    CustomLog ${HOMEDIR}/logs/access.log combined
</VirtualHost>
VHOSTEOF
            a2ensite "${USERNAME}.conf" 2>/dev/null || true
            systemctl reload apache2 2>/dev/null || true
            log "OK" "Account suspended - suspension page active"
        fi
        ;;

    unsuspend)
        log "START" "Unsuspending account: ${USERNAME}"

        # Restore original vhost (reuse provision logic)
        cat > "/etc/apache2/sites-available/${USERNAME}.conf" << VHOSTEOF
<VirtualHost *:80>
    ServerAdmin webmaster@${DOMAIN}
    ServerName ${DOMAIN}
    ServerAlias www.${DOMAIN}
    DocumentRoot ${HOMEDIR}/public_html
    <Directory ${HOMEDIR}/public_html>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    ErrorLog ${HOMEDIR}/logs/error.log
    CustomLog ${HOMEDIR}/logs/access.log combined
    <FilesMatch \\.php$>
        SetHandler "proxy:unix:/var/run/php/php${PACKAGE_PHP}-fpm-${USERNAME}.sock|fcgi://localhost/"
    </FilesMatch>
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-XSS-Protection "1; mode=block"
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript application/json
    <FilesMatch "\\.(ico|pdf|flv|jpg|jpeg|png|gif|js|css|swf)$">
        Header set Cache-Control "max-age=2592000, public"
    </FilesMatch>
</VirtualHost>
VHOSTEOF
        a2ensite "${USERNAME}.conf" 2>/dev/null || true
        a2ensite "${USERNAME}-le-ssl.conf" 2>/dev/null || true
        rm -f "${HOMEDIR}/public_html/.suspended.html" 2>/dev/null || true
        systemctl reload apache2 2>/dev/null || true
        log "OK" "Account unsuspended"
        ;;

    terminate)
        log "START" "Terminating account: ${USERNAME}"

        # Disable and remove vhost
        a2dissite "${USERNAME}.conf" 2>/dev/null || true
        rm -f "/etc/apache2/sites-available/${USERNAME}.conf" 2>/dev/null || true
        rm -f "/etc/apache2/sites-available/${USERNAME}-le-ssl.conf" 2>/dev/null || true

        # Remove DNS zone
        if [ -f "/etc/bind/zones/db.${DOMAIN}" ]; then
            rm -f "/etc/bind/zones/db.${DOMAIN}" 2>/dev/null || true
            sed -i "/zone \"${DOMAIN}\"/,/^};/d" /etc/bind/named.conf.local 2>/dev/null || true
        fi

        # Delete SSL certificates
        if [ -d "/etc/letsencrypt/live/${DOMAIN}" ]; then
            certbot delete --cert-name "${DOMAIN}" --non-interactive 2>/dev/null || true
        fi

        # Drop database
        DB_NAME=$(echo "${USERNAME}_db" | tr '-' '_' | cut -c1-64)
        DB_USER=$(echo "${USERNAME}_db" | tr '-' '_' | cut -c1-16)
        mysql -u root -pSkylinehosting171 -e "DROP DATABASE IF EXISTS \`${DB_NAME}\`;" 2>/dev/null || true
        mysql -u root -pSkylinehosting171 -e "DROP USER IF EXISTS '${DB_USER}'@'localhost';" 2>/dev/null || true

        # Remove PHP-FPM pool
        for PHPVER in "8.2" "8.1" "8.0" "7.4"; do
            if [ -f "/etc/php/${PHPVER}/fpm/pool.d/${USERNAME}.conf" ]; then
                rm -f "/etc/php/${PHPVER}/fpm/pool.d/${USERNAME}.conf" 2>/dev/null || true
                systemctl reload "php${PHPVER}-fpm" 2>/dev/null || true
            fi
        done

        # Remove quota
        if command -v setquota &>/dev/null; then
            setquota -u "$USERNAME" 0 0 0 0 "$HOMEDIR" 2>/dev/null || true
            rm -f "/etc/systemd/system/user@$(id -u "$USERNAME").service.d/limits.conf" 2>/dev/null || true
            systemctl daemon-reload 2>/dev/null || true
        fi

        # Remove FTP account
        if command -v pure-pw &>/dev/null; then
            pure-pw userdel "$USERNAME" 2>/dev/null || true
            pure-pw mkdb 2>/dev/null || true
        fi

        # Remove user and home directory (last steps)
        if id "$USERNAME" &>/dev/null; then
            userdel -rf "$USERNAME" 2>/dev/null || true
        fi
        if [ -d "$HOMEDIR" ]; then
            rm -rf "$HOMEDIR" 2>/dev/null || true
        fi

        systemctl reload apache2 2>/dev/null || true
        systemctl reload named 2>/dev/null || true

        log "COMPLETE" "Account ${USERNAME} terminated"
        ;;

    status)
        if [ -f "$STATUS_FILE" ]; then
            cat "$STATUS_FILE"
        else
            echo "unknown"
        fi
        ;;

    *)
        echo "Usage: $0 <provision|suspend|unsuspend|terminate|status> <username> <domain> <homedir> [password] [package_id]"
        exit 1
        ;;
esac
exit 0
