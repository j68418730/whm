#!/bin/bash
# Planet Hosts - AlmaLinux Streaming Stack Builder
# Builds igloo, Icecast, liquidsoap, ezstream from source
# Repeatable: run anytime, skips already-installed components
set -e

BUILD_DIR="/usr/local/src"
INSTALL_LOG="/root/streaming_build.log"

log() { echo "[$(date +%H:%M:%S)] $@" | tee -a "$INSTALL_LOG"; }

if [ "$EUID" -ne 0 ]; then echo "Run as root."; exit 1; fi

# Dependencies
log "Installing build dependencies..."
dnf groupinstall -y "Development Tools" 2>/dev/null || true
dnf install -y cmake which pkgconfig openssl-devel libxslt-devel libxml2-devel \
  curl-devel libvorbis-devel speex-devel opus-devel sqlite-devel autoconf \
  automake libtool gettext-devel wget git

# ---------- 1. igloo ----------
if ! pkg-config --exists igloo 2>/dev/null; then
  log "Building igloo..."
  cd "$BUILD_DIR"
  # Try multiple sources
  for url in \
    "https://deb.debian.org/debian/pool/main/i/igloo/igloo_0.9.5.orig.tar.xz" \
    "https://snapshot.debian.org/archive/debian/20260601T000000Z/pool/main/i/igloo/igloo_0.9.5.orig.tar.xz"; do
    wget -q --timeout=10 -O igloo.tar.xz "$url" 2>/dev/null && file igloo.tar.xz | grep -q "XZ" && break
  done
  
  if [ -f igloo.tar.xz ] && file igloo.tar.xz | grep -q "XZ"; then
    tar xf igloo.tar.xz
    cd igloo-*
    cmake -B build -DCMAKE_INSTALL_PREFIX=/usr
    cmake --build build -j$(nproc)
    cmake --install build
    log "igloo installed."
  else
    log "WARNING: Could not download igloo. Icecast will not build."
  fi
else
  log "igloo already installed."
fi

# ---------- 2. Icecast ----------
if ! command -v icecast &>/dev/null; then
  log "Building Icecast..."
  cd "$BUILD_DIR"
  if [ -f /root/whm/Icecast-Server/configure.ac ]; then
    cp -a /root/whm/Icecast-Server /usr/local/src/icecast-src
    cd /usr/local/src/icecast-src
  else
    wget -q --timeout=10 "https://downloads.xiph.org/releases/icecast/icecast-2.5.0.tar.gz" -O icecast.tar.gz
    tar xzf icecast.tar.gz
    cd icecast-2.5.0
  fi
  
  ./configure 2>&1 | tail -5
  make -j$(nproc) 2>&1 | tail -5
  make install 2>&1 | tail -5
  log "Icecast installed."
else
  log "Icecast already installed."
fi

# ---------- 3. Ezstream ----------
if ! command -v ezstream &>/dev/null; then
  log "Building ezstream..."
  cd "$BUILD_DIR"
  wget -q --timeout=10 "https://downloads.xiph.org/releases/ezstream/ezstream-1.0.2.tar.gz" -O ezstream.tar.gz
  tar xzf ezstream.tar.gz
  cd ezstream-*
  ./configure 2>&1 | tail -3
  make -j$(nproc) 2>&1 | tail -3
  make install 2>&1 | tail -3
  log "Ezstream installed."
else
  log "Ezstream already installed."
fi

# ---------- 4. Liquidsoap ----------
if ! command -v liquidsoap &>/dev/null; then
  log "Building liquidsoap (via OPAM)..."
  if ! command -v opam &>/dev/null; then
    curl -sL https://raw.githubusercontent.com/ocaml/opam/master/shell/install.sh -o /tmp/opam_install.sh
    bash /tmp/opam_install.sh --disable-sandboxing 2>/dev/null || true
  fi
  opam init --disable-sandboxing -y 2>/dev/null || true
  eval "$(opam env)"
  opam install -y liquidsoap 2>&1 | tail -5 || log "WARNING: liquidsoap build failed."
  log "Liquidsoap installed."
else
  log "Liquidsoap already installed."
fi

log ""
log "=== Build Complete ==="
log "Icecast: $(command -v icecast || echo 'NOT INSTALLED')"
log "Ezstream: $(command -v ezstream || echo 'NOT INSTALLED')"
log "Liquidsoap: $(command -v liquidsoap || echo 'NOT INSTALLED')"
echo ""
echo "Re-run this script anytime to install missing components."
