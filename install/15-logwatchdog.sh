#!/bin/bash
# Planet Hosts — Log Size Watchdog
# Checks for log files exceeding size threshold and creates alerts

set -eo pipefail

LOG_DIRS=(
    "/var/log/planethosts"
    "/var/log/radiohosting"
    "/var/log/apache2"
    "/var/log/shoutcast"
    "/var/log/icecast2"
    "/var/log/liquidsoap"
    "/home"
)

ALERT_FILE="/var/www/radiohosting/storage/security/logwatchdog.alerts"
LOG_FILE="/var/log/planethosts/logwatchdog.log"

THRESHOLD_GB=1
THRESHOLD_BYTES=$((THRESHOLD_GB * 1024 * 1024 * 1024))

# TRUNCATE=1 -> automatically truncate oversized logs instead of just alerting.
# Safe for append-mode writers (ffmpeg >>, apache, etc.). Defaults to on; the
# dashboard "Truncate" button and this flag are the two recovery paths.
TRUNCATE=${TRUNCATE:-1}

mkdir -p "$(dirname "$ALERT_FILE")"
mkdir -p "$(dirname "/var/log/planethosts/logwatchdog.log")"

log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $*" >> "$LOG_FILE"
    echo "$*"
}

# truncate_if_large <file>: alert (and optionally truncate) a single oversized log
truncate_if_large() {
    local file="$1"
    [ -f "$file" ] || return
    local size
    size=$(stat -c%s "$file" 2>/dev/null || echo 0)
    [ "$size" -gt "$THRESHOLD_BYTES" ] || return

    local size_gb=$(( size / 1024 / 1024 / 1024 ))
    local size_mb=$(( size / 1024 / 1024 ))
    local msg="Log file $file is ${size_gb}GB (${size_mb}MB) - exceeds ${THRESHOLD_GB}GB threshold"
    log "ALERT: $msg"

    local timestamp
    timestamp=$(date '+%Y-%m-%d %H:%M:%S')
    local alert_json
    alert_json=$(jq -n \
        --arg file "$file" \
        --arg size "$size" \
        --arg size_gb "$size_gb" \
        --arg size_mb "$size_mb" \
        --arg threshold "$THRESHOLD_GB" \
        --arg timestamp "$timestamp" \
        '{file: $file, size_bytes: ($size|tonumber), size_gb: ($size_gb|tonumber), size_mb: ($size_mb|tonumber), threshold_gb: ($threshold|tonumber), timestamp: $timestamp, severity: "critical"}')

    alerts="${alerts}${alert_json},"
    found_oversized=1

    if [ "$TRUNCATE" = "1" ]; then
        : > "$file" 2>/dev/null && log "TRUNCATED: $file freed ${size_mb}MB"
    fi
}

# Check each log directory for oversized files
found_oversized=0
alerts=""

for dir in "${LOG_DIRS[@]}"; do
    [ -d "$dir" ] || continue

    # Find files larger than threshold
    while IFS= read -r file; do
        truncate_if_large "$file"
    done < <(find "$dir" -type f -size +${THRESHOLD_GB}G 2>/dev/null)

    # AutoDJ logs live under /home/*/radio/autodj — scan them separately so the
    # glob expands. Without this, a runaway autodj_*.log (previous 27GB case)
    # would be missed and keep filling the disk.
    if [ "$dir" = "/home" ]; then
        for autodj in /home/*/radio/autodj; do
            [ -d "$autodj" ] || continue
            while IFS= read -r file; do
                truncate_if_large "$file"
            done < <(find "$autodj" -maxdepth 1 -type f -name "*.log" -size +${THRESHOLD_GB}G 2>/dev/null)
        done
    fi
done

# Write alerts to file if any found
if [ "$found_oversized" -eq 1 ]; then
    # Read existing alerts
    existing="[]"
    if [ -f "$ALERT_FILE" ]; then
        existing=$(cat "$ALERT_FILE" 2>/dev/null || echo "[]")
    fi
    
    # Parse new alerts and prepend to existing
    new_alerts="[${alerts%,}]"
    combined=$(echo "$existing $new_alerts" | jq -s 'add | sort_by(.timestamp) | reverse | .[:50]')
    echo "$combined" > "$ALERT_FILE"
    
    # Also log to system log
    logger -t "ph-logwatchdog" "Found $(echo "$combined" | jq length) oversized log files"
else
    # No oversized files - log that check passed
    log "OK: No log files exceed ${THRESHOLD_GB}GB threshold"
fi

# Install the ph-logwatchdog wrapper (used by the admin Security Center panel)
cat > /usr/local/bin/ph-logwatchdog << 'WRAPPER'
#!/bin/bash
exec /var/www/radiohosting/install/15-logwatchdog.sh "$@"
WRAPPER
chmod +x /usr/local/bin/ph-logwatchdog 2>/dev/null || true

# Register the 15-minute timer so the watchdog runs automatically
cat > /etc/systemd/system/ph-logwatchdog.service << 'SVC'
[Unit]
Description=Planet Hosts Log Size Watchdog
After=network.target

[Service]
Type=oneshot
ExecStart=/usr/local/bin/ph-logwatchdog
SVC
cat > /etc/systemd/system/ph-logwatchdog.timer << 'TMR'
[Unit]
Description=Run Planet Hosts Log Size Watchdog every 15 minutes

[Timer]
OnBootSec=5min
OnUnitActiveSec=15min
Persistent=true

[Install]
WantedBy=timers.target
TMR
systemctl daemon-reload
systemctl enable --now ph-logwatchdog.timer 2>/dev/null || true

exit 0