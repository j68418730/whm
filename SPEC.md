# Planet Hosts Master Panel - Installer Refactor Specification

## Overview

Refactor the current installer into a modular installation system similar to commercial hosting panels such as cPanel, Plesk, DirectAdmin, and CyberPanel.

The installer must no longer generate licenses locally. Licensing must be completely separated from the public source code.

---

# Current Installer

Use the existing install.sh as the foundation.

Do NOT rewrite it from scratch.

Refactor it into modules while preserving all current installation functionality.

The installer already performs:

* Repository setup
* System updates
* Firewall installation
* Apache installation
* MariaDB installation
* PHP installation
* FFmpeg installation
* Icecast installation
* Liquidsoap installation
* phpMyAdmin installation
* Panel deployment
* Apache Virtual Host creation
* Database creation
* Schema import
* Cron creation

These features must remain.

---

# Remove

Completely remove:

keygen.sh

license generation

RSA signing

private keys

local license creation

Do not generate any licenses locally.

---

# New Installation Flow

Customer downloads install.sh

↓

Install repositories

↓

Update operating system

↓

Install packages

↓

Configure firewall

↓

Install Apache

↓

Install MariaDB

↓

Install PHP

↓

Install media software

↓

Copy panel files

↓

Configure Apache

↓

Create database

↓

Import database schema

↓

Request license activation

↓

Receive signed license

↓

Run setup.sh

↓

Panel ready

---

# New Folder Structure

installer/

```
install.sh

setup.sh

healthcheck.sh

license-activate.sh

plugin-installer.sh

migration.sh

update.sh
```

scripts/

```
apache.sh

mariadb.sh

php.sh

firewall.sh

permissions.sh

dns.sh

ftp.sh

ssl.sh

email.sh
```

config/

plugins/

storage/

logs/

cache/

backups/

uploads/

---

# Install.sh Responsibilities

install.sh should ONLY:

Install repositories

Update OS

Install packages

Configure firewall

Install services

Deploy panel

Create Virtual Host

Create database

Import schema

Create .env

Create cron jobs

Call:

license-activate.sh

If successful

Call:

setup.sh

Nothing more.

---

# license-activate.sh

This replaces keygen.sh.

Collect:

Server UUID

Hostname

Public IP

Operating System

Distribution

Kernel Version

CPU Model

CPU Cores

RAM

Disk Size

Panel Version

Prompt for:

License Key

Customer Email (optional)

Send HTTPS POST request to:

Planet Hosts Licensing Server

Payload:

License Key

Server UUID

Hostname

IP

OS

Panel Version

Wait for response.

If successful:

Download signed license

Store in:

/etc/planethosts/license.json

Store:

Activation Date

Expiration Date

Enabled Modules

Update Channel

Activation Token

Return success.

If failed:

Display reason

Exit installer

Do NOT continue.

---

# setup.sh

Runs only once.

Check for:

/var/www/radiohosting/.installed

If found

Exit.

Otherwise continue.

---

# setup.sh Tasks

Verify license

Verify Apache

Verify MariaDB

Verify PHP

Verify FFmpeg

Verify Icecast

Verify Liquidsoap

Verify firewall

Verify cron

Verify storage permissions

Create folders

storage/

logs/

cache/

uploads/

backups/

temp/

Set permissions.

Generate application keys.

Generate security salts.

Create default administrator account if none exists.

Create:

Owner Role

Administrator Role

Reseller Role

Client Role

Support Role

Import:

Default Settings

Default DNS Templates

Default Email Templates

Default Firewall Templates

Default Hosting Packages

Default Dashboard Widgets

Register:

Menus

Permissions

Routes

Widgets

Build caches.

Run plugin installer.

Run health check.

Create:

.installed

Finish.

---

# plugin-installer.sh

Automatically scan:

plugins/

Each plugin contains:

plugin.json

install.php

database/schema.sql

permissions.json

widgets.json

routes.php

Automatically:

Read plugin.json

Check dependencies

Import SQL

Execute install.php

Register permissions

Register widgets

Register menus

Enable plugin

Log installation

No hardcoded plugin logic.

---

# Healthcheck

Create:

healthcheck.sh

Verify:

Apache

MariaDB

PHP

Redis

FFmpeg

Icecast

Liquidsoap

Firewall

Cron

Disk

RAM

CPU

Internet

DNS

SSL

Mail

FTP

Storage

Permissions

License

Output:

PASS

WARNING

FAIL

Log everything.

---

# Installer Logging

Create:

/var/log/planethosts/

Every action logs:

Timestamp

Module

Action

Duration

Result

Errors

Installer Version

---

# Rollback

If installation fails:

Rollback partially completed work where safe.

Examples:

Remove incomplete database

Remove incomplete Virtual Host

Disable incomplete services

Restore previous configuration if updating

Write rollback log

---

# Updates

Create:

update.sh

Workflow:

Verify license

Check for updates

Backup current installation

Download update

Install update

Run migrations

Update plugins

Clear cache

Rebuild cache

Run health check

Restart services

Finish

---

# Public Repository Rules

GitHub MUST contain:

Installer

Panel

Themes

Plugins

Assets

Database Schema

Documentation

GitHub MUST NEVER contain:

keygen.sh

Private Keys

RSA Signing Keys

License Generator

License Database

Customer Database

License Server

Update Server Secrets

Activation Secrets

Signing Certificates

---

# Private MasterInstall

MasterInstall is NOT stored on GitHub.

MasterInstall contains:

License Generator

License Signer

RSA Keys

Activation API

Customer License Database

Billing Integration

Download Server

Update Server

License Revocation

License Renewal

Upgrade/Downgrade Logic

Server Registration

Machine Fingerprinting

License Validation

Signed License Creation

Only MasterInstall may create or sign licenses.

---

# Security

All communication with the licensing server must use HTTPS.

Never store private keys on customer servers.

Never expose signing certificates.

Never expose licensing database credentials.

Never allow offline license generation.

Verify license signatures before accepting them.

Cache the last successful validation to tolerate temporary network outages.

---

# Final Goal

The completed installer should behave like a commercial hosting control panel installer.

The public installer installs and configures the software.

The private MasterInstall platform handles all licensing.

After a successful license activation, setup.sh completes the application initialization, installs plugins, builds caches, performs health checks, and marks the installation as complete.

The codebase should remain modular, maintainable, secure, and ready for future expansion without requiring major changes to the installer.

---

# Automatic DNS, Hostname, Nameserver, and SSL Provisioning

Implement a fully automated server provisioning system similar to cPanel/WHM.

## Server Identity

During installation automatically detect:

- Public IPv4
- Public IPv6 (if available)
- Private IP
- Hostname
- FQDN (Fully Qualified Domain Name)
- Operating System
- Distribution
- Kernel Version
- Timezone
- CPU
- RAM
- Disk Space

If no hostname is configured, prompt the administrator to enter one (e.g., server1.example.com) and configure it using operating system tools.

## Hostname Configuration

Automatically:

- Set the system hostname
- Update /etc/hostname
- Update /etc/hosts
- Verify the hostname resolves correctly
- Save the hostname in panel configuration

## DNS Manager

Create a DNS Manager capable of automatically managing zones.

Support:

- A Records
- AAAA Records
- CNAME
- MX
- TXT
- SPF
- DKIM
- DMARC
- NS Records
- SRV Records
- CAA Records
- PTR (where supported)

Allow administrators to create DNS templates automatically applied to newly added domains.

## Automatic Nameserver Configuration

During installation, allow the administrator to configure:

- Primary Nameserver (e.g., ns1.example.com)
- Secondary Nameserver (e.g., ns2.example.com)

Automatically:

- Create required NS records
- Create A/AAAA records for nameservers
- Configure the DNS service
- Save settings as defaults

Every new hosted domain should automatically receive these nameservers unless overridden.

## Automatic Domain Provisioning

When a new domain is added, automatically:

- Create the website root
- Create the VirtualHost
- Create the DNS zone
- Apply DNS templates
- Configure PHP
- Configure logging
- Create SSL request
- Reload web server

No manual configuration required.

## Automatic Let's Encrypt Support

Install Certbot and all required dependencies automatically.

Support both Apache and Nginx.

Automatically issue certificates for:

- Hostname
- Primary Domain
- Parked Domains
- Addon Domains
- Subdomains
- Wildcard Domains (when DNS validation is available)

Automatically:

- Install certificates
- Configure HTTPS
- Redirect HTTP to HTTPS (optional setting)
- Enable HTTP/2 and HTTP/3 where supported
- Enable OCSP stapling where supported

## Automatic SSL Renewal

Create automatic renewal using Certbot.

- Renew certificates before expiration
- After renewal: reload web server, verify certificate, log renewal, notify admin on failure
- No manual renewal ever required

## AutoSSL for Every Domain

Implement an AutoSSL service. Whenever a domain is (added, modified, restored, migrated), automatically:

- Verify DNS resolution
- Verify domain points to this server
- Request Let's Encrypt certificate
- Install certificate
- Configure web server
- Enable HTTPS
- Log the operation

Administrators should also be able to trigger AutoSSL manually from the panel.

## Background SSL Monitor

Create a scheduled service that every hour:

- Scans all hosted domains
- Detects domains without SSL
- Detects expiring certificates
- Detects failed renewals
- Automatically attempts to repair or reissue certificates
- Generates alerts only when automatic repair fails

## DNS Validation

Before requesting SSL, automatically verify:

- A record resolves correctly
- AAAA record (if used) resolves correctly
- Hostname resolves
- Domain points to correct server

If validation fails: explain the issue, retry later automatically.

## Reverse Proxy Support

Support SSL provisioning for:

- Apache
- Nginx
- Apache behind Nginx
- Reverse proxy configurations

## Multi-Domain Support

Support unlimited:

- Primary domains
- Addon domains
- Parked domains
- Alias domains
- Subdomains

Each domain automatically receives its own SSL certificate unless the administrator selects a wildcard certificate.

## Logging

Log every DNS and SSL operation: timestamp, domain, action, success/failure, certificate expiration, renewal date, error details.

## Admin Dashboard

Add an SSL & DNS dashboard showing:

- Hostname
- Server IP
- Nameservers
- DNS status
- SSL status
- Certificate issuer
- Expiration dates
- AutoSSL status
- Domains requiring attention
- Renewal history

## SSL Provider Abstraction

Instead of hardcoding Let's Encrypt, build an SSL Provider abstraction. Support multiple certificate authorities:

- Let's Encrypt
- ZeroSSL
- Commercial certificates
- Internal CA

The AutoSSL service uses whichever provider is configured as the default. This allows switching providers without rewriting the panel.

## Zero-Touch Goal

A true "zero-touch" experience: adding a domain automatically configures DNS, virtual hosts, HTTPS, and certificate renewal with minimal administrator intervention.
