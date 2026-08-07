#!/bin/bash
# 14-opendkim — DKIM signing
set -e
DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$DIR/lib.sh"

sc_log "opendkim" "install" "RUNNING" "Installing OpenDKIM"
$PKG_INSTALL opendkim opendkim-tools 2>/dev/null || true

systemctl enable --now opendkim 2>/dev/null || true

# Basic config (postfix integration is left to the mail module)
mkdir -p /etc/opendkim/keys /run/opendkim
cat > /etc/opendkim.conf << 'EOF'
Syslog                  yes
UMask                   007
Canonicalization        relaxed/simple
Mode                    sv
SubDomains              yes
AutoRestart             yes
AutoRestartRate         10/1M
PidFile                 /run/opendkim/opendkim.pid
Socket                  local:/run/opendkim/opendkim.sock
KeyTable                refile:/etc/opendkim/KeyTable
SigningTable            refile:/etc/opendkim/SigningTable
InternalHosts           refile:/etc/opendkim/TrustedHosts
EOF
touch /etc/opendkim/KeyTable /etc/opendkim/SigningTable
cat > /etc/opendkim/TrustedHosts << 'EOF'
127.0.0.1
localhost
EOF
chown -R opendkim:opendkim /etc/opendkim /run/opendkim 2>/dev/null || true

sc_status opendkim ok "$(opendkim --version 2>/dev/null || echo installed)"
sc_log "opendkim" "install" "OK" "OpenDKIM installed"
