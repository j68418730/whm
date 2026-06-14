# Spectre WHM Product Architecture

Spectre WHM is the main product. Its core should focus on WHM-style root administration:

- server setup and service status
- account creation, suspension, termination, quotas, and ownership
- packages, feature lists, and reseller controls
- DNS, web server, PHP, MySQL/MariaDB, FTP, email, SSL, backups, security, monitoring, and API

Streaming and billing are add-ons:

- `plugins/Radio`: SHOUTcast, Icecast, AutoDJ, DJ users, playlists, transcoding, stream analytics
- `plugins/Billing`: products, invoices, subscriptions, taxes, coupons, payment gateways, usage billing

## Rules

- WHM core routes live in `routes/core.php`.
- Add-on routes live in each plugin's `routes.php`.
- Add-ons expose dashboard metadata through their plugin class.
- The WHM dashboard may link to enabled add-ons, but add-on business logic must stay inside the plugin.
- Radio and billing can be disabled from `config/plugins.php` without breaking the WHM core.

## Build Order

1. Harden the WHM core: auth, roles, audit logs, command queue, server adapters.
2. Build account/package/reseller provisioning.
3. Add DNS, web server, database, email, SSL, backup, and monitoring adapters.
4. Expand the Radio add-on after the WHM foundation is stable.
5. Expand the Billing add-on after products/packages and account lifecycle events exist.
