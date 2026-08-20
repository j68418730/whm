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
const CONFIG_PATH = path.join(__dirname, 'agent-config.json');

function loadConfig() {
  if (!fs.existsSync(CONFIG_PATH)) {
    console.error('Missing ' + CONFIG_PATH);
    process.exit(1);
  }
  const c = JSON.parse(fs.readFileSync(CONFIG_PATH, 'utf8'));
  c.base_dir = c.base_dir || (IS_WIN ? 'C:\\PlanetHostsGames' : '/var/gameservers');
  c.poll_interval_ms = c.poll_interval_ms || 10000;
  return c;
}

const CFG = loadConfig();
const BASE = CFG.base_dir;

function slugify(name) { return String(name || 'server').toLowerCase().replace(/[^a-z0-9_-]/g, ''); }
function dirFor(payload, server) { return path.join(BASE, slugify(payload.slug || (server && server.server_name) || 'server')); }
function readFileSafe(p) { try { return fs.readFileSync(p, 'utf8'); } catch (e) { return ''; } }

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

function doInstall(dir, payload) {
  fs.mkdirSync(dir, { recursive: true });
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
  return { status: 'ok' };
}

function doStart(dir) {
  fs.mkdirSync(dir, { recursive: true });
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
        case 'install': resultJson = doInstall(dir, data.payload); break;
        case 'start': resultJson = doStart(dir); break;
        case 'stop': resultJson = doStop(dir); break;
        case 'restart': doStop(dir); resultJson = doStart(dir); break;
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
}

console.log('PH Game Node Agent started. Polling ' + CFG.panel_url + ' every ' + CFG.poll_interval_ms + 'ms. Base dir: ' + BASE);
setInterval(poll, CFG.poll_interval_ms);
poll();
