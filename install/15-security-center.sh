#!/bin/bash
# 15-security-center — finalize: status report + UI integration
set -e
DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$DIR/lib.sh"

sc_log "security-center" "finalize" "RUNNING" "Finalizing Security Center"

# Emit a combined status file the UI dashboard reads
cat > /var/www/radiohosting/storage/security/summary.json << 'EOF'
{
  "generated": "PLACEHOLDER",
  "modules": {}
}
EOF

REPORT="/var/www/radiohosting/storage/security/status.txt"
{
    echo "Security Center Status — $(date)"
    echo "================================"
    for s in /var/www/radiohosting/storage/security/*.status; do
        [ -f "$s" ] && cat "$s"
    done
} > "$REPORT"
chown -R www-data:www-data /var/www/radiohosting/storage/security 2>/dev/null || true

# Add www-data sudoers entries so the UI can trigger scans (narrow, tool-specific)
cat > /etc/sudoers.d/www-data-security << 'EOF'
www-data ALL=(ALL) NOPASSWD: /usr/local/bin/ph-*, /usr/bin/clamscan, /usr/bin/yara, /usr/bin/trivy, /usr/bin/osv-scanner, /usr/bin/lynis, /usr/bin/aide, /usr/bin/rkhunter, /usr/bin/chkrootkit, /usr/bin/logwatch, /usr/bin/goaccess, /usr/local/bin/testssl, /usr/bin/spamc, /bin/systemctl restart clamav-daemon, /bin/systemctl restart clamav-freshclam
EOF
chmod 440 /etc/sudoers.d/www-data-security 2>/dev/null || true

sc_status security-center ok "finalized"
sc_log "security-center" "finalize" "OK" "Security Center finalized"
