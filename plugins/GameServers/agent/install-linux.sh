#!/bin/bash
# ==============================================================
#  Planet Hosts Game Node Agent — Linux Installer
#  Installs the agent as a systemd service that auto-starts on boot
#  and polls the panel over outbound HTTPS (no inbound ports needed).
#
#  Usage:
#    install-linux.sh
#      interactively prompts for panel URL, token and base dir
#
#    PANEL_URL=https://... NODE_TOKEN=... BASE_DIR=/var/gameservers \
#      ./install-linux.sh          (non-interactive)
#
#  Requirements: bash, curl, sudo. Node.js is installed automatically
#  when missing (apt / dnf / yum / apk / pacman are all supported).
# ==============================================================
set -e

INSTALL_DIR="${INSTALL_DIR:-/opt/ph-agent}"
LOG_FILE=""
interactive() { tty -s; }

say() { echo -e "[PH-AGENT] $1"; }
die() { echo -e "[PH-AGENT] ERROR: $1" >&2; exit 1; }

# ── Collect config ────────────────────────────────────────────
if [ -n "$PANEL_URL" ]; then PANEL_URL="${PANEL_URL%/}"; else
  read -rp "Panel URL (e.g. https://planet-hosts.com): " PANEL_URL
  [ -z "$PANEL_URL" ] && PANEL_URL="https://planet-hosts.com"
  PANEL_URL="${PANEL_URL%/}"
fi
if [ -z "$NODE_TOKEN" ]; then
  read -rp "Node token (from Admin -> Games -> Game Nodes): " NODE_TOKEN
  [ -z "$NODE_TOKEN" ] && die "A node token is required."
fi
BASE_DIR="${BASE_DIR:-/var/gameservers}"
if [ -z "$BASE_DIR" ] && interactive; then
  read -rp "Game install directory (default: /var/gameservers): " BASE_DIR
fi
[ -z "$BASE_DIR" ] && BASE_DIR="/var/gameservers"

# ── Optional Steam login (only for games that must be purchased on Steam) ──
STEAM_USER="${STEAM_USER:-anonymous}"
STEAM_PASS=""
if [ "${STEAM_USER:-anonymous}" = "anonymous" ] && interactive; then
  read -rp "Will you install games purchased on Steam (ARK, Valheim...)? [y/N] " NEED_STEAM
  if [[ "${NEED_STEAM:-n}" =~ ^[yY] ]]; then
    read -rp "Your Steam username: " STEAM_USER
    read -rsp "Your Steam password: " STEAM_PASS; echo
    [ -z "$STEAM_USER" ] && die "Steam username required."
  fi
fi

# ── Install Node.js if missing ────────────────────────────────
ensure_node() {
  if command -v node >/dev/null 2>&1; then
    say "Node.js found: $(node -v)"
    return
  fi
  say "Node.js not found — installing..."
  if command -v apt-get >/dev/null 2>&1; then
    curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash - >/dev/null 2>&1
    sudo apt-get install -y nodejs >/dev/null 2>&1
  elif command -v dnf >/dev/null 2>&1; then
    sudo dnf install -y nodejs >/dev/null 2>&1
  elif command -v yum >/dev/null 2>&1; then
    sudo yum install -y nodejs >/dev/null 2>&1
  elif command -v apk >/dev/null 2>&1; then
    sudo apk add --no-cache nodejs npm >/dev/null 2>&1
  elif command -v pacman >/dev/null 2>&1; then
    sudo pacman -S --noconfirm nodejs >/dev/null 2>&1
  elif command -v brew >/dev/null 2>&1; then
    brew install node >/dev/null 2>&1
  else
    die "Unsupported distro — install Node.js >= 18 manually, then re-run."
  fi
  command -v node >/dev/null 2>&1 || die "Node.js installation failed."
  say "Node.js installed: $(node -v)"
}

ensure_node

# ── Install files (self-copies from the extracted package) ───
say "Installing to $INSTALL_DIR ..."
sudo mkdir -p "$INSTALL_DIR"
SRC_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
sudo cp "$SRC_DIR/agent.js" "$INSTALL_DIR/agent.js" 2>/dev/null \
  || curl -fsSL "$PANEL_URL/admin/games/nodes/agent-source" -o /tmp/ph-agent.js 2>/dev/null \
  || die "agent.js not found beside the installer. Keep it in the same folder, or ensure the panel is reachable."

# ── Write config ──────────────────────────────────────────────
sudo tee "$INSTALL_DIR/agent-config.json" >/dev/null <<EOF
{
  "panel_url":        "$PANEL_URL",
  "node_token":       "$NODE_TOKEN",
  "base_dir":         "$BASE_DIR",
  "poll_interval_ms": 10000,
  "steamcmd":         "steamcmd",
  "steam_user":       "$STEAM_USER",
  "steam_pass":       "$STEAM_PASS"
}
EOF
sudo chmod 644 "$INSTALL_DIR/agent-config.json" 2>/dev/null || true
sudo mkdir -p "$BASE_DIR"
sudo chmod 755 "$BASE_DIR" 2>/dev/null || true

# ── systemd unit ──────────────────────────────────────────────
UNIT="ph-agent.service"
if command -v systemctl >/dev/null 2>&1; then
  UNIT_PATH="/etc/systemd/system/ph-agent.service"
  [ -f "$SRC_DIR/ph-agent.service" ] && sudo cp "$SRC_DIR/ph-agent.service" "$UNIT_PATH"
  [ "$INSTALL_DIR" != "/opt/ph-agent" ] \
    && sudo sed -i "s#/opt/ph-agent#$INSTALL_DIR#g" "$UNIT_PATH"
  NODE_BIN="$(command -v node)"
  if [ "$NODE_BIN" != "/usr/bin/node" ]; then
    sudo sed -i "s#/usr/bin/node#$NODE_BIN#g" "$UNIT_PATH"
  fi

  sudo systemctl daemon-reload
  sudo systemctl enable ph-agent.service >/dev/null 2>&1 || true
  sudo systemctl restart ph-agent.service || true
  LOG_FILE="journalctl -u ph-agent"
else
  # No systemd (Docker / minimal) — run in foreground
  say "No systemd detected — starting agent in foreground backgrounded via nohup."
  [ "$NODE_BIN" = "" ] && NODE_BIN="$(command -v node)"
  sudo sh -c "cd $INSTALL_DIR && nohup $NODE_BIN agent.js >> agent.log 2>&1 &"
  LOG_FILE="tail -f $INSTALL_DIR/agent.log"
fi

say "Done. Agent installed and started."
say "Panel:     $PANEL_URL"
say "Base dir:  $BASE_DIR"
say "Check status:         systemctl status ph-agent.service"
say "View logs (Linux):       $LOG_FILE"
echo ""
say "If the node does not show ONLINE within ~10s, re-check the token at Admin -> Games -> Game Nodes."