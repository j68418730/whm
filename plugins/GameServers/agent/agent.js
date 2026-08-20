#!/usr/bin/env node
/**
 * Planet Hosts Game Node Agent (poll mode) — cross-platform (Windows / Linux / macOS)
 * Runs on a remote destination. Connects OUT to the panel over HTTPS (no inbound
 * ports / no ISP port-forwarding needed), polls for jobs, executes game-server
 * commands locally, and posts results back.
 *
 * Usage:  node agent.js                  (reads agent-config.json beside it)
 * Windows exe:  ph-agent.exe             (same, reads agent-config.json beside it)
 */
'use strict';

const fs = require('fs');
const path = require('path');
const os = require('os');
const { execSync, spawn, spawnSync } = require('child_process');

const IS_WIN = process.platform === 'win32';
// Real folder the agent lives in. `node agent.js` runs from source so __dirname
// is correct; a pkg-built exe snapshots __dirname, so use the exe's folder instead.
const APP_DIR = process.pkg ? path.dirname(process.execPath) : __dirname;
const CONFIG_PATH = path.join(APP_DIR, 'agent-config.json');
const STATUS_PATH = path.join(APP_DIR, 'agent-status.json');
const MAP_PATH = path.join(APP_DIR, 'install_map.json');
const PID_PATH = path.join(APP_DIR, 'agent.pid');

function loadConfig() {
  if (!fs.existsSync(CONFIG_PATH)) {
    console.error('Missing ' + CONFIG_PATH);
    process.exit(1);
  }
  const c = JSON.parse(fs.readFileSync(CONFIG_PATH, 'utf8'));
  // Multiple install locations (drives). Config may use the legacy single
  // `base_dir`, a `locations` array, or a list of {path} objects.
  if (!Array.isArray(c.locations)) c.locations = [];
  c.locations = c.locations
    .map(l => (typeof l === 'string' ? l : (l && l.path) || ''))
    .filter(s => s && s.trim().length)
    .map(p => path.resolve(String(p)));
  // De-duplicate preserving order
  c.locations = c.locations.filter((p, i) => c.locations.indexOf(p) === i);
  if (c.locations.length === 0) {
    c.locations = [path.resolve(c.base_dir || (IS_WIN ? 'C:\\PlanetHostsGames' : '/var/gameservers'))];
  }
  c.base_dir = c.locations[0];
  c.poll_interval_ms = c.poll_interval_ms || 10000;
  return c;
}

const CFG = loadConfig();
const BASE = CFG.base_dir;

function slugify(name) { return String(name || 'server').toLowerCase().replace(/[^a-z0-9_-]/g, ''); }

function loadMap() { try { return JSON.parse(fs.readFileSync(MAP_PATH, 'utf8')); } catch (e) { return {}; } }
function saveMap(m) { try { fs.writeFileSync(MAP_PATH, JSON.stringify(m)); } catch (e) {} }

// Resolve which drive/root a server lives on. Panel may hint via payload.location;
// otherwise fall back to the recorded root for that slug, then the default root.
function rootFor(payload, server) {
  const slug = slugify(payload.slug || (server && server.server_name) || 'server');
  const map = loadMap();
  if (map[slug] && CFG.locations.indexOf(map[slug]) !== -1) return map[slug];
  const want = payload.location ? path.resolve(String(payload.location)) : '';
  if (want && CFG.locations.indexOf(want) !== -1) return want;
  return CFG.base_dir;
}

function dirFor(payload, server) {
  return path.join(rootFor(payload, server), slugify(payload.slug || (server && server.server_name) || 'server'));
}

// Remember which root a slug was installed on so start/stop/status find it
// even if a second drive is added later.
function rememberLocation(dir, payload, server) {
  const slug = slugify(payload.slug || (server && server.server_name) || 'server');
  const root = CFG.locations.find(p => path.resolve(p) === path.dirname(path.resolve(dir))) || CFG.base_dir;
  const map = loadMap();
  if (map[slug] !== root) { map[slug] = root; saveMap(map); }
}

function readFileSafe(p) { try { return fs.readFileSync(p, 'utf8'); } catch (e) { return ''; } }

// Single-instance guard: prevents double polling when the same agent is wired up
// as both a scheduled task and a Windows service (or started manually twice).
(function guard() {
  try {
    if (fs.existsSync(PID_PATH)) {
      const pid = parseInt(fs.readFileSync(PID_PATH, 'utf8'), 10);
      if (pid) {
        try { process.kill(pid, 0); console.error('Another agent instance is running (pid ' + pid + '). Exiting.'); process.exit(0); }
        catch (e) {}
      }
    }
    fs.writeFileSync(PID_PATH, String(process.pid));
    process.on('exit', function () { try { fs.unlinkSync(PID_PATH); } catch (e) {} });
  } catch (e) {}
})();

// A tiny status file the Windows tray manager reads to show live status offline.
function writeStatus() {
  try {
    fs.writeFileSync(STATUS_PATH, JSON.stringify({
      online: true, pid: process.pid, panel_url: CFG.panel_url,
      last_seen: new Date().toISOString(), locations: CFG.locations,
    }));
  } catch (e) {}
}

// Best-effort free/total disk space per location (Windows: wmic, POSIX: df).
function diskFree(p) {
  try {
    if (IS_WIN) {
      const drive = String(p).substr(0, 2);
      const out = execSync('wmic logicaldisk where "DeviceID=\'' + drive + '\'" get FreeSpace,Size /value 2>nul').toString();
      let free = null, total = null;
      out.split(/\r?\n/).forEach(line => {
        const mFree = /FreeSpace=(\d+)/.exec(line); if (mFree) free = parseInt(mFree[1], 10);
        const mSize = /Size=(\d+)/.exec(line); if (mSize) total = parseInt(mSize[1], 10);
      });
      return { free: free, total: total };
    }
    const out = execSync('df -k ' + JSON.stringify(p) + ' 2>/dev/null').toString().trim().split(/\r?\n/);
    const parts = out[out.length - 1].split(/\s+/);
    return { free: parseInt(parts[3], 10) * 1024, total: parseInt(parts[1], 10) * 1024 };
  } catch (e) { return { free: null, total: null }; }
}

// Report node environment + disk state to the panel (throttled to every 3rd poll).
let envCounter = 0;
async function reportEnv() {
  envCounter++;
  if (envCounter % 3 !== 0) return;
  const locs = [];
  for (const p of CFG.locations) {
    const d = diskFree(p);
    fs.mkdirSync(p, { recursive: true });
    locs.push({ path: p, free: d.free, total: d.total });
  }
  const body = new URLSearchParams();
  body.append('token', CFG.node_token);
  body.append('locations', JSON.stringify(locs));
  body.append('os', os.platform());
  body.append('arch', os.arch());
  body.append('version', '2.1.0');
  try {
    await fetch(CFG.panel_url + '/api/agent/env', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: body.toString(),
    });
  } catch (e) {}
}

function writeStartScript(dir) {
  if (IS_WIN) {
    const f = path.join(dir, 'start.bat');
    if (fs.existsSync(f)) return;
    fs.writeFileSync(f, '@echo off\r\ncd /d "' + dir + '"\r\n'
      + 'if exist server.exe (server.exe) else (if exist srcds_run.exe (srcds_run.exe) else (echo No game binary found. Edit start.bat ^& ping -n 99999 127.0.0.1 >nul))\r\n');
    return;
  }
  const f = path.join(dir, 'start.sh');
  if (fs.existsSync(f)) return;
  fs.writeFileSync(f, '#!/bin/bash\ncd ' + JSON.stringify(dir) + '\n'
    + 'if [ -x ./server ]; then exec ./server\n'
    + 'elif [ -x ./srcds_run ]; then exec ./srcds_run\n'
    + 'else echo "No game binary found. Edit start.sh"; sleep 9999999\nfi\n');
  try { fs.chmodSync(f, 0o755); } catch (e) {}
}

function runSync(dir, argv) {
  try {
    const r = spawnSync(IS_WIN ? argv[0] : 'bash', IS_WIN ? argv.slice(1) : ['-c'].concat(argv.join(' ')), {
      cwd: dir, encoding: 'utf8', timeout: 60 * 60 * 1000, windowsHide: true, shell: IS_WIN,
    });
    return { status: 'ok', output: (r.stdout || '') + (r.stderr || '') };
  } catch (e) {
    return { status: 'failed', error: String(e.message || e) };
  }
}

function steamArgs(dir, appid) {
  const steam = CFG.steamcmd || (IS_WIN ? 'steamcmd.exe' : 'steamcmd');
  const args = [steam, '+force_install_dir', dir];
  args.push((CFG.steam_user && CFG.steam_user !== 'anonymous') ? '+login' : '');
  if (CFG.steam_user && CFG.steam_user !== 'anonymous') {
    args.push(CFG.steam_user);
    if (CFG.steam_pass) args.push(CFG.steam_pass);
  } else {
    args[args.indexOf('')] = '+login';
    args.push('anonymous');
  }
  args.push('+app_update', String(appid), 'validate', '+quit');
  return args;
}

function doInstall(dir, payload, server) {
  fs.mkdirSync(dir, { recursive: true });
  rememberLocation(dir, payload, server);
  const appid = payload.appid || 0;
  if (appid && appid > 0) {
    const log = path.join(dir, 'install.log');
    const r = runSync(dir, steamArgs(dir, appid));
    fs.writeFileSync(log, r.output || '');
    if (r.status !== 'ok') return r;
  } else {
    fs.writeFileSync(path.join(dir, 'readme.txt'), 'Demo server installed.\n');
  }
  writeStartScript(dir);
  return { status: 'ok', location: path.dirname(path.resolve(dir)) };
}

function doStart(dir, payload, server) {
  fs.mkdirSync(dir, { recursive: true });
  rememberLocation(dir, payload, server);
  writeStartScript(dir);
  const log = path.join(dir, 'server.log');
  const pidFile = path.join(dir, 'server.pid');
  doStopSilent(dir);
  let child;
  if (IS_WIN) {
    child = spawn('cmd.exe', ['/c', path.join(dir, 'start.bat')], {
      cwd: dir, detached: true, windowsHide: true,
      stdio: ['ignore', fs.openSync(log, 'a'), fs.openSync(log, 'a')],
    });
  } else {
    child = spawn('bash', [path.join(dir, 'start.sh')], {
      cwd: dir, detached: true, stdio: ['ignore', fs.openSync(log, 'a'), fs.openSync(log, 'a')],
    });
  }
  child.unref();
  fs.writeFileSync(pidFile, String(child.pid));
  return { status: 'ok', pid: child.pid };
}

function doStopSilent(dir) {
  const pidFile = path.join(dir, 'server.pid');
  try {
    const pid = parseInt(readFileSafe(pidFile), 10);
    if (pid) {
      if (IS_WIN) { try { execSync('taskkill /F /T /PID ' + pid); } catch (e) {} }
      else { try { execSync('kill -9 ' + pid + ' 2>/dev/null'); } catch (e) {} }
    }
  } catch (e) {}
  if (IS_WIN) {
    try { execSync('taskkill /F /IM server.exe >nul 2>&1'); } catch (e) {}
  } else {
    try { execSync('pkill -f ' + JSON.stringify(path.join(dir, 'start.sh')) + ' 2>/dev/null; true'); } catch (e) {}
  }
  try { fs.unlinkSync(pidFile); } catch (e) {}
}

function doStop(dir) { doStopSilent(dir); return { status: 'ok' }; }

function doStatus(dir) {
  const pidFile = path.join(dir, 'server.pid');
  let pid = parseInt(readFileSafe(pidFile), 10);
  let running = !!pid;
  if (running) {
    if (IS_WIN) {
      try { execSync('tasklist /FI "PID eq ' + pid + '" | findstr /i "' + pid + '" >nul'); } catch (e) { running = false; }
    } else {
      try { process.kill(pid, 0); } catch (e) { running = false; }
    }
  }
  return { status: 'ok', running: running, pid: running ? pid : null };
}

function doLog(dir) {
  const log = path.join(dir, 'server.log');
  let text = '';
  if (fs.existsSync(log)) {
    const all = fs.readFileSync(log, 'utf8');
    text = all.split(/\r?\n/).slice(-50).join('\n');
  }
  return { status: 'ok', log: text };
}

function doDelete(dir) { try { fs.rmSync(dir, { recursive: true, force: true }); } catch (e) {} return { status: 'ok' }; }

async function poll() {
  try {
    const res = await fetch(CFG.panel_url + '/api/agent/commands?token=' + encodeURIComponent(CFG.node_token));
    if (!res.ok) return;
    const data = await res.json();
    if (!data || !data.job) return;

    const dir = dirFor(data.payload, data.server);
    let resultJson = { status: 'failed', error: 'unknown' };
    try {
      switch (data.job.command) {
        case 'install': resultJson = doInstall(dir, data.payload, data.server); break;
        case 'start': resultJson = doStart(dir, data.payload, data.server); break;
        case 'stop': resultJson = doStop(dir); break;
        case 'restart': doStop(dir); resultJson = doStart(dir, data.payload, data.server); break;
        case 'status': resultJson = doStatus(dir); break;
        case 'log': resultJson = doLog(dir); break;
        case 'delete': resultJson = doDelete(dir); break;
        default: resultJson = { status: 'failed', error: 'unknown command ' + data.job.command };
      }
    } catch (e) {
      resultJson = { status: 'failed', error: String(e.message || e) };
    }
    const ok = resultJson.status === 'ok';
    const body = new URLSearchParams();
    body.append('token', CFG.node_token);
    body.append('job_id', data.job.id);
    body.append('status', ok ? 'done' : 'failed');
    body.append('result', JSON.stringify(resultJson));
    await fetch(CFG.panel_url + '/api/agent/result', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: body.toString(),
    });
  } catch (e) {
    /* network / parse errors — ignore and retry */
  }
  writeStatus();
  reportEnv();
}

console.log('PH Game Node Agent v2.1 started. Polling ' + CFG.panel_url + ' every ' + CFG.poll_interval_ms + 'ms.');
console.log('Install locations: ' + CFG.locations.join(' | '));
setInterval(poll, CFG.poll_interval_ms);
poll();
