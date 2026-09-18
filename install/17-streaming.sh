#!/bin/bash
# 17-streaming — Icecast + SHOUTcast v1 + SHOUTcast v2 (idempotent)
# Installs the streaming engines the panel provisions stations onto.
set +e
DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$DIR/lib.sh"

sc_log "streaming" "install" "RUNNING" "Installing streaming engines (Icecast, SHOUTcast v1/v2)"

# ─── Icecast ───
if ! command -v icecast2 >/dev/null 2>&1 && ! command -v icecast >/dev/null 2>&1; then
    case "$PKG_MGR" in
        apt)
            echo "icecast2 icecast2/icecast2 boolean true" | debconf-set-selections 2>/dev/null || true
            $PKG_INSTALL icecast2 2>/dev/null || true
            ;;
        dnf|yum) $PKG_INSTALL icecast 2>/dev/null || true ;;
    esac
fi
systemctl enable --now icecast2 2>/dev/null || systemctl enable --now icecast 2>/dev/null || true
if systemctl is-active icecast2 >/dev/null 2>&1 || systemctl is-active icecast >/dev/null 2>&1; then
    sc_status icecast ok "icecast active"
    sc_log "streaming" "icecast" "OK" "Icecast active"
else
    sc_status icecast warn "icecast installed but not running"
    sc_log "streaming" "icecast" "WARN" "Icecast not active"
fi

# ─── SHOUTcast DNAS v2 ───
SC2_TAR=""
for _cand in \
    "$DIR/../shoutcast-server/shoucast-v2/sc_serv2_linux_x64-latest.tar.gz" \
    "$DIR/../shoutcast-server/shoucast-v2/sc_serv2_linux-latest.tar.gz" \
    "$DIR/../sc_serv2_linux_x64-latest.tar.gz" \
    "/var/www/radiohosting/shoutcast-server/shoucast-v2/sc_serv2_linux_x64-latest.tar.gz"; do
    if [ -f "$_cand" ]; then SC2_TAR="$_cand"; break; fi
done
if [ -n "$SC2_TAR" ] || [ -x /opt/planethosts/shoutcast/sc_serv ]; then
    mkdir -p /opt/planethosts/shoutcast /var/log/shoutcast
    [ -n "$SC2_TAR" ] && tar xzf "$SC2_TAR" -C /opt/planethosts/shoutcast 2>/dev/null
    chmod 755 /opt/planethosts/shoutcast/sc_serv 2>/dev/null
    if [ ! -f /opt/planethosts/shoutcast/sc_serv.conf ]; then
        cat > /opt/planethosts/shoutcast/sc_serv.conf << 'SCEOF'
adminpassword=ShoutcastAdmin171
password=Shoutcast171
requirestreamconfigs=1
streamid=1
streampath=/stream
portbase=8000
logfile=/var/log/shoutcast/sc_serv.log
w3clog=/var/log/shoutcast/sc_w3c.log
banfile=/opt/planethosts/shoutcast/sc_serv.ban
ripfile=/var/log/shoutcast/sc_rip.log
SCEOF
    fi
    if [ ! -f /etc/systemd/system/shoutcast.service ]; then
        cat > /etc/systemd/system/shoutcast.service << 'UNIT'
[Unit]
Description=SHOUTcast DNAS v2 Server
After=network.target
[Service]
Type=simple
User=shoutcast
Group=shoutcast
WorkingDirectory=/opt/planethosts/shoutcast
ExecStart=/opt/planethosts/shoutcast/sc_serv /opt/planethosts/shoutcast/sc_serv.conf
Restart=always
[Install]
WantedBy=multi-user.target
UNIT
    fi
    useradd -r -d /opt/planethosts/shoutcast -s /sbin/nologin shoutcast 2>/dev/null || true
    chown -R shoutcast:shoutcast /opt/planethosts/shoutcast /var/log/shoutcast 2>/dev/null || true
    systemctl daemon-reload 2>/dev/null || true
    systemctl enable --now shoutcast 2>/dev/null || true
    if systemctl is-active shoutcast >/dev/null 2>&1; then
        sc_status shoutcast_v2 ok "SHOUTcast v2 active"
    else
        sc_status shoutcast_v2 warn "SHOUTcast v2 installed but not running"
    fi
    sc_log "streaming" "shoutcast_v2" "OK" "SHOUTcast v2 processed"
else
    sc_status shoutcast_v2 failed "SHOUTcast v2 tarball not found"
    sc_log "streaming" "shoutcast_v2" "FAIL" "SHOUTcast v2 tarball missing"
fi

# ─── SHOUTcast v1 (DNAS 1.x, optional/legacy) ───
SC1_TAR=""
for _cand in \
    "$DIR/../shoutcast-server/shoutcast-v1/sc_serv_1.9.8_linux.tar.gz" \
    "$DIR/../shoutcast-server/shoutcast-v1/sc_serv_1.9.8.tar.gz" \
    "$DIR/../shoutcast-server/sc_serv_1.9.8_linux.tar.gz"; do
    if [ -f "$_cand" ]; then SC1_TAR="$_cand"; break; fi
done
if [ -n "$SC1_TAR" ] || [ -d /opt/planethosts/shoutcast-v1 ]; then
    mkdir -p /opt/planethosts/shoutcast-v1 /var/log/shoutcast
    [ -n "$SC1_TAR" ] && tar xzf "$SC1_TAR" -C /opt/planethosts/shoutcast-v1 2>/dev/null
    chmod 755 /opt/planethosts/shoutcast-v1/sc_serv 2>/dev/null
    if [ ! -f /opt/planethosts/shoutcast-v1/sc_serv.conf ]; then
        cat > /opt/planethosts/shoutcast-v1/sc_serv.conf << 'SC1EOF'
MaxUser=100
Password=Shoutcast171
AdminPassword=ShoutcastAdmin171
PortBase=9000
LogFile=/var/log/shoutcast/sc1.log
SC1EOF
    fi
    if [ ! -f /etc/systemd/system/shoutcast-v1.service ]; then
        cat > /etc/systemd/system/shoutcast-v1.service << 'UNIT1'
[Unit]
Description=SHOUTcast DNAS v1 Server
After=network.target
[Service]
Type=simple
User=shoutcast
Group=shoutcast
WorkingDirectory=/opt/planethosts/shoutcast-v1
ExecStart=/opt/planethosts/shoutcast-v1/sc_serv /opt/planethosts/shoutcast-v1/sc_serv.conf
Restart=always
[Install]
WantedBy=multi-user.target
UNIT1
    fi
    systemctl daemon-reload 2>/dev/null || true
    systemctl enable --now shoutcast-v1 2>/dev/null || true
    if systemctl is-active shoutcast-v1 >/dev/null 2>&1; then
        sc_status shoutcast_v1 ok "SHOUTcast v1 active"
    else
        sc_status shoutcast_v1 warn "SHOUTcast v1 installed but not running"
    fi
    sc_log "streaming" "shoutcast_v1" "OK" "SHOUTcast v1 processed"
else
    sc_status shoutcast_v1 not_installed "SHOUTcast v1 tarball not present (legacy/optional)"
    sc_log "streaming" "shoutcast_v1" "WARN" "SHOUTcast v1 tarball missing (optional)"
fi

chown -R www-data:www-data /var/www/radiohosting/storage 2>/dev/null || true
exit 0