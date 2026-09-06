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
> **Credentials are NOT stored in this file or this repo.** They live on the
> live server in `db_creds.sh` (mode 600) and in the app `.env` (gitignored).
> Fetch them at deploy time; never commit them.
- **IP:** 15.204.114.226
- **SSH User:** debian (see local `$env:SSH_ASKPASS` / keychain)
- **Panel URL:** http://15.204.114.226:2087/
- **Admin Login:** root (see `.env`/server vault)
- **DB (radiouser):** see `db_creds.sh` / `.env`
- **MySQL root:** see `db_creds.sh` / `.env`
- **phpMyAdmin:** http://15.204.114.226/phpmyadmin/
- **Webmail:** http://15.204.114.226:2096/
- **nginx:** http://15.204.114.226:8080/ (reverse proxy)

## Services
All enabled on boot: Apache (80,443,2082,2086,2087,2096,2100,2101), MariaDB, Postfix, Dovecot, Bind9, Icecast2 (8000), SHOUTcast DNAS (8000), nginx (8080), firewalld, Fail2Ban

## SHOUTcast Install
Binary at `K:\site_del\Masterinstall\shoutcast-server\shoucast-v2\sc_serv2_linux_x64-latest.tar.gz`
To reinstall: `sudo tar xzf sc_serv2_linux_x64-latest.tar.gz -C /usr/local/shoutcast && sudo systemctl restart shoutcast`

## Project Status

### Needs Work
- **Chat System (paused)**: widget embed still blank (DOM timing), moderation UI, message history limits, Cbox parity features (emoji/GIF picker, SSO, webhooks), room permissions system, product-based auto-join rooms, file uploads, voice/video

### Known Issues
- repair.planet-hosts.com DNS not resolving from some ISPs (glue present but some resolvers still cached)
- streaming_stations IDs changed from 9/10/11 to 12/13/14
