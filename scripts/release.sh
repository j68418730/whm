#!/bin/bash
# Planet Hosts - Release tool (run ONCE per release, from a clean pushed repo).
# Usage: sudo bash scripts/release.sh <version> <version_code> [channel] ["release notes"]
#   channel: stable (default) | lts | release | beta | dev
#
# What it does:
#  1. Verifies the working tree is clean and on master.
#  2. Downloads the GitHub archive for the current HEAD commit and computes its
#     SHA-256 (deterministic lock of the package a server will receive).
#  3. Writes VERSION.json (release manifest) pointing at that commit + checksum.
#  4. Tags the release commit (v<version>) and commits the manifest.
#  5. Pushes both to origin/master.

set -e
BASE_PATH="$(cd "$(dirname "$0")/.." && pwd)"
cd "$BASE_PATH"

VER="${1:?usage: release.sh <version> <version_code> [channel] [notes]}"
CODE="${2:?usage: release.sh <version> <version_code> [channel] [notes]}"
CHAN="${3:-stable}"
NOTES="$4"
case "$CHAN" in stable|lts|release|beta|dev) ;; *) echo "Invalid channel: $CHAN" >&2; exit 1 ;; esac

if [ "$(git branch --show-current)" != "master" ]; then echo "Must release from master." >&2; exit 1; fi
# Block only on tracked changes; untracked runtime files (storage/, .env, uploads) are fine.
if [ -n "$(git status --porcelain | grep -v '^??')" ]; then echo "Working tree has tracked changes. Commit/stash first." >&2; exit 1; fi

# Need the current HEAD pushed so the tarball download target exists upstream.
LOCAL="$(git rev-parse HEAD)"
REMOTE="$(git rev-parse origin/master 2>/dev/null || echo '')"
if [ "$LOCAL" != "$REMOTE" ]; then
    echo "HEAD ($LOCAL) is not pushed to origin/master ($REMOTE). Push first." >&2
    exit 1
fi
HEAD="$LOCAL"
echo "Releasing commit $HEAD as Ph-Whm $VER (code $CODE, channel $CHAN)"

# Previous release (for require_version_code + migrations diff)
PREV_CODE=0
PREV_TAG=""
if [ -f VERSION.json ]; then
    PREV_CODE=$(php -r '$d=json_decode(file_get_contents("VERSION.json"),true); echo (int)($d["version_code"]??0);' 2>/dev/null || echo 0)
fi
LATEST_TAG=$(git tag --sort=-version:refname | head -1 || true)
[ -n "$LATEST_TAG" ] && PREV_TAG="$LATEST_TAG"

# Migrations shipped by this release
MIGS=$(git diff --name-only "${PREV_TAG:-$(git rev-list --max-parents=0 HEAD)}..HEAD" -- database/migrations 2>/dev/null | sed 's|database/migrations/||' | grep -E '\.(sql|php)$' | tac | awk '!seen[$0]++' | head -50)
if [ -z "$MIGS" ]; then MIGLIST="[]"; else
  MIGLIST="["
  first=1
  while IFS= read -r m; do [ -z "$m" ] && continue; [ "$first" = "1" ] || MIGLIST="$MIGLIST,"; MIGLIST="$MIGLIST\"$m\""; first=0; done <<< "$MIGS"
  MIGLIST="$MIGLIST]"
fi

# Deterministic package: GitHub tarball of this exact commit
TARBALL=$(mktemp /tmp/ph-release-XXXXXX.tar.gz)
echo "Downloading archive of $HEAD to compute checksum (this may take a minute)..."
curl -sfL -m 600 -o "$TARBALL" "https://codeload.github.com/j68418730/whm/tar.gz/$HEAD"
SHA=$(sha256sum "$TARBALL" | awk '{print $1}')
rm -f "$TARBALL"

cat > VERSION.json <<EOF
{
  "version": "$VER",
  "version_code": $CODE,
  "channel": "$CHAN",
  "release_date": "$(date '+%Y-%m-%d')",
  "release_type": "Core Release",
  "security": false,
  "require_version_code": $PREV_CODE,
  "php_required": ">=7.4",
  "requires_reboot": false,
  "requires_services_restart": ["apache2"],
  "migrations": $MIGLIST,
  "download": "https://codeload.github.com/j68418730/whm/tar.gz/$HEAD",
  "checksum": "$SHA",
  "target_sha": "$HEAD",
  "release_notes": "${NOTES:-}"
}
EOF

echo "Manifest written. Checksum: $SHA"
echo "Tagging v$VER at $HEAD and committing manifest..."
git tag -a "v$VER" "$HEAD" -m "Planet Hosts release $VER (channel $CHAN)"
git add VERSION.json
git commit -m "Release $VER - manifest (channel $CHAN, code $CODE)"
git push origin master --tags

echo "Release $VER published."
echo "  target_sha: $HEAD"
echo "  checksum:   $SHA"
echo "  source:     https://raw.githubusercontent.com/j68418730/whm/master/VERSION.json"