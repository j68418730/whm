#!/bin/bash
# 03-yara — pattern matching for web malware (PHP shells, miners, obfuscation)
set +e
DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$DIR/lib.sh"

sc_log "yara" "install" "RUNNING" "Installing YARA"
if command -v yara >/dev/null 2>&1; then
    sc_log "yara" "install" "SKIP" "already installed"
else
    if ! $PKG_INSTALL yara >/dev/null 2>&1; then
        sc_log "yara" "install" "BUILD" "apt failed, building from source"
        apt-get install -y build-essential libjansson-dev libssl-dev libmagic-dev >/dev/null 2>&1
        cd /tmp
        git clone --depth 1 https://github.com/VirusTotal/yara.git >/dev/null 2>&1
        if [ -d /tmp/yara ]; then
            cd /tmp/yara
            ./bootstrap.sh >/dev/null 2>&1
            ./configure --with-crypto >/dev/null 2>&1
            make -j"$(nproc)" >/dev/null 2>&1
            make install >/dev/null 2>&1
            ldconfig
        fi
    fi
fi

mkdir -p /usr/local/share/planethosts-security/yara
cat > /usr/local/share/planethosts-security/yara/web-malware.yar << 'EOF'
rule PHP_EvalBase64
{
    meta: description = "Encoded PHP: eval(base64_decode(...))"
    strings:
        $a = "eval(base64_decode"
        $b = "eval(gzinflate(base64_decode"
        $c = "eval(str_rot13"
    condition: any of them
}

rule PHP_ShellExec
{
    meta: description = "Remote execution calls"
    strings:
        $a = "shell_exec("
        $b = "passthru("
        $c = "proc_open("
        $d = "popen("
    condition: any of them
}

rule PHP_Webshell_Names
{
    meta: description = "Known webshell markers"
    strings:
        $a = "c99shell"
        $b = "r57shell"
        $c = "b374k"
        $d = "backdoor"
        $e = "webshell"
    condition: any of them
}

rule Obfuscated_JS_Iframe
{
    meta: description = "Hidden iframe injection"
    strings:
        $a = "<iframe"
        $b = "document.write('<script"
    condition: all of them
}

rule Crypto_Miner_JS
{
    meta: description = "Cryptocurrency miner markers"
    strings:
        $a = "coinhive"
        $b = "miner."
        $c = "cryptonight"
    condition: any of them
}
EOF

cat > /usr/local/bin/ph-yarascan << 'EOF'
#!/bin/bash
# Usage: ph-yarascan [dir]
DIR="${1:-/home}"
RULES="/usr/local/share/planethosts-security/yara/web-malware.yar"
LOG="/var/log/planethosts/yarascan.log"
mkdir -p /var/log/planethosts
echo "[$(date '+%Y-%m-%d %H:%M:%S')] scan start $DIR" >> "$LOG"
if command -v yara >/dev/null 2>&1 && [ -f "$RULES" ]; then
    find "$DIR" -type f \( -name "*.php" -o -name "*.phtml" -o -name "*.php5" -o -name "*.js" \) \
        -not -path "*/node_modules/*" -not -path "*/.cache/*" 2>/dev/null | while read -r f; do
        yara "$RULES" "$f" >> "$LOG" 2>/dev/null && echo "HIT: $f" >> "$LOG"
    done
fi
echo "[$(date '+%Y-%m-%d %H:%M:%S')] scan done" >> "$LOG"
EOF
chmod +x /usr/local/bin/ph-yarascan

VERSION="$(yara --version 2>/dev/null || echo installed)"
sc_status yara ok "$VERSION"
sc_log "yara" "install" "OK" "YARA + web-malware rules installed ($VERSION)"
exit 0
