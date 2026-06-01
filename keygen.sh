#!/bin/bash

# =========================================================
# Planet Hosts Master Panel - License Key Generator
# Generates an RSA key pair and creates a signed license.
# =========================================================

set -eo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

PRIVATE_KEY="$SCRIPT_DIR/license_private.pem"
PUBLIC_KEY="$SCRIPT_DIR/config/license_public.pem"
LICENSE_FILE="$SCRIPT_DIR/license.key"
KEY_BITS=4096

clear

echo "=============================================="
echo " Planet Hosts License Key Generator"
echo "=============================================="
echo ""

# Check for openssl
if ! command -v openssl >/dev/null 2>&1; then
    echo "Error: openssl is required but not installed."
    echo "Install it with: sudo yum install openssl"
    exit 1
fi

# =========================================================
# Generate key pair
# =========================================================

if [ -f "$PRIVATE_KEY" ]; then
    read -p "Private key exists. Regenerate? (y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo "Using existing key pair."
    else
        echo "Generating new RSA key pair ($KEY_BITS bits)..."
        openssl genrsa -out "$PRIVATE_KEY" "$KEY_BITS"
        openssl rsa -in "$PRIVATE_KEY" -pubout -out "$PUBLIC_KEY"
        echo "Key pair generated."
    fi
else
    echo "Generating new RSA key pair ($KEY_BITS bits)..."
    openssl genrsa -out "$PRIVATE_KEY" "$KEY_BITS"
    openssl rsa -in "$PRIVATE_KEY" -pubout -out "$PUBLIC_KEY"
    echo "Key pair generated."
fi

# =========================================================
# Gather license info
# =========================================================

echo ""
echo "License Information"
echo "-------------------"

if [ "$1" = "--auto" ]; then
    LICENSEE="Planet Hosts Auto Install"
    MAX_DOMAINS=0
    EXPIRY="never"
else
    read -p "Licensee (company / person): " LICENSEE
    read -p "Max domains (0 = unlimited): " MAX_DOMAINS
    read -p "Expiry date (YYYY-MM-DD, empty = never): " EXPIRY

    if [ -z "$LICENSEE" ]; then
        LICENSEE="Planet Hosts User"
    fi

    if [ -z "$MAX_DOMAINS" ]; then
        MAX_DOMAINS=0
    fi

    if [ -z "$EXPIRY" ]; then
        EXPIRY="never"
    fi
fi

# Machine ID (MAC-based)
MACHINE_ID=$(ip link 2>/dev/null | grep -oP '(?<=link/ether )[\da-f:]+' | head -1 || echo "unknown")
if [ -z "$MACHINE_ID" ] || [ "$MACHINE_ID" = "unknown" ]; then
    MACHINE_ID="$(hostname)-$(date +%s)"
fi

# Generate unique license ID
LICENSE_ID="PH-$(openssl rand -hex 8 | tr '[:lower:]' '[:upper:]')"
ISSUED=$(date +%Y-%m-%d)

# =========================================================
# Build and sign payload
# =========================================================

PAYLOAD=$(cat <<EOF
{
  "license_id": "$LICENSE_ID",
  "licensee": "$LICENSEE",
  "issued": "$ISSUED",
  "expiry": "$EXPIRY",
  "machine_id": "$MACHINE_ID",
  "max_domains": $MAX_DOMAINS
}
EOF
)

SIGNATURE=$(echo -n "$PAYLOAD" | openssl dgst -sha256 -sign "$PRIVATE_KEY" | openssl base64 -A)

# =========================================================
# Write license file
# =========================================================

cat > "$LICENSE_FILE" <<EOF
-----BEGIN PLANET HOSTS LICENSE-----
$SIGNATURE
-----BEGIN LICENSE DATA-----
$PAYLOAD
-----END LICENSE DATA-----
-----END PLANET HOSTS LICENSE-----
EOF

echo ""
echo "=============================================="
echo " License Generated"
echo "=============================================="
echo ""
echo "License ID:  $LICENSE_ID"
echo "Licensee:    $LICENSEE"
echo "Issued:      $ISSUED"
echo "Expiry:      $EXPIRY"
echo "Domains:     $MAX_DOMAINS"
echo "Machine ID:  $MACHINE_ID"
echo ""
echo "Files created:"
echo "  $PRIVATE_KEY   (KEEP SECURE - do not share)"
echo "  $PUBLIC_KEY    (embedded in panel)"
echo "  $LICENSE_FILE  (deploy to server)"
echo ""
echo "To install license on server:"
echo "  cp license.key /var/www/radiohosting/license.key"
echo ""
echo "To verify:"
echo "  ./keygen.sh --verify"
echo ""

# =========================================================
# Optional: verify
# =========================================================

if [ "$1" = "--verify" ] || [ "$2" = "--verify" ]; then
    echo "Verifying signature..."
    SIGNATURE_CHECK=$(grep -A1 "BEGIN PLANET HOSTS LICENSE" "$LICENSE_FILE" | tail -1)
    PAYLOAD_CHECK=$(sed -n '/BEGIN LICENSE DATA/,/END LICENSE DATA/p' "$LICENSE_FILE" | grep -v "BEGIN LICENSE DATA" | grep -v "END LICENSE DATA")
    echo -n "$PAYLOAD_CHECK" | openssl base64 -d -A 2>/dev/null | openssl dgst -sha256 -verify "$PUBLIC_KEY" -signature <(echo -n "$SIGNATURE_CHECK" | openssl base64 -d -A) 2>/dev/null && echo "VALID" || echo "INVALID"
fi
