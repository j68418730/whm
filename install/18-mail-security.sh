#!/bin/bash
# 18-mail-security — SpamAssassin + OpenDKIM + ClamAV mail integration (idempotent)
set +e
DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$DIR/lib.sh"

sc_log "mail-security" "install" "RUNNING" "Installing mail security (SpamAssassin, OpenDKIM, ClamAV)"

# ─── SpamAssassin ───
case "$PKG_MGR" in
    apt) $PKG_INSTALL spamassassin spamc 2>/dev/null || true ;;
    dnf|yum) $PKG_INSTALL spamassassin 2>/dev/null || true ;;
esac
if command -v spamc >/dev/null 2>&1 || command -v spamassassin >/dev/null 2>&1; then
    systemctl enable --now spamassassin 2>/dev/null || systemctl enable --now spamd 2>/dev/null || true
    if systemctl is-active spamassassin >/dev/null 2>&1 || systemctl is-active spamd >/dev/null 2>&1 || command -v spamc >/dev/null 2>&1; then
        sc_status spamassassin ok "SpamAssassin installed"
    else
        sc_status spamassassin warn "SpamAssassin installed but not running"
    fi
    sc_log "mail-security" "spamassassin" "OK" "SpamAssassin processed"
else
    sc_status spamassassin failed "SpamAssassin install failed"
    sc_log "mail-security" "spamassassin" "FAIL" "SpamAssassin missing"
fi

# ─── OpenDKIM ───
case "$PKG_MGR" in
    apt) $PKG_INSTALL opendkim opendkim-tools 2>/dev/null || true ;;
    dnf|yum) $PKG_INSTALL opendkim 2>/dev/null || true ;;
esac
if command -v opendkim >/dev/null 2>&1; then
    mkdir -p /etc/opendkim/keys 2>/dev/null || true
    systemctl enable --now opendkim 2>/dev/null || true
    if systemctl is-active opendkim >/dev/null 2>&1; then
        sc_status opendkim ok "OpenDKIM active"
    else
        sc_status opendkim warn "OpenDKIM installed but not running"
    fi
    sc_log "mail-security" "opendkim" "OK" "OpenDKIM processed"
else
    sc_status opendkim failed "OpenDKIM install failed"
    sc_log "mail-security" "opendkim" "FAIL" "OpenDKIM missing"
fi

# ─── ClamAV mail integration (ensure daemon + freshclam) ───
systemctl enable --now clamav-daemon 2>/dev/null || systemctl enable --now clamd 2>/dev/null || true
systemctl enable --now clamav-freshclam 2>/dev/null || systemctl enable --now freshclam 2>/dev/null || true
if command -v clamscan >/dev/null 2>&1; then
    sc_status clamav ok "ClamAV installed"
else
    sc_status clamav failed "ClamAV missing"
fi

exit 0