#!/bin/bash
# =========================================================
# Planet Hosts Master Panel - License Activation
# =========================================================
# Collects server info, sends HTTPS POST to licensing server,
# stores signed license at /etc/planethosts/license.json

set -eo pipefail

LOG_DIR="/var/log/planethosts"
LICENSE_DIR="/etc/planethosts"
LICENSE_FILE="$LICENSE_DIR/license.json"
ACTIVATE_URL="https://license.planet-hosts.com/api/activate"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PANEL_DIR="/var/www/radiohosting"

log() {
    local msg="$1"
    local ts=$(date '+%Y-%m-%d %H:%M:%S')
    mkdir -p "$LOG_DIR"
    echo "$ts | LICENSE | $msg" >> "$LOG_DIR/license.log"
    echo "[LICENSE] $msg"
}

get_server_uuid() {
    if [ -f /etc/machine-id ]; then
        echo "$(cat /etc/machine-id)-$(dmidecode -s system-uuid 2>/dev/null || hostid)"
    elif command -v dmidecode >/dev/null 2>&1; then
        dmidecode -s system-uuid 2>/dev/null || hostid
    else
        echo "$(hostid)-$(cat /proc/sys/kernel/random/uuid 2>/dev/null || echo unknown)"
    fi
}

get_os() {
    if [ -f /etc/os-release ]; then
        . /etc/os-release
        echo "$ID $VERSION_ID"
    else
        echo "unknown"
    fi
}

get_kernel() {
    uname -r
}

get_cpu() {
    local model=$(grep -m1 'model name' /proc/cpuinfo 2>/dev/null | sed 's/.*: //')
    local cores=$(nproc 2>/dev/null || echo 1)
    echo "$model ($cores cores)"
}

get_ram() {
    local total=$(grep MemTotal /proc/meminfo 2>/dev/null | awk '{printf "%.0f", $2/1024/1024}')
    echo "${total}GB"
}

get_disk() {
    local total=$(df -h / 2>/dev/null | awk 'NR==2 {print $2}')
    echo "$total"
}

get_panel_version() {
    if [ -f "$PANEL_DIR/VERSION" ]; then
        cat "$PANEL_DIR/VERSION"
    else
        echo "1.0.0"
    fi
}

# --- Main ---
clear
echo "=================================================="
echo " Planet Hosts - License Activation"
echo "=================================================="
echo ""

mkdir -p "$LICENSE_DIR"

# --- Collect Server Info ---
log "Collecting server information..."
SERVER_UUID=$(get_server_uuid)
HOSTNAME=$(hostname -f 2>/dev/null || hostname)
PUBLIC_IP=$(curl -s --max-time 5 https://ifconfig.me/ip 2>/dev/null || \
            curl -s --max-time 5 https://icanhazip.com 2>/dev/null || echo "unknown")
OS=$(get_os)
DISTRO=$(awk -F= '/^ID=/{print $2}' /etc/os-release 2>/dev/null | tr -d '"' || echo "unknown")
KERNEL=$(get_kernel)
CPU=$(get_cpu)
CORES=$(nproc 2>/dev/null || echo 1)
RAM=$(get_ram)
DISK=$(get_disk)
PANEL_VERSION=$(get_panel_version)

echo ""
echo "Server Information:"
echo "  UUID:     $SERVER_UUID"
echo "  Hostname: $HOSTNAME"
echo "  IP:       $PUBLIC_IP"
echo "  OS:       $OS"
echo "  Kernel:   $KERNEL"
echo "  CPU:      $CPU"
echo "  RAM:      $RAM"
echo "  Disk:     $DISK"
echo "  Version:  $PANEL_VERSION"
echo ""

# --- Prompt for License Key ---
echo "------------------------------------------"
echo "Enter your Planet Hosts license key:"
read -p "License Key: " LICENSE_KEY

if [ -z "$LICENSE_KEY" ]; then
    log "No license key entered."
    echo "ERROR: License key is required."
    exit 1
fi

echo ""
echo "Enter your email (optional, for license recovery):"
read -p "Email: " CUSTOMER_EMAIL

# --- Send Activation Request ---
log "Sending activation request to licensing server..."
echo ""
echo "Activating license..."

RESPONSE=$(curl -s --max-time 30 \
    -X POST "$ACTIVATE_URL" \
    -H "Content-Type: application/json" \
    -H "User-Agent: PlanetHosts-Installer/$PANEL_VERSION" \
    -d "{
        \"license_key\": \"$LICENSE_KEY\",
        \"server_uuid\": \"$SERVER_UUID\",
        \"hostname\": \"$HOSTNAME\",
        \"ip\": \"$PUBLIC_IP\",
        \"os\": \"$OS\",
        \"distribution\": \"$DISTRO\",
        \"kernel\": \"$KERNEL\",
        \"cpu\": \"$CPU\",
        \"cpu_cores\": $CORES,
        \"ram\": \"$RAM\",
        \"disk\": \"$DISK\",
        \"panel_version\": \"$PANEL_VERSION\",
        \"email\": \"$CUSTOMER_EMAIL\"
    }" 2>/dev/null || echo "HTTP_ERROR")

# --- Handle Response ---
if echo "$RESPONSE" | grep -q "HTTP_ERROR"; then
    log "Failed to connect to licensing server."
    echo ""
    echo "ERROR: Could not reach licensing server at $ACTIVATE_URL"
    echo "Please check your internet connection and try again."
    echo ""
    echo "You can retry later by running:"
    echo "  bash $SCRIPT_DIR/license-activate.sh"
    exit 1
fi

# --- Parse Response ---
STATUS=$(echo "$RESPONSE" | python3 -c "import sys,json; d=json.load(sys.stdin); print(d.get('status','error'))" 2>/dev/null || echo "error")

if [ "$STATUS" = "success" ]; then
    # Save the full response as license
    echo "$RESPONSE" > "$LICENSE_FILE"
    chmod 600 "$LICENSE_FILE"

    # Extract key details
    EXPIRY=$(echo "$RESPONSE" | python3 -c "import sys,json; d=json.load(sys.stdin); print(d.get('expires_at','unknown'))" 2>/dev/null || echo "unknown")
    MODULES=$(echo "$RESPONSE" | python3 -c "import sys,json; d=json.load(sys.stdin); print(','.join(d.get('modules',[])))" 2>/dev/null || echo "all")
    CHANNEL=$(echo "$RESPONSE" | python3 -c "import sys,json; d=json.load(sys.stdin); print(d.get('update_channel','stable'))" 2>/dev/null || echo "stable")

    log "License activated successfully."
    echo ""
    echo "=================================================="
    echo " License Activated Successfully"
    echo "=================================================="
    echo ""
    echo "License Key:    $LICENSE_KEY"
    echo "Expires:        $EXPIRY"
    echo "Modules:        $MODULES"
    echo "Update Channel: $CHANNEL"
    echo ""
    echo "License stored at: $LICENSE_FILE"
    echo ""

    exit 0
else
    ERROR_MSG=$(echo "$RESPONSE" | python3 -c "import sys,json; d=json.load(sys.stdin); print(d.get('message','Unknown error'))" 2>/dev/null || echo "Unknown error")

    log "License activation failed: $ERROR_MSG"
    echo ""
    echo "=================================================="
    echo " License Activation Failed"
    echo "=================================================="
    echo ""
    echo "Reason: $ERROR_MSG"
    echo ""
    echo "Please verify your license key and try again."
    echo "If the problem persists, contact support."
    echo ""

    exit 1
fi
