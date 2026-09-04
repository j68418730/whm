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
)

THRESHOLD_GB=1
THRESHOLD_BYTES=$((THRESHOLD_GB * 1024 * 1024 * 1024))
ALERT_FILE="/var/www/radiohosting/storage/security/logwatchdog.alerts"
LOG_FILE="/var/log/planethosts/logwatchdog.log"

mkdir -p "$(dirname "$ALERT_FILE")"
mkdir -p "$(dirname "/var/log/planethosts/logwatchdog.log")"

log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $*" >> "$LOG_FILE"
    echo "$*"
}

# Check each log directory for oversized files
found_oversized=0
alerts=""

for dir in "${LOG_DIRS[@]}"; do
    if [ ! -d "$dir" ]; then
        continue
    fi

    # Find files larger than threshold
    while IFS= read -r file; do
        if [ ! -f "$file" ]; then
            continue
        fi

        size=$(stat -c%s "$file" 2>/dev/null || echo 0)
        if [ "$size" -gt "$THRESHOLD_BYTES" ]; then
            size_gb=$(( size / 1024 / 1024 / 1024 ))
            size_mb=$(( size / 1024 / 1024 ))
            msg="Log file $file is ${size_gb}GB (${size_mb}MB) - exceeds ${THRESHOLD_GB}GB threshold"
            log "ALERT: $msg"
            
            # Add to alerts JSON
            timestamp=$(date '+%Y-%m-%d %H:%M:%S')
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
        fi
    done < <(find "$dir" -type f -size +${THRESHOLD_GB}G 2>/dev/null)
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

exit 0