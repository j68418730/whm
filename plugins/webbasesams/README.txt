# Planet Hosts Radio — Database & Server Info
# Server: root@45.61.59.55  (LEGACY - credentials rotated, see db_creds.sh on live server)
# Panel: https://planet-hosts.com:2083
# SHOUTcast: port 9000 (unified), 9003 (internal)
# AutoDJ connects to port 9003
#
# Credentials are NOT stored in this repo. On the live server they live in:
#   /usr/local/planet-hosts/db_creds.sh  (root:root, mode 600)
# or in the app .env (DB_USERNAME / DB_PASSWORD).

# To restore:
# gunzip -c radiohosting_dump.sql.gz | mysql -u "$DB_USER" -p"$DB_PASS" radiohosting

# To dump:
# mysqldump -u "$DB_USER" -p"$DB_PASS" radiohosting | gzip > radiohosting_dump.sql.gz