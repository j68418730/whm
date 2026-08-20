#!/bin/bash
# ==============================================================
#  Planet Hosts Game Node Agent — macOS Installer
#  Installs the agent as a launchd LaunchAgent that auto-starts on
#  login and polls the panel over outbound HTTPS (no inbound ports).
#
#  Usage:
#    install-macos.sh
#      interactively prompts for panel URL, token and base dir
#
#    PANEL_URL=https://... NODE_TOKEN=... BASE_DIR=/Users/you/Games \
#      ./install-macos.sh          (non-interactive)
#
#  Requires: macOS 12+, Node.js >= 18 (Homebrew-installed automatically
#  when missing:  brew install node).
# ==============================================================
set -e

INSTALL_DIR="${INSTALL_DIR:-$HOME/ph-agent}"
AGENT_LABEL="com.planethosts.ph-agent"
LOG_FILE=""
say() { echo -e "[PH-AGENT] $1"; }
die() { echo -e "[PH-AGENT] ERROR: $1" >&2; exit 1; }
interactive() { tty -s; }

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
BASE_DIR="${BASE_DIR:-$HOME/PlanetHostsGames}"
if [ -z "$BASE_DIR" ] && interactive; then
  read -rp "Game install directory (default: $HOME/PlanetHostsGames): " BASE_DIR
fi
[ -z "$BASE_DIR" ] && BASE_DIR="$HOME/PlanetHostsGames"

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

# ── Node ──────────────────────────────────────────────────────
if ! command -v node >/dev/null 2>&1; then
  say "Node.js missing — installing via Homebrew..."
  command -v brew >/dev/null 2>&1 || /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
  brew install node
fi
NODE_BIN="$(command -v node)"
say "Node: $NODE_BIN ($(node -v))"

# ── Install files ─────────────────────────────────────────────
say "Installing to $INSTALL_DIR ..."
mkdir -p "$INSTALL_DIR" "$HOME/Library/LaunchAgents"
SRC_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cp "$SRC_DIR/agent.js" "$INSTALL_DIR/agent.js" 2>/dev/null \
  || curl -fsSL "$PANEL_URL/admin/games/nodes/agent-source" -o "$INSTALL_DIR/agent.js" 2>/dev/null \
  || die "agent.js not found beside the installer. Keep it in the same folder, or ensure the panel is reachable."
chmod +x "$INSTALL_DIR/agent.js"

cat > "$INSTALL_DIR/agent-config.json" <<EOF
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

# ── launchd LaunchAgent ───────────────────────────────────────
PLIST="$HOME/Library/LaunchAgents/$AGENT_LABEL.plist"
cat > "$PLIST" <<EOF
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
<dict>
  <key>Label</key><string>$AGENT_LABEL</string>
  <key>ProgramArguments</key>
  <array>
    <string>$NODE_BIN</string>
    <string>$INSTALL_DIR/agent.js</string>
  </array>
  <key>WorkingDirectory</key><string>$INSTALL_DIR</string>
  <key>RunAtLoad</key><true/>
  <key>KeepAlive</key><true/>
  <key>StandardOutPath</key><string>$INSTALL_DIR/agent.log</string>
  <key>StandardErrorPath</key><string>$INSTALL_DIR/agent.log</string>
</dict>
</plist>
EOF

launchctl unload "$PLIST" 2>/dev/null || true
launchctl load "$PLIST" 2>/dev/null || true
LOG_FILE="tail -f $INSTALL_DIR/agent.log"

say "Done. Agent installed and started (LaunchAgent $AGENT_LABEL)."
say "Panel:     $PANEL_URL"
say "Base dir:  $BASE_DIR"
say "View logs: $LOG_FILE"
echo ""
say "If the node does not show ONLINE within ~10s, re-check the token at Admin -> Games -> Game Nodes."