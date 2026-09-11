#!/bin/bash
# Planet Hosts - Silent update check (refreshes storage/update_available.json).
# Runs via cron (/etc/cron.d/planet-hosts-updates). No logging.

BASE_PATH="$(cd "$(dirname "$0")/.." && pwd)"
[ -d "$BASE_PATH/.git" ] || exit 0

cd "$BASE_PATH" || exit 1
git fetch origin --quiet 2>/dev/null

CURRENT=$(git rev-parse --short HEAD 2>/dev/null || echo unknown)
UPSTREAM=$(git rev-parse --short origin/master 2>/dev/null || echo unknown)
BEHIND=$(git rev-list HEAD..origin/master --count 2>/dev/null || echo 0)
if [ "$BEHIND" -gt 0 ]; then AVAIL=true; else AVAIL=false; fi

JSON="{\"current\":\"$CURRENT\",\"upstream\":\"$UPSTREAM\",\"behind\":$BEHIND,\"update_available\":$AVAIL,\"checked_at\":\"$(date '+%Y-%m-%d %H:%M:%S')\"}"
echo "$JSON" > "$BASE_PATH/storage/update_available.json"
chown www-data:www-data "$BASE_PATH/storage/update_available.json" 2>/dev/null
chmod 644 "$BASE_PATH/storage/update_available.json" 2>/dev/null

exit 0