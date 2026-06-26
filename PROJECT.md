# Planet Hosts Master Panel — Project Status

## Architecture

WHM/cPanel-style hosting panel with client area, admin dashboards, modular plugin system, and feature-aware menus. All features are gated via `feature_lists` table and package flags. The panel is served at three access points:

| Portal | URL | Port |
|--------|-----|------|
| Main site | `planet-hosts.com` | 80→443 |
| User portal | `planet-hosts.com:2082`→`:2083` | HTTP→HTTPS |
| Admin portal | `planet-hosts.com:2087` | HTTPS only |
| Reseller | `planet-hosts.com:2086`→`:2087` | HTTP→HTTPS |
| Webmail | `planet-hosts.com:2096`→`:2097` | HTTP→HTTPS |

## Port Mapping (cPanel-style)

| Port | Protocol | Use | Redirect |
|------|----------|-----|----------|
| 80 | HTTP | Main site | 301→443 |
| 443 | HTTPS | Main site (SSL) | — |
| 2082 | HTTP | User portal | 301→2083 |
| 2083 | HTTPS | User portal (SSL) | — |
| 2086 | HTTP | Reseller | 301→2087 |
| 2087 | HTTPS | Admin portal | — |
| 2096 | HTTP | Webmail | 301→2097 |
| 2097 | HTTPS | Webmail (SSL) | — |

## Key Features Built

### SnappyMail
- Replaced Roundcube with SnappyMail at `/snappymail/` and `/roundcube/` (aliased)
- SSO via internal API (`RainLoop\Api::Actions()->LoginProcess()`) — sets `smaccount` auth cookie, 302 redirects to `/snappymail/`
- Wildcard domain `_wildcard_.json` — any domain works without specific config
- `shortLogin: true` for Dovecot IMAP (local part only)
- `password_plain` saved in `mail_accounts`, system user created via `useradd`

### Radio System (`plugins/Radio/`)
- Full dashboard with 15+ tabs: Overview, AutoDJ, DJs, Mods, Schedule, Requests, Media, Playlists, Mounts, Bans, Widgets, Pages, Stats, Backups, Chat, Logs
- Multi-station dashboard with station switcher
- AutoDJ Setup Wizard (8-step)
- Icecast streaming via systemd service (`icecast@.service`) and config generator (`gen-icecast-config.php`)
- Icecast SSL: native TLS mode + reverse-proxy (Nginx/Apache) mode
- DDoS rules and Fail2Ban jails for Icecast
- Media Library: folders, drag-drop upload, progress bar, delete, scan, add to playlist
- Playlists: reference folders, scheduling (date ranges, months), auto-populate, duplicate/export M3U
- Widget generator: 6 types (Now Playing, Listener Count, DJ Status, Request Form, Schedule, Recently Played)
- Station homepage at `/home/planethosts/public_html/`
- Music stored in `/home/{username}/music/` (counts against account disk quota)

### File Manager (`user/Controllers/FileManagerController.php`)
- cPanel-style: folder tree, code editor, rename/copy/move, permissions editor, zip create/extract, search, drag-drop upload, right-click context menu, file properties

### FTP Manager (`user/Views/ftp.php`)
- cPanel-style: password strength meter, generate password, radio directories, disk quota, FTPS toggle, connection details (FTP/FTPS/SFTP)

### Security Center (`admin/Controllers/SecurityController.php`)
- Service detection: Fail2Ban, ModSecurity, OWASP CRS, RKHunter, ChkRootKit, Lynis, AIDE
- Security stats display, quick IP block

### Firewall Module (`admin/Controllers/FirewallController.php`)
- CSF-style: block/unblock IP, open/close ports, application presets (HTTP, HTTPS, SSH, FTP, Icecast, Games, cPanel, etc.), view logs, restart, iptables rule viewer

### DDoS Protection
- iptables rate limiting (ICMP, SYN flood, per-IP connlimit)
- Fail2Ban jails for SSH, radio-auth, Icecast
- Rules persisted via iptables-save

### Hostname Manager (`admin/Controllers/HostnameController.php`)
- Dedicated page at `/admin/hostname`
- Validate hostname (FQDN, DNS resolution, server IP match)
- Update OS hostname (`hostnamectl`, `/etc/hostname`, `/etc/hosts`)
- Dynamic Apache vhost generation (`planethosts-panel.conf`)
- AutoSSL via Certbot for hostname
- "Rebuild Hostname Configuration" button (vhost + SSL rebuild)
- Health check: hostname match, DNS, Apache, SSL, HTTP/HTTPS
- Dashboard status display with live AJAX health

### Universal SSL Manager (`admin/Controllers/UniversalSslController.php`)
- Full dashboard at `/admin/ssl/universal`
- 7 service profiles: Apache, Nginx, Icecast, FTP (FTPS), Postfix (SMTP), Dovecot (IMAP/POP3), Liquidsoap
- Auto-detection of installed services and listening ports
- Let's Encrypt automation: request, install, auto-renew
- Icecast SSL: native TLS + reverse-proxy mode
- SSL health check: cert existence, TLS handshake, hostname match, days left, service running
- Auto-repair button per service
- Activity log for all SSL operations
- Hourly cron endpoint (`/admin/ssl/cron`) for auto-renewal and repair

### Database tables created
- `ssl_certs` — certificate storage (domain, cert, key, expiry, auto-renew)
- `ssl_services` — service-to-cert mapping with config
- `ssl_log` — SSL operation audit log

### Hosting Portal Page
- Full marketing page at `planet-hosts.com` with rotating pricing cards
- 5 pricing categories: Web Hosting (10 plans), Web Reseller (10), Icecast (11), Icecast Reseller (10), Game Server (1)
- Testimonials with static grid + auto-scrolling carousel
- Floating live chat with panel form, status detection, message polling
- Visitor tracking (excludes admin pages)
- Dynamic host links (no hardcoded IPs)

## Packages & Billing

All packages rebuilt from scratch. 51 active packages across 10 categories (5 each) + testinglive:

| Category | Type | Count | Features |
|----------|------|-------|----------|
| Web Hosting | `web_hosting` | 6 | disk, bandwidth, email, ftp, databases, live_chat |
| Web Reseller | `web_reseller` | 5 | disk, bandwidth, email, ftp, databases, live_chat |
| SHOUTcast | `shoutcast` | 5 | listener_limit, bitrate, storage, dj, shoutcast_enabled |
| SHOUTcast Reseller | `shoutcast_reseller` | 5 | listener_limit, bitrate, storage, dj, shoutcast_enabled |
| Icecast | `icecast` | 5 | listener_limit, bitrate, storage, dj, icecast_enabled |
| Icecast Reseller | `icecast_reseller` | 5 | listener_limit, bitrate, storage, dj, icecast_enabled |
| Chat Room | `chat_room` | 5 | chatroom_enabled |
| Chat Room Voice | `chat_room_voice` | 5 | chatroom_enabled, chatroom_voice_enabled |
| Game Server | `game_server` | 5 | game_enabled |
| DJ Panel | `dj_panel` | 5 | listener_limit, bitrate, dj, dj_panel_enabled |

testinglive has ALL features enabled (shoutcast, icecast, chat room, voice, games, dj panel, live chat).

Billing products (51) auto-created from packages in `billing_products` table with `package_id` FK linking back.

## Streaming System

### Streaming Engine (`plugins/Radio/Services/StreamingEngine.php`)
- Engine-agnostic API: same interface for SHOUTcast and Icecast
- `StreamingDriverInterface` — contract for all drivers
- Station CRUD via `streaming_stations` table

### SHOUTcast v2
- Binary: `/opt/planethosts/shoutcast/sc_serv` (DNAS 2.6.1 Build 777)
- Installed once, shared by all stations
- Per-user station dirs: `/home/{username}/stations/{port}/`
- systemd services: `ph-stream-{port}.service`
- Port range: 9000–10000

### SHOUTcast v1
- Binary: `/opt/planethosts/shoutcast1/sc_serv` (v1.9.8, 2004)
- Separate install from v2
- Per-user station dirs: `/home/{username}/v1stations/{port}/`
- systemd services: `ph-v1-stream-{port}.service`
- Port range: 11000–12000
- Auto-registers when binary is present

### Icecast
- System service: `icecast2`
- Managed by `IcecastDriver`
- Port range: 8000–9000

### Streaming API
- `GET /admin/streaming` — dashboard
- `GET/POST /admin/api/streaming/*` — full admin API
- `GET/POST /api/v1/*` — public API (engine-independent, API key auth)

## Hosting Portal Page

Full marketing portal at `https://planet-hosts.com/` restored from backup with:
- Hero section with stats
- Features grid (8 feature cards)
- Rotating pricing cards from billing_products (10 categories, auto-slide every 7s)
- Why Choose section
- Testimonials with auto-scroll
- Floating live chat panel
- Footer with links

## Login Pages

| Portal | URL | Type |
|--------|-----|------|
| User login | `https://planet-hosts.com:2083/` | Clean login form (email or username) |
| Admin login | `https://planet-hosts.com:2087/` | Full admin panel login |

## Fixes Applied

- **License system**: Removed online CURL to nonexistent endpoint (was 10s hang). Now uses local RSA verification only. keygen.sh and license_private.pem moved to MasterInstall (out of public repo).
- **Dashboard**: Fixed 0-byte rendering (license check hang). Added cron service detection (Debian uses `cron`, RHEL uses `crond`). Added streaming engines widget. Fixed SSL status display (cert dir permissions were 700).
- **File manager**: Added 15 missing routes (list, read, save, create, rename, copy, move, extract, chmod, properties, search).
- **public/index.php**: Was deleted during git clean (0 bytes). Restored from backup. Removed license check for dev mode.
- **AuthController, DashboardController**: Were deleted during git clean. Restored from backup.
- **Firewall ports**: 2083 and 2097 were missing from public zone (added to docker zone by mistake). Fixed.
- **SSL certs**: `/etc/letsencrypt/live/` had 700 permissions blocking Apache. Changed to 755.
- **Hostname DB**: Stored as `server1.planet-hosts.com` but system was `server.planet-hosts.com`. Fixed.
- **`provision.sh`**: Created for account creation (creates system user, public_html, default index).
- **`vsftpd.conf`**: Fixed to enable write, chroot users to home dir.
- **Package categories**: Cleaned duplicates, added missing categories (shoutcast, icecast_reseller, etc.).
- **Portal pricing**: Replaced hardcoded packages with dynamic billing_products.

## Recent Changes

- **2026-06-25**: Port mapping fixed to cPanel-style (2082→2083, 2096→2097, 2087 SSL-only)
- **2026-06-25**: Hardcoded IP `45.61.59.55` replaced with `planet-hosts.com` across 24 files
- **2026-06-25**: HTTP→HTTPS 301 redirects for all ports
- **2026-06-25**: Hostname Manager built — validate, OS update, vhost, AutoSSL, rebuild
- **2026-06-25**: Universal SSL Manager built — 7 service profiles, auto-detect, Icecast SSL, health
- **2026-06-25**: Radio "User" display name bug fixed (better fallback chain)
- **2026-06-25**: Live chat badge image path fixed
- **2026-06-25**: Installer refactored to modular structure at `K:\site_del\install.sh-update.sh\`
- **2026-06-26**: SHOUTcast v1 (1.9.8) installed. Streaming Engine with 3 drivers.
- **2026-06-26**: Packages rebuilt: 51 active across 10 categories. Billing products auto-created.
- **2026-06-26**: Portal restored from backup with dynamic billing products pricing.
- **2026-06-26**: All admin panel pages fixed (license check removed, 0-byte rendering fixed).
- **2026-06-26**: File manager routes fixed (15 missing routes added).
- **2026-06-26**: SSL cert permissions fixed, hostname DB updated, provision.sh created.

## Installer

The installer has been refactored from a single `install.sh` into a modular system:

```
install.sh                          # Entry point
installer/
  install.sh                        # Main installer (12 steps + license + setup)
  setup.sh                          # One-time init (admin, roles, caches)
  license-activate.sh               # HTTPS POST → licensing server
  healthcheck.sh                    # PASS/WARNING/FAIL on all services
  plugin-installer.sh               # Auto-scans plugins/
  migration.sh                      # DB migration runner
  update.sh                         # Update workflow
scripts/
  apache.sh, mariadb.sh, php.sh     # Service modules
  firewall.sh, permissions.sh       # System config
  dns.sh, ftp.sh, ssl.sh, email.sh  # Optional modules
config/panel/app.php                # Default config
```

Key design: **No license generation in public code.** All licensing via HTTPS to MasterInstall server. License stored at `/etc/planethosts/license.json`.

## Server Optimization
- 2GB swap file
- Apache MaxRequestWorkers capped at 30
- Heavy services removed (ClamAV, Rspamd)
- php-mysql driver reinstalled (was autoremoved)

## Login Restrictions
- Admin: root, kane, planethosts
- User portal: standard authentication
- Restored to normal (whitelist removed)

## Git
- Auto-push configured with `credential.helper store`
- Pushes to `origin/master`
- Repo: `https://github.com/j68418730/whm`
