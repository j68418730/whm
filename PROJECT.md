# Planet Hosts WHM

## Overview
Web Hosting Manager panel with integrated billing, streaming engine management, package management, and a fully widget-based modular dashboard.

## Architecture
- **PHP** — custom MVC framework (`core/`), plugins in `plugins/`, admin in `admin/`, user in `user/`
- **Database** — MariaDB via `core/Database.php` (PDO wrapper, no ORM)
- **Views** — PHP templates wrapped in theme layouts (`theme/admin_layout.php`, `theme/user_layout.php`)
- **Routing** — `routes/core.php` + plugin route files, loaded via `core/Router.php`
- **Widgets** — file-based auto-discovery from `widgets/` folder, managed by `core/WidgetManager.php`

## Deployment
- Server: `root@45.61.59.55` (Apache/2.4.67, MariaDB, PHP 8.4)
- Path: `/var/www/radiohosting/`
- Local: `K:\site_del\Masterinstall\` (project root — primary workspace)

### SSH Workflow (Windows)
Use `SSH_ASKPASS` to authenticate without interactive prompt (password in `K:\site_del\donotupload\sshpass.bat`):
```powershell
$env:SSH_ASKPASS = "K:\site_del\donotupload\sshpass.bat"
$env:SSH_ASKPASS_REQUIRE = "force"

# Run remote commands
ssh -o StrictHostKeyChecking=no -o BatchMode=no root@45.61.59.55 'command'

# Copy files
scp -o StrictHostKeyChecking=no -o BatchMode=no local/file.php root@45.61.59.55:/var/www/radiohosting/dst/file.php
```

### Deploy Workflow
1. Backup server files to local `K:\site_del\backups\` (never on server)
2. Deploy changed files via SCP
3. Run `php -l` syntax check on server
4. Run database migration (`database/fix_*.sql`)
5. Verify, then commit to git

### Backup Workflow
- **Local backup dir**: `K:\site_del\backups\` (NOT on server — server space is limited)
- **Server files**: tar.gz via SSH, download to local with SCP, then `rm -rf` on server
- **Workspace backup**: copy entire `K:\site_del\Masterinstall\` to `K:\site_del\backups\` before major changes

## Critical Development Rules

### 1. No Duplication
Before creating any new page, widget, module, API, database table, or feature, search the existing codebase first. If an equivalent implementation already exists, extend and improve it rather than creating a duplicate. Only create new components when no suitable implementation exists.

### 2. File Updates
Always update `Project.md` and `README.md` before the end of each session with the current state, decisions made, and next steps.

### 3. Existing Widget Preservation
- Do NOT recreate existing dashboard widgets
- Do NOT replace existing widgets
- Do NOT duplicate existing functionality
- If a widget exists: improve, extend, modernize the UI, add missing functionality, fix bugs
- Keep existing DB compatibility and API compatibility
- Only create a new widget when no existing widget performs that function

### 4. Plugin Widget Priority
Core widgets always have higher priority than plugin widgets. If a plugin provides a widget with the same name as a core widget, prompt the administrator to Replace, Rename, or Cancel.

---

## Dashboard Widget System

### Architecture
Every widget is a single PHP file in `widgets/` returning a config array with `key`, `name`, `description`, `icon`, `defaultZone`, `defaultSort`, `height`, and `render` callback.

Widgets are auto-discovered via `WidgetManager::loadFromFolder()` during application boot.

### Existing Widgets (11 total)
| File | Key | Category | Data Source |
|---|---|---|---|
| `stats_bar.php` | `stats_bar` | Hosting | DashboardController (accounts/tickets/revenue) |
| `server_health.php` | `server_health` | System | Direct shell commands + auto-refresh |
| `services.php` | `services` | System | systemctl is-active |
| `streaming_engines.php` | `streaming_engines` | Streaming | File existence + systemctl + pgrep |
| `hostname_status.php` | `hostname_status` | System | DashboardController + /admin/hostname/health API |
| `quick_actions.php` | `quick_actions` | Navigation | Static links |
| `recent_activity.php` | `recent_activity` | Activity | DashboardController (orders/accounts/tickets) |
| `recent_logins.php` | `recent_logins` | Activity | login_attempts table |
| `revenue.php` | `revenue` | Billing | payments + invoices tables |
| `server_stats.php` | `server_stats` | System | Legacy — preserved for existing users |
| `service_status.php` | `service_status` | System | Legacy — preserved for existing users |

### Core Infrastructure
- `core/Widget.php` — Widget value object
- `core/WidgetManager.php` — Singleton: register, loadFromFolder, getUserWidgets, ensureDefaults, saveLayout, renderZone, addWidget, removeWidget, setData/getData
- `admin/Controllers/WidgetController.php` — Add/remove/save-layout endpoints
- `admin/Controllers/DashboardController.php` — Gathers data, shares via WidgetManager::setData()
- `admin/Views/dashboard/index.php` — Main + side zones with HTML5 drag-and-drop

### Database
`user_widgets` table: id, user_id, widget_key, zone, sort_order, settings (JSON), created_at, updated_at

### Planned Improvements
- Widget Builder (custom widget creation UI with 20+ types)
- Layout management (save/load/reset/export/import/share)
- Role/permission-based widget visibility
- Pin, hide, collapse, resize UI
- Multi-column grid (beyond main/side)
- Widget SDK/manifest standard for plugins

---

## Streaming Engine

### Supported Engines
- **SHOUTcast v2** — `/opt/planethosts/shoutcast/sc_serv`, systemd `ph-stream-{port}`
- **SHOUTcast v1** — `/opt/planethosts/shoutcast1/sc_serv`, systemd `ph-v1-stream-{port}`
- **Icecast** — systemd `icecast2` (or `icecast`)

### Stream Storage
Two tables with overlapping data:
| Table | Used By | Password |
|---|---|---|
| `radio_streams` | Admin (StreamsController) | plain_password + password_hash |
| `streaming_stations` | Admin wizard (legacy) | plain_password + password_hash |

### Admin Stream Management
- 4-step create wizard: Customer → Stream → Network → Review
- Edit: full settings (engine, port, mount, bitrate, format, password, user, toggles)
- Actions: start, stop, restart, start-all, stop-all, autodj-start-all, autodj-stop-all, clone, delete, suspend, unsuspend
- Port allocation via `core/PortManager.php` (range-based, socket-checked)

---

## Package System
Packages are stored in `hosting_packages` table. Types: shoutcast, icecast, shoutcast_reseller, icecast_reseller, dj_panel, hosting, reseller.

Packages are the source of truth for pricing, features, limits, and billing.

## Feature Lists
`feature_lists` table defines feature sets applied to accounts. Columns organized in groups:

**Resource Limits**: email_accounts, ftp_accounts, databases, database_users, subdomains, parked_domains, addon_domains

**General Features**: cron_jobs, ssh_access, ssl_allowed, git_access, nodejs, python, ruby, terminal, backups

**Website Builder**: builder, ai_website_builder, ai_assistant, installer

**Developer Tools**: plugin_marketplace, api_access, webhooks

**Chat**: chatbox, chatbox_voice, chatbox_video, dj_panel

**Streaming** (collapsible section, master toggle: streaming_enabled):
- Engines: shoutcast_v1, shoutcast_v2, icecast_enabled
- Limits: max_stations, max_djs, max_listeners, max_bitrate, playlist_storage
- Toggles: autodj, ssl_streaming, statistics, recording, song_requests

**Game Servers** (collapsible section, master toggle: game_servers_enabled):
- Toggles: steamcmd, workshop, mod_support, scheduled_restarts, automatic_updates, game_backups
- Limits: max_game_servers

**VPS** (collapsible section, master toggle: vps_enabled):
- Limits: vcpu, ram, vps_storage, vps_bandwidth, snapshots, vps_backups, ipv4, ipv6
- Toggles: iso_mount

Manual Custom on account create form shows all feature checkboxes. Legacy `radio`, `shoutcast`, `game` columns preserved (hidden) for backward compatibility.

---

## Key Decisions
- **No Docker** for this project (Docker is only for clonets project)
- **License**: local RSA verification only — no online CURL to nonexistent endpoints
- **Masterinstall**: `K:\site_del\Masterinstall\` is the deployment source
- **Streaming abstraction**: communicate only through drivers (Icecast, SHOUTcast v2, SHOUTcast v1), never directly with binaries
