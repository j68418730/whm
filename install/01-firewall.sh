#!/bin/bash
# 01-firewall — firewalld/fail2ban baseline (idempotent; does NOT replace existing rules)
# NOTE: This module only ensures firewalld + fail2ban are installed and active.
# It does NOT flush or replace existing Planet Hosts firewall rules.
set +e
DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$DIR/lib.sh"

sc_log "firewall" "install" "RUNNING" "Ensuring firewalld + fail2ban"
$PKG_INSTALL firewalld fail2ban 2>/dev/null || true

systemctl enable --now firewalld 2>/dev/null || true
systemctl enable --now fail2ban 2>/dev/null || true

if systemctl is-active firewalld >/dev/null 2>&1; then
    sc_status firewall ok "firewalld active"
    sc_log "firewall" "install" "OK" "firewalld active"
else
    sc_status firewall warn "firewalld not active"
    sc_log "firewall" "install" "WARN" "firewalld not active"
fi
