# Agent Instructions

## Auto-Deploy
After every commit/push, automatically deploy to the live server:
1. `git push` (already done)
2. SSH into `debian@15.204.114.226` with `$env:SSH_ASKPASS` and `git pull origin master`
3. Run PHP lint on changed files
4. Run any new DB migrations in `database/migrations/`
5. Run storage setup: `sudo bash scripts/setup_storage.sh`
6. Update `K:\site_del\Masterinstall` with `git pull origin master`

## Server Info
- **IP:** 15.204.114.226
- **SSH User:** debian / Skylinehosting171
- **Panel URL:** http://15.204.114.226:2087/
- **Admin Login:** root / vps-535ec74e-J2X9on8
- **DB (radiouser):** Skylinehosting171
- **MySQL root:** Skylinehosting171
- **phpMyAdmin:** http://15.204.114.226/phpmyadmin/ (root / Skylinehosting171)
- **Webmail:** http://15.204.114.226:2096/
- **nginx:** http://15.204.114.226:8080/ (reverse proxy)

## Services
All enabled on boot: Apache (80,443,2082,2086,2087,2096,2100,2101), MariaDB, Postfix, Dovecot, Bind9, Icecast2 (8000), SHOUTcast DNAS (8000), nginx (8080), firewalld, Fail2Ban

## SHOUTcast Install
Binary at `K:\site_del\Masterinstall\shoutcast-server\shoucast-v2\sc_serv2_linux_x64-latest.tar.gz`
To reinstall: `sudo tar xzf sc_serv2_linux_x64-latest.tar.gz -C /usr/local/shoutcast && sudo systemctl restart shoutcast`

## Project Status

### Done
- Multi-station DJ assignment (radio_dj_streams junction table)
- DJ list query fixed with LEFT JOIN for junction table
- Missing streaming_stations columns added (pid_file, last_stopped, last_started, backups, ssl_mode)
- radio_streams synced from streaming_stations
- dj_panel.php schedule fix (radio_dj_schedule table)
- Media upload: onclick fix, $_FILES key fix, position column removed
- upload_max_filesize/post_max_size increased to 128M
- Per-file progress bars for media upload
- Duplicate file detection on upload
- Music database directory auto-created during provisioning
- Global playlist system: admin CRUD (table, controller, views, routes)
- Global Music tab for users with download-to-station
- File Manager: tree view, file list, code editor, visual HTML editor (Code/Split/Design), double-click open, permissions editor
- Admin file manager: user selection, file browsing
- Subdomain system: DNS + Apache vhost + auto-directory creation
- FTP account creation with reserved name/duplicate/directory traversal checks
- ToDo list admin page
- DnsManager syncZoneToBind writes Bind9 zone files

### Needs Work
- Admin global playlists upload still has issues (empty $_FILES)
- AutoDJ wizard: media upload, engine detection, finish button
- DNS glue records for ns1/ns2.planet-hosts.com (subdomain resolution)
- Missing game DB tables: game_slot_pricing, game_packages (need migrations)
- GameServers controller uses `is_active`/`status` columns not in current tables
- repair.planet-hosts.com DNS glue records needed at registrar

### Known Issues
- admin/radio/global-playlists/upload/{id} receives empty $_FILES on admin port
- repair.planet-hosts.com DNS not resolving from some ISPs (needs glue records at registrar)
- streaming_stations IDs changed from 9/10/11 to 12/13/14
