#!/bin/bash
# Planet Hosts — Apache vhost / catch-all setup
# 1. Disables PrivateTmp for apache2 (so `systemctl reload apache2` works and
#    /tmp writes by the web worker are visible to root's sudo for subdomain
#    vhost/zone creation).
# 2. Installs a catch-all default vhost so unknown hostnames show a
#    "Site Not Found" page instead of exposing the panel.
set -e

echo "[1/3] Disable PrivateTmp for apache2"
sudo mkdir -p /etc/systemd/system/apache2.service.d
cat > /etc/systemd/system/apache2.service.d/override.conf << 'EOF'
[Service]
PrivateTmp=false
EOF
sudo systemctl daemon-reload
echo "  done"

echo "[2/3] Install catch-all vhost"
mkdir -p /var/www/radiohosting/public/_catchall
cp /var/www/radiohosting/public/_catchall/index.html /var/www/radiohosting/public/_catchall/index.html 2>/dev/null || true
cat > /etc/apache2/sites-available/000-catchall.conf << 'VHOSTEOF'
<VirtualHost *:80>
    ServerAdmin webmaster@planet-hosts.com
    ServerName catchall.invalid
    DocumentRoot /var/www/radiohosting/public/_catchall
    <Directory /var/www/radiohosting/public/_catchall>
        Options -Indexes
        AllowOverride None
        Require all granted
    </Directory>
    ErrorLog /var/log/apache2/catchall_error.log
    CustomLog /var/log/apache2/catchall_access.log combined
</VirtualHost>
VHOSTEOF
cd /etc/apache2/sites-enabled
sudo rm -f 000-catchall.conf
sudo ln -s ../sites-available/000-catchall.conf 000-catchall.conf
echo "  done"

echo "[3/3] Restart + verify Apache"
sudo systemctl restart apache2
sleep 1
sudo apache2ctl configtest 2>&1 | tail -1
systemctl is-active apache2
echo ""
echo "default vhost:"
sudo apache2ctl -S 2>/dev/null | grep "port 80 namevhost" | head -1
echo ""
echo "APACHE SETUP COMPLETE"
