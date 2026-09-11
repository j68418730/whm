#!/bin/bash
# Planet Hosts — Updater repair patch (TEST/DEV servers only).
#
# Replaces the old/broken updater components with the fixed ones from the
# pinned Git commit. It does NOT touch VERSION.json, storage release state,
# or any version numbers — the installed version only changes when the real
# updater (scripts/update.sh) actually runs, at which point this fix is
# already in place.
#
# Safe to re-run (idempotent). Only affects the updater itself, nothing else.
#
#   sudo bash scripts/patch_updater.sh          # default pin = fixed commit
#   PIN=<sha> sudo bash scripts/patch_updater.sh  # pull updater files from another commit
set -e

# Locate the app root. Works whether this script runs from scripts/, the app
# root, or anywhere else (e.g. /tmp) - we walk up until we find public/index.php.
SCRIPT_DIR="$(cd "$(dirname "$0")" 2>/dev/null && pwd)"
BASE_PATH=""
cur="${SCRIPT_DIR:-$(pwd)}"
while [ -n "$cur" ]; do
    if [ -f "$cur/public/index.php" ]; then BASE_PATH="$cur"; break; fi
    parent="$(dirname "$cur")"
    [ "$parent" = "$cur" ] && break
    cur="$parent"
done
[ -n "$BASE_PATH" ] || BASE_PATH="/var/www/radiohosting"
echo "==> App root: $BASE_PATH"

if [ ! -f "$BASE_PATH/public/index.php" ]; then
    echo "ERROR: no app found at $BASE_PATH (public/index.php missing)." >&2
    exit 1
fi

PIN="${PIN:-993e84ae7e492f2c1c4baec8e9c3225d27e3f7d1}"
RAW="https://raw.githubusercontent.com/j68418730/whm/$PIN"
BK="$BASE_PATH/storage/updater_patch_backup_$(date +%Y%m%d_%H%M%S)"

FILES="
scripts/update.sh
scripts/updates_check.php
scripts/check_update.sh
scripts/migrate.php
scripts/setup_storage.sh
core/Updates.php
core/Database.php
admin/Controllers/UpdateController.php
"

echo "==> Planet Hosts updater repair patch (pin $PIN)"
[ "$(id -u)" = "0" ] || { echo "ERROR: run as root (sudo bash scripts/patch_updater.sh)" >&2; exit 1; }

# Verify connectivity to the pinned commit before touching anything
if ! curl -sfL -m 20 "$RAW/scripts/update.sh" -o /dev/null; then
    echo "ERROR: cannot fetch updater files from $RAW" >&2
    echo "       GitHub raw must be reachable from this server." >&2
    exit 1
fi

mkdir -p "$BK"

for rel in $FILES; do
    dest="$BASE_PATH/$rel"
    if [ -f "$dest" ]; then
        mkdir -p "$BK/$(dirname "$rel")"
        cp -a "$dest" "$BK/$rel"
    fi
    mkdir -p "$(dirname "$dest")"
    echo -n "  updating $rel ... "
    curl -sfL -m 60 "$RAW/$rel" -o "$dest.tmp" || { echo "FAILED"; exit 1; }
    mv -f "$dest.tmp" "$dest"
    echo "ok"
    case "$rel" in
        *.sh) chmod 755 "$dest" ;;
        *)     chmod 644 "$dest" ;;
    esac
    chown www-data:www-data "$dest" 2>/dev/null || true
done

# www-data passwordless sudo for panel-triggered update/rollback.
# Panel always invokes the ABSOLUTE path: sudo /bin/bash <base>/scripts/update.sh [--rollback]
SUDOERS_FILE=/etc/sudoers.d/radiohosting-update
{
    echo "# Allow WHM UI triggered self-update/rollback"
    echo "# Panel invokes: sudo /bin/bash $BASE_PATH/scripts/update.sh [--rollback]"
    echo "www-data ALL=(root) NOPASSWD: /bin/bash $BASE_PATH/scripts/update.sh"
    echo "www-data ALL=(root) NOPASSWD: /bin/bash $BASE_PATH/scripts/update.sh *"
} > "$SUDOERS_FILE"
chmod 440 "$SUDOERS_FILE"
if ! visudo -c >/dev/null 2>&1; then
    rm -f "$SUDOERS_FILE"
    echo "ERROR: sudoers validation failed; removed $SUDOERS_FILE" >&2
    exit 1
fi
echo "==> sudoers: $SUDOERS_FILE (visudo -c OK)"

bash -n "$BASE_PATH/scripts/update.sh" && echo "==> lint: scripts/update.sh OK"
php -l "$BASE_PATH/scripts/updates_check.php" >/dev/null && echo "==> lint: updates_check.php OK"
php -l "$BASE_PATH/core/Updates.php" >/dev/null && echo "==> lint: core/Updates.php OK"
php -l "$BASE_PATH/core/Database.php" >/dev/null && echo "==> lint: core/Database.php OK"
php -l "$BASE_PATH/admin/Controllers/UpdateController.php" >/dev/null && echo "==> lint: UpdateController.php OK"

echo "==> Backup of previous updater files: $BK"
echo "==> Patch applied. Version numbers unchanged; run scripts/update.sh to update."
echo "==> Tip: after ANY release is installed by the updater, re-run this patch"
echo "==>      (until a release that already bundles these fixes is installed)."