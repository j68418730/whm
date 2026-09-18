#!/bin/bash
# Planet Hosts - Silent update check (refreshes storage/update_available.json).
# Runs via cron (/etc/cron.d/planet-hosts-updates). No logging to update.log.
BASE_PATH="$(cd "$(dirname "$0")/.." && pwd)"
chown www-data:www-data "$BASE_PATH/storage" 2>/dev/null || true
# Resolve a PHP with pdo_mysql (keeps check working even if the distro default lacks it)
PHP_BIN="$(command -v php8.4 || command -v php8.3 || command -v php8.2 || command -v php 2>/dev/null || echo /usr/bin/php)"
"$PHP_BIN" "$BASE_PATH/scripts/updates_check.php" 10 > /dev/null 2>&1
chown www-data:www-data "$BASE_PATH/storage/update_available.json" 2>/dev/null || true
chmod 644 "$BASE_PATH/storage/update_available.json" 2>/dev/null || true
exit 0