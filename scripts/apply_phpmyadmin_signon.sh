#!/bin/bash
# Ensure phpMyAdmin uses the panel's signon session (public/pma_autologin.php)
# instead of cookie/config auth with a baked-in root password.
# Idempotent; safe to run at every install/update.

set -e
CFG=/etc/phpmyadmin/config.inc.php

[ -f "$CFG" ] || { echo "[pma] $CFG missing - skipping"; exit 0; }

if grep -q "SignonSession" "$CFG" 2>/dev/null; then
    echo "[pma] signon mode already configured"
    exit 0
fi

php << 'PHP'
<?php
$f = '/etc/phpmyadmin/config.inc.php';
$c = @file_get_contents($f);
if ($c === false) { echo "[pma] cannot read $f\n"; exit(0); }

$c = preg_replace("/auth_type']\s*=\s*'(config|cookie)';/", "auth_type'] = 'signon';", $c);
if (strpos($c, "SignonSession") === false) {
    $old = "auth_type'] = 'signon';";
    $new  = "auth_type'] = 'signon';\n"
          . "\$cfg['Servers'][\$i]['SignonSession'] = 'PHPSESSID';\n"
          . "\$cfg['Servers'][\$i]['SignonURL'] = '/pma_autologin.php';\n"
          . "\$cfg['Servers'][\$i]['LogoutURL'] = '/admin/login';";
    $pos  = strpos($c, $old);
    if ($pos !== false) {
        $c = substr($c, 0, $pos) . $new . substr($c, $pos + strlen($old));
    }
}
@file_put_contents($f, $c);
echo "[pma] signon mode applied\n";
PHP

# phpMyAdmin 5.2+ reads PMA_single_signon_user/password from the session.
# Make sure the panel's public/pma_autologin.php sets BOTH key sets
# (PMA_single_signon_* and legacy PMA_signon_*).
AL=/var/www/radiohosting/public/pma_autologin.php
if [ -f "$AL" ] && ! grep -q "PMA_single_signon_user" "$AL"; then
    echo "[pma] WARNING: $AL missing PMA_single_signon_user - signon will fail on phpMyAdmin 5.2+"
fi

# Only expose /phpmyadmin on panel ports (2082/2086/2087), never on port 80.
# The global debian phpmyadmin.conf alias must stay disabled:
if [ -f /etc/apache2/conf-enabled/phpmyadmin.conf ]; then
    a2disconf phpmyadmin >/dev/null 2>&1 || true
fi
if ! grep -q "Alias /phpmyadmin /usr/share/phpmyadmin" /etc/apache2/sites-enabled/panel-ports.conf 2>/dev/null; then
    echo "[pma] WARNING: /phpmyadmin alias not present in /etc/apache2/sites-enabled/panel-ports.conf"
fi
exit 0