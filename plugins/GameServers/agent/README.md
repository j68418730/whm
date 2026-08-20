# Planet Hosts Game Node Agent

Runs on a **remote destination** (another server or a local computer) and lets the
Planet Hosts panel control game servers there — without opening any inbound ports.

The agent **connects OUT to the panel over HTTPS** (port 443) and polls for jobs.
This works behind CGNAT / restrictive ISPs where inbound port-forwarding is not
possible.

## How it works
1. The panel queues a job for a remote node (`node_jobs` table).
2. The agent polls `GET /panel/api/agent/commands?token=…` every 10s and picks up the job.
3. The agent runs the game command locally (install/start/stop/restart/log/status/delete).
4. The agent reports back via `POST /panel/api/agent/result`.

## Install
1. Copy this folder to the remote machine (e.g. `/opt/ph-agent` on Linux, `C:\ph-agent` on Windows).
2. Copy `agent-config.example.json` → `agent-config.json` and edit:
   - `panel_url` — the main panel (e.g. `https://planet-hosts.com`)
   - `node_token` — create the node in **Admin → Games → Nodes** and copy its token here
   - `base_dir` — where game servers are installed on this machine
   - `steam_user` / `steam_pass` — Steam account for installing games (or `anonymous`)
3. Requires Node.js 18+ (has built-in `fetch`).

## Run as a service
**Linux:**
```
sudo cp -r agent /opt/ph-agent
sudo cp /opt/ph-agent/ph-agent.service /etc/systemd/system/
sudo systemctl daemon-reload
sudo systemctl enable --now ph-agent
systemctl status ph-agent
```

**Windows:** double-click `ph-agent.bat` (or register with Task Scheduler to run at boot).

**macOS:** `nohup node agent.js &` (or a launchd plist).

## Commands the agent accepts (whitelist only — no arbitrary shell)
`install`, `start`, `stop`, `restart`, `status`, `log`, `delete`
