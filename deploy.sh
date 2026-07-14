#!/usr/bin/env bash
#
# deploy.sh — Planet Hosts Studio auto-deploy helper (run on the SERVER).
#
# Applies every idempotent SQL file under database/migrations/ to the live
# database. All migrations use CREATE TABLE IF NOT EXISTS / ADD COLUMN IF
# NOT EXISTS, so re-running on every deploy is safe.
#
# Run AFTER `git pull origin master`:
#     cd /var/www/radiohosting && git pull origin master && ./deploy.sh
#
# Override credentials via environment variables if they differ:
#     DB_HOST  DB_NAME  DB_USER  DB_PASS
#
set -uo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
MIG_DIR="$APP_DIR/database/migrations"

DB_HOST="${DB_HOST:-localhost}"
DB_NAME="${DB_NAME:-radiohosting}"
DB_USER="${DB_USER:-root}"
DB_PASS="${DB_PASS:-Skylinehosting171}"

if ! command -v mysql >/dev/null 2>&1; then
    echo "ERROR: 'mysql' client not found in PATH." >&2
    exit 1
fi

if [ ! -d "$MIG_DIR" ]; then
    echo "ERROR: migrations dir not found: $MIG_DIR" >&2
    exit 1
fi

echo "==> Applying migrations from $MIG_DIR (db: $DB_NAME@$DB_HOST)"

shopt -s nullglob
count=0
for f in "$MIG_DIR"/*.sql; do
    echo "  -> $(basename "$f")"
    if mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$f"; then
        count=$((count + 1))
    else
        echo "  !! FAILED: $(basename "$f") — review the error above." >&2
    fi
done
shopt -u nullglob

echo "==> Done. Applied $count migration file(s)."
