#!/bin/bash
# 13-spamassassin — email spam filtering
set -e
DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$DIR/lib.sh"

sc_log "spamassassin" "install" "RUNNING" "Installing SpamAssassin"
$PKG_INSTALL spamassassin spamc 2>/dev/null || true

systemctl enable --now spamassassin 2>/dev/null || true

cat > /usr/local/bin/ph-spamassassin << 'EOF'
#!/bin/bash
# Test a message: cat email.txt | ph-spamassassin
if command -v spamc >/dev/null 2>&1; then
    spamc -c 2>/dev/null || true
else
    echo "spamassassin not available"
fi
EOF
chmod +x /usr/local/bin/ph-spamassassin

sc_status spamassassin ok "$(sa-version 2>/dev/null || spamassassin --version 2>/dev/null | head -1 || echo installed)"
sc_log "spamassassin" "install" "OK" "SpamAssassin installed"
