# Planet Hosts - Installer Architecture

## Overview

The installer is a modular bash-based system designed for AlmaLinux 9 / RHEL 9 / RockyLinux 9.

## Layout

```
installer/          # Core installation scripts
  install.sh        # Main installer (step 1-12, calls license + setup)
  setup.sh          # Initial panel setup (run once)
  license-activate.sh  # License activation via HTTPS
  healthcheck.sh    # System health verification
  plugin-installer.sh  # Automatic plugin installer
  migration.sh      # Database migration runner
  update.sh         # Panel update tool

scripts/            # Modular service handlers
  apache.sh         # Apache install/configure
  mariadb.sh        # MariaDB install/configure
  php.sh            # PHP install/configure
  firewall.sh       # Firewall rules
  permissions.sh    # File permissions
  dns.sh            # BIND DNS
  ftp.sh            # FTP server
  ssl.sh            # SSL certificates
  email.sh          # Postfix/Dovecot

config/             # Default configurations
  nginx/            # Nginx templates (future)
  php/              # PHP ini templates
  mysql/            # MySQL config templates
  icecast/          # Icecast config templates
  firewall/         # Firewall rule templates
  panel/            # Panel app config

plugins/            # Plugin storage directory
storage/            # Runtime storage
logs/               # Installation logs
cache/              # Cache directory
backups/            # Backup storage
uploads/            # Upload storage
database/           # Schema files
docs/               # Documentation
```

## Installation Flow

1. install.sh runs steps 1-12
2. install.sh calls license-activate.sh
3. On success, install.sh calls setup.sh
4. setup.sh initializes the panel
5. Panel is ready

## Security

- No license generation in public code
- No private keys or signing certs in GitHub
- All licensing via HTTPS to MasterInstall server
- License stored at /etc/planethosts/license.json (chmod 600)
