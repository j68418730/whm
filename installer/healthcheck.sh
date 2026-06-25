#!/bin/bash
# =========================================================
# Planet Hosts Master Panel - Health Check
# =========================================================
# Verifies all services and system resources.
# Output: PASS / WARNING / FAIL

set -eo pipefail

LOG_DIR="/var/log/planethosts"
PANEL_DIR="/var/www/radiohosting"
EXIT_CODE=0

log() {
    local level="$1" msg="$2"
    local ts=$(date '+%Y-%m-%d %H:%M:%S')
    mkdir -p "$LOG_DIR"
    echo "$ts | HEALTHCHECK | $level | $msg" >> "$LOG_DIR/healthcheck.log"
    printf "  [%-7s] %s\n" "$level" "$msg"
}

echo "=================================================="
echo " Planet Hosts - System Health Check"
echo "=================================================="
echo ""

PASS=0
WARNING=0
FAIL=0

# --- Services ---
echo "--- Services ---"
for svc in httpd mariadb firewalld crond; do
    if systemctl is-active --quiet "$svc" 2>/dev/null; then
        log "PASS" "$svc is running"
        ((PASS++))
    else
        log "FAIL" "$svc is NOT running"
        ((FAIL++))
        EXIT_CODE=1
    fi
done

# --- PHP ---
echo ""
echo "--- PHP ---"
if command -v php >/dev/null 2>&1; then
    PHP_VER=$(php -v 2>/dev/null | head -1 | awk '{print $2}')
    log "PASS" "PHP $PHP_VER is available"
    ((PASS++))
else
    log "FAIL" "PHP is not installed"
    ((FAIL++))
    EXIT_CODE=1
fi

# Check required PHP extensions
REQUIRED_EXTS="pdo mysql mbstring curl gd intl xml zip bcmath openssl redis"
MISSING_EXTS=""
for ext in $REQUIRED_EXTS; do
    if ! php -m 2>/dev/null | grep -qi "$ext"; then
        MISSING_EXTS="$MISSING_EXTS $ext"
    fi
done
if [ -z "$MISSING_EXTS" ]; then
    log "PASS" "All required PHP extensions present"
    ((PASS++))
else
    log "WARNING" "Missing PHP extensions:$MISSING_EXTS"
    ((WARNING++))
fi

# --- FFmpeg ---
echo ""
echo "--- FFmpeg ---"
if command -v ffmpeg >/dev/null 2>&1; then
    FFMPEG_VER=$(ffmpeg -version 2>/dev/null | head -1 | awk '{print $3}')
    log "PASS" "FFmpeg $FFMPEG_VER is available"
    ((PASS++))
else
    log "WARNING" "FFmpeg is not installed"
    ((WARNING++))
fi

# --- Icecast ---
echo ""
echo "--- Icecast ---"
if systemctl is-active --quiet icecast 2>/dev/null; then
    log "PASS" "Icecast is running"
    ((PASS++))
elif command -v icecast >/dev/null 2>&1; then
    log "WARNING" "Icecast is installed but not running"
    ((WARNING++))
else
    log "WARNING" "Icecast is not installed"
    ((WARNING++))
fi

# --- Liquidsoap ---
echo ""
echo "--- Liquidsoap ---"
if command -v liquidsoap >/dev/null 2>&1; then
    LS_VER=$(liquidsoap --version 2>/dev/null | head -1 || echo "unknown")
    log "PASS" "Liquidsoap $LS_VER is available"
    ((PASS++))
else
    log "WARNING" "Liquidsoap is not installed"
    ((WARNING++))
fi

# --- Disk ---
echo ""
echo "--- Disk ---"
DISK_USAGE=$(df -h / 2>/dev/null | awk 'NR==2 {print $5}' | sed 's/%//')
if [ "$DISK_USAGE" -lt 80 ]; then
    log "PASS" "Disk usage: ${DISK_USAGE}%"
    ((PASS++))
elif [ "$DISK_USAGE" -lt 90 ]; then
    log "WARNING" "Disk usage: ${DISK_USAGE}%"
    ((WARNING++))
else
    log "FAIL" "Disk usage critical: ${DISK_USAGE}%"
    ((FAIL++))
    EXIT_CODE=1
fi

# --- RAM ---
echo ""
echo "--- RAM ---"
TOTAL_RAM=$(grep MemTotal /proc/meminfo 2>/dev/null | awk '{printf "%.0f", $2/1024/1024}')
FREE_RAM=$(grep MemAvailable /proc/meminfo 2>/dev/null | awk '{printf "%.0f", $2/1024/1024}')
if [ "$FREE_RAM" -gt 0 ]; then
    log "PASS" "RAM: ${FREE_RAM}GB free / ${TOTAL_RAM}GB total"
    ((PASS++))
else
    log "WARNING" "Low memory available"
    ((WARNING++))
fi

# --- CPU ---
echo ""
echo "--- CPU ---"
CPU_LOAD=$(awk '{print $1}' /proc/loadavg 2>/dev/null || echo "0")
CPU_CORES=$(nproc 2>/dev/null || echo 1)
THRESHOLD=$(echo "$CPU_CORES * 0.8" | bc 2>/dev/null || echo "$CPU_CORES")
if (( $(echo "$CPU_LOAD < $THRESHOLD" | bc -l 2>/dev/null || echo 1) )); then
    log "PASS" "CPU load: $CPU_LOAD (cores: $CPU_CORES)"
    ((PASS++))
else
    log "WARNING" "CPU load high: $CPU_LOAD (cores: $CPU_CORES)"
    ((WARNING++))
fi

# --- Network ---
echo ""
echo "--- Network ---"
if ping -c 1 -W 2 8.8.8.8 >/dev/null 2>&1; then
    log "PASS" "Internet connection OK"
    ((PASS++))
else
    log "FAIL" "No internet connection"
    ((FAIL++))
    EXIT_CODE=1
fi

if host planet-hosts.com >/dev/null 2>&1; then
    log "PASS" "DNS resolution OK"
    ((PASS++))
else
    log "WARNING" "DNS resolution issue"
    ((WARNING++))
fi

# --- Storage Permissions ---
echo ""
echo "--- Storage ---"
if [ -d "$PANEL_DIR" ]; then
    if touch "$PANEL_DIR/storage/test_write" 2>/dev/null; then
        rm -f "$PANEL_DIR/storage/test_write"
        log "PASS" "Panel storage is writable"
        ((PASS++))
    else
        log "FAIL" "Panel storage is NOT writable"
        ((FAIL++))
        EXIT_CODE=1
    fi
else
    log "FAIL" "Panel directory does not exist"
    ((FAIL++))
    EXIT_CODE=1
fi

# --- License ---
echo ""
echo "--- License ---"
if [ -f /etc/planethosts/license.json ]; then
    log "PASS" "License file present"
    ((PASS++))
else
    log "FAIL" "No license file found"
    ((FAIL++))
    EXIT_CODE=1
fi

# --- Database ---
echo ""
echo "--- Database ---"
if mysqladmin ping -u root >/dev/null 2>&1; then
    log "PASS" "MariaDB is responding"
    ((PASS++))
    if mysql -u root -e "USE radiohosting;" 2>/dev/null; then
        log "PASS" "radiohosting database exists"
        ((PASS++))
    else
        log "FAIL" "radiohosting database does not exist"
        ((FAIL++))
        EXIT_CODE=1
    fi
else
    log "FAIL" "MariaDB is not responding"
    ((FAIL++))
    EXIT_CODE=1
fi

# --- SSL ---
echo ""
echo "--- SSL ---"
if [ -d /etc/letsencrypt/live ] || systemctl is-active --quiet httpd 2>/dev/null && grep -q "SSLEngine" /etc/httpd/conf.d/*.conf 2>/dev/null; then
    log "PASS" "SSL appears configured"
    ((PASS++))
else
    log "WARNING" "SSL not configured (optional)"
    ((WARNING++))
fi

# --- Summary ---
echo ""
echo "=================================================="
echo " Health Check Results"
echo "=================================================="
printf "  PASS:    %d\n" "$PASS"
printf "  WARNING: %d\n" "$WARNING"
printf "  FAIL:    %d\n" "$FAIL"
echo "=================================================="

log "HEALTHCHECK" "complete" "PASS=$PASS WARNING=$WARNING FAIL=$FAIL"

exit $EXIT_CODE
