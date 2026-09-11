#!/bin/bash
# Planet Hosts - Silent update check (refreshes storage/update_available.json).
# Runs via cron (/etc/cron.d/planet-hosts-updates). No logging to update.log.
BASE_PATH="$(cd "$(dirname "$0")/.." && pwd)"
chown www-data:www-data "$BASE_PATH/storage" 2>/dev/null || true
/usr/bin/php "$BASE_PATH/scripts/updates_check.php" 10 > /dev/null 2>&1
chown www-data:www-data "$BASE_PATH/storage/update_available.json" 2>/dev/null || true
chmod 644 "$BASE_PATH/storage/update_available.json" 2>/dev/null || true
exit 0