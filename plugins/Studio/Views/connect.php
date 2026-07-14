<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Planet Hosts Studio — Connect</title>
<style>
  :root {
    --bg: #0e1116;
    --bg-card: #161b22;
    --bg-input: #0d1117;
    --bg-hover: #1f2630;
    --border: #2a313c;
    --text: #e6edf3;
    --text-dim: #8b949e;
    --accent: #2f81f7;
    --accent-2: #3fb950;
    --danger: #f85149;
    --warn: #d29922;
    --radius: 10px;
  }
  * { box-sizing: border-box; }
  body {
    margin: 0;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    background: var(--bg);
    color: var(--text);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 32px 16px 64px;
  }
  .wrap { width: 100%; max-width: 720px; }
  .brand {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 24px;
  }
  .brand .logo {
    width: 40px; height: 40px;
    border-radius: 10px;
    background: linear-gradient(135deg, var(--accent), #a371f7);
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 18px; color: #fff;
  }
  .brand h1 { font-size: 20px; margin: 0; font-weight: 600; }
  .brand p { margin: 2px 0 0; color: var(--text-dim); font-size: 13px; }

  .steps {
    display: flex;
    gap: 8px;
    margin-bottom: 20px;
  }
  .step-pill {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 12px;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    background: var(--bg-card);
    font-size: 12px;
    color: var(--text-dim);
    transition: all .2s;
  }
  .step-pill .num {
    width: 22px; height: 22px;
    border-radius: 50%;
    background: var(--bg-input);
    border: 1px solid var(--border);
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 12px;
  }
  .step-pill.active { border-color: var(--accent); color: var(--text); }
  .step-pill.active .num { background: var(--accent); border-color: var(--accent); color: #fff; }
  .step-pill.done .num { background: var(--accent-2); border-color: var(--accent-2); color: #fff; }
  .step-pill.done { color: var(--text); }

  .card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 24px;
    margin-bottom: 16px;
  }
  .card h2 { margin: 0 0 4px; font-size: 16px; }
  .card .sub { color: var(--text-dim); font-size: 13px; margin: 0 0 18px; }

  .field { margin-bottom: 16px; }
  .field label { display: block; font-size: 13px; margin-bottom: 6px; color: var(--text-dim); }
  .field input, .field select {
    width: 100%;
    padding: 11px 12px;
    background: var(--bg-input);
    border: 1px solid var(--border);
    border-radius: 8px;
    color: var(--text);
    font-size: 14px;
    outline: none;
    transition: border-color .15s;
  }
  .field input:focus, .field select:focus { border-color: var(--accent); }
  .field input:disabled { opacity: .55; cursor: not-allowed; }

  .row { display: flex; gap: 12px; }
  .row .field { flex: 1; }

  .checkbox { display: flex; align-items: center; gap: 10px; margin: 4px 0 18px; }
  .checkbox input { width: 18px; height: 18px; accent-color: var(--accent); }
  .checkbox label { margin: 0; color: var(--text); font-size: 14px; }

  .btn {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 11px 20px;
    background: var(--accent);
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: opacity .15s, background .15s;
  }
  .btn:hover { opacity: .9; }
  .btn:disabled { opacity: .5; cursor: not-allowed; }
  .btn.secondary { background: var(--bg-hover); }
  .btn.ghost { background: transparent; border: 1px solid var(--border); color: var(--text-dim); }
  .btn.green { background: var(--accent-2); }

  .msg { margin-top: 14px; font-size: 13px; padding: 10px 12px; border-radius: 8px; display: none; }
  .msg.ok { display: block; background: rgba(63,185,80,.12); color: var(--accent-2); border: 1px solid rgba(63,185,80,.3); }
  .msg.err { display: block; background: rgba(248,81,73,.12); color: var(--danger); border: 1px solid rgba(248,81,73,.3); }
  .msg.info { display: block; background: rgba(47,129,247,.12); color: var(--accent); border: 1px solid rgba(47,129,247,.3); }

  .hidden { display: none !important; }

  .station {
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 14px 16px;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: var(--bg-input);
  }
  .station .meta { font-size: 12px; color: var(--text-dim); margin-top: 4px; }
  .station .name { font-weight: 600; font-size: 14px; }
  .badge { font-size: 11px; padding: 2px 8px; border-radius: 20px; border: 1px solid var(--border); color: var(--text-dim); }
  .badge.live { color: var(--accent-2); border-color: rgba(63,185,80,.4); }
  .badge.off { color: var(--danger); border-color: rgba(248,81,73,.4); }

  .success-box {
    text-align: center;
    padding: 28px 16px;
  }
  .success-box .icon {
    width: 64px; height: 64px; margin: 0 auto 16px;
    border-radius: 50%;
    background: rgba(63,185,80,.15);
    border: 1px solid var(--accent-2);
    color: var(--accent-2);
    display: flex; align-items: center; justify-content: center;
    font-size: 32px;
  }
  pre.config {
    background: var(--bg-input);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 14px;
    text-align: left;
    font-size: 13px;
    color: var(--text);
    overflow-x: auto;
    margin-top: 16px;
  }
  .toolbar { display: flex; gap: 10px; margin-top: 16px; flex-wrap: wrap; }
</style>
</head>
<body>
<div class="wrap">
  <div class="brand">
    <div class="logo">PH</div>
    <div>
      <h1>Planet Hosts Studio</h1>
      <p>Connect your encoder in three secure steps</p>
    </div>
  </div>

  <div class="steps">
    <div class="step-pill" data-step="1"><span class="num">1</span> Planet Hosts Account</div>
    <div class="step-pill" data-step="2"><span class="num">2</span> DJ Login</div>
    <div class="step-pill" data-step="3"><span class="num">3</span> Station</div>
    <div class="step-pill" data-step="4"><span class="num">4</span> Connection</div>
  </div>

  <div id="section-1" class="card">
    <h2>Planet Hosts Account</h2>
    <p class="sub">Authenticate against Planet Hosts services using your API credentials.</p>
    <div class="field">
      <label for="ph-api-url">API URL</label>
      <input id="ph-api-url" type="text" placeholder="https://panel.planethosts.com" autocomplete="off">
    </div>
    <div class="field">
      <label for="ph-api-user">API Username</label>
      <input id="ph-api-user" type="text" placeholder="your-api-username" autocomplete="off">
    </div>
    <div class="field">
      <label for="ph-api-key">API Key</label>
      <input id="ph-api-key" type="password" placeholder="ph_..." autocomplete="off">
    </div>
    <button class="btn" id="btn-connect-ph">Connect To Planet Hosts</button>
    <div class="msg" id="msg-1"></div>
  </div>

  <div id="section-2" class="card hidden">
    <h2>DJ Login</h2>
    <p class="sub">Sign in with your DJ account assigned by your station manager.</p>
    <div class="field">
      <label for="dj-user">DJ Username</label>
      <input id="dj-user" type="text" placeholder="dj_username" autocomplete="off">
    </div>
    <div class="field">
      <label for="dj-pass">DJ Password</label>
      <input id="dj-pass" type="password" placeholder="••••••••" autocomplete="off">
    </div>
    <button class="btn" id="btn-auth-dj">Authenticate DJ</button>
    <div class="msg" id="msg-2"></div>
  </div>

  <div id="section-3" class="card hidden">
    <h2>Station Selection</h2>
    <p class="sub">Choose the station you want to broadcast to.</p>
    <div id="station-list"></div>
    <div class="msg" id="msg-3"></div>
  </div>

  <div id="section-4" class="card hidden">
    <h2>Advanced Connection</h2>
    <p class="sub">Your encoder credentials were retrieved automatically. Enable Manual Configuration Mode to edit them.</p>
    <div class="row">
      <div class="field">
        <label for="c-host">Hostname</label>
        <input id="c-host" type="text" disabled>
      </div>
      <div class="field" style="max-width:140px">
        <label for="c-port">Port</label>
        <input id="c-port" type="text" disabled>
      </div>
    </div>
    <div class="row">
      <div class="field">
        <label for="c-user">Username</label>
        <input id="c-user" type="text" disabled>
      </div>
      <div class="field">
        <label for="c-pass">Password</label>
        <input id="c-pass" type="text" disabled>
      </div>
    </div>
    <div class="row">
      <div class="field">
        <label for="c-mount">Mount</label>
        <input id="c-mount" type="text" disabled>
      </div>
      <div class="field" style="max-width:200px">
        <label for="c-proto">Protocol</label>
        <select id="c-proto" disabled>
          <option value="icecast">Icecast</option>
          <option value="shoutcast_v2">Shoutcast V2</option>
          <option value="shoutcast_v1">Shoutcast V1</option>
        </select>
      </div>
    </div>
    <div class="checkbox">
      <input type="checkbox" id="manual-mode">
      <label for="manual-mode">Manual Configuration Mode</label>
    </div>
    <button class="btn green" id="btn-connect-stream">Connect</button>
    <div class="msg" id="msg-4"></div>
  </div>

  <div id="section-done" class="card hidden">
    <div class="success-box">
      <div class="icon">&#10003;</div>
      <h2 style="margin:0">You're ready to broadcast</h2>
      <p class="sub" style="margin-top:8px">Copy these settings into your encoder (butt, MIXXX, Traktor, etc.) and start your source.</p>
      <pre class="config" id="config-text"></pre>
      <div class="toolbar">
        <button class="btn" id="btn-copy">Copy Configuration</button>
        <button class="btn ghost" id="btn-reset">Disconnect &amp; Reset</button>
      </div>
    </div>
  </div>
</div>

<script>
const KEYS = {
  url: 'ph_studio_api_url',
  user: 'ph_studio_api_user',
  key: 'ph_studio_api_key',
  token: 'ph_studio_dj_token',
  station: 'ph_studio_station_id'
};

function lsGet(k, d) { const v = localStorage.getItem(k); return v === null ? d : v; }
function lsSet(k, v) { localStorage.setItem(k, v); }
function lsDel(k) { localStorage.removeItem(k); }

function apiBase() {
  let u = (document.getElementById('ph-api-url').value || '').trim();
  if (!u) u = location.origin;
  u = u.replace(/\/+$/, '');
  if (!/\/api$/.test(u)) u += '/api';
  return u;
}

async function api(path, opts = {}) {
  const headers = Object.assign({}, opts.headers || {});
  const init = { method: opts.method || 'GET', headers };
  if (opts.body) init.body = opts.body;
  let res, data = null;
  try {
    res = await fetch(apiBase() + path, init);
    try { data = await res.json(); } catch (e) { data = null; }
  } catch (e) {
    return { ok: false, status: 0, data: { error: 'Network error: ' + e.message } };
  }
  return { ok: res.ok, status: res.status, data };
}

function showMsg(id, text, kind) {
  const el = document.getElementById(id);
  el.textContent = text;
  el.className = 'msg ' + (kind || 'info');
}
function clearMsg(id) { document.getElementById(id).className = 'msg'; }

function setStep(n, state) {
  const pill = document.querySelector('.step-pill[data-step="' + n + '"]');
  if (!pill) return;
  pill.classList.remove('active', 'done');
  if (state) pill.classList.add(state);
}
function showSection(id) { document.getElementById(id).classList.remove('hidden'); }
function hideSection(id) { document.getElementById(id).classList.add('hidden'); }

function restore() {
  document.getElementById('ph-api-url').value = lsGet(KEYS.url, location.origin);
  document.getElementById('ph-api-user').value = lsGet(KEYS.user, '');
  document.getElementById('ph-api-key').value = lsGet(KEYS.key, '');
  if (lsGet(KEYS.key, '')) {
    document.getElementById('section-1').classList.remove('hidden');
    setStep(1, null);
  }
}

async function connectPlanetHosts() {
  clearMsg('msg-1');
  const url = document.getElementById('ph-api-url').value.trim();
  const user = document.getElementById('ph-api-user').value.trim();
  const key = document.getElementById('ph-api-key').value.trim();
  if (!url || !key) { showMsg('msg-1', 'API URL and API Key are required.', 'err'); return; }
  const btn = document.getElementById('btn-connect-ph');
  btn.disabled = true; btn.textContent = 'Connecting…';
  const { ok, data } = await api('/v1/health', { headers: { 'X-API-Key': key } });
  btn.disabled = false; btn.textContent = 'Connect To Planet Hosts';
  if (!ok) {
    showMsg('msg-1', (data && data.error) ? data.error : 'Connection failed (HTTP ' + 0 + ').', 'err');
    return;
  }
  lsSet(KEYS.url, url);
  lsSet(KEYS.user, user);
  lsSet(KEYS.key, key);
  showMsg('msg-1', 'Connected to Planet Hosts.', 'ok');
  setStep(1, 'done');
  setStep(2, 'active');
  showSection('section-2');
}

async function authDj() {
  clearMsg('msg-2');
  const username = document.getElementById('dj-user').value.trim();
  const password = document.getElementById('dj-pass').value.trim();
  const apiUser = lsGet(KEYS.user, '');
  if (!username || !password) { showMsg('msg-2', 'DJ Username and Password are required.', 'err'); return; }
  const btn = document.getElementById('btn-auth-dj');
  btn.disabled = true; btn.textContent = 'Authenticating…';
  const body = new URLSearchParams();
  body.set('username', username);
  body.set('password', password);
  if (apiUser) body.set('api_username', apiUser);
  const { ok, data } = await api('/dj/login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: body.toString()
  });
  btn.disabled = false; btn.textContent = 'Authenticate DJ';
  if (!ok || !data || !data.success) {
    showMsg('msg-2', (data && data.error) ? data.error : 'Authentication failed.', 'err');
    return;
  }
  lsSet(KEYS.token, data.token);
  showMsg('msg-2', 'DJ authenticated. ' + (data.stations ? data.stations.length : 0) + ' station(s) assigned.', 'ok');
  setStep(2, 'done');
  renderStations(data.stations || []);
  setStep(3, 'active');
  showSection('section-3');
}

function renderStations(stations) {
  const list = document.getElementById('station-list');
  list.innerHTML = '';
  if (!stations.length) {
    list.innerHTML = '<p class="sub">No stations are assigned to this DJ account.</p>';
    return;
  }
  stations.forEach(function (s) {
    const live = (s.status === 'running' || s.status === 'live' || s.status === 'online');
    const row = document.createElement('div');
    row.className = 'station';
    row.innerHTML =
      '<div>' +
        '<div class="name">' + escapeHtml(s.name) + '</div>' +
        '<div class="meta">' + escapeHtml(s.stream_type || 'icecast') + ' • ' + (s.bitrate || 128) + ' kbps • ' + (s.listeners || 0) + ' listeners</div>' +
      '</div>' +
      '<div style="display:flex;align-items:center;gap:12px">' +
        '<span class="badge ' + (live ? 'live' : 'off') + '">' + (live ? 'LIVE' : 'OFFLINE') + '</span>' +
        '<button class="btn" data-id="' + s.station_id + '">Connect</button>' +
      '</div>';
    row.querySelector('button').addEventListener('click', function () { selectStation(s.station_id); });
    list.appendChild(row);
  });
}

async function selectStation(id) {
  clearMsg('msg-3');
  const token = lsGet(KEYS.token, '');
  const key = lsGet(KEYS.key, '');
  showMsg('msg-3', 'Retrieving stream credentials…', 'info');
  const { ok, data } = await api('/stations/' + id + '/stream', { headers: { 'X-API-Key': key } });
  if (!ok || !data || !data.success) {
    clearMsg('msg-3');
    showMsg('msg-3', (data && data.error) ? data.error : 'Could not load stream configuration.', 'err');
    return;
  }
  lsSet(KEYS.station, String(id));
  const c = data.data;
  document.getElementById('c-host').value = c.hostname || '';
  document.getElementById('c-port').value = c.port || '';
  document.getElementById('c-user').value = c.username || '';
  document.getElementById('c-pass').value = c.password || '';
  document.getElementById('c-mount').value = c.mount || '';
  document.getElementById('c-proto').value = c.protocol || 'icecast';
  clearMsg('msg-3');
  setStep(3, 'done');
  setStep(4, 'active');
  showSection('section-4');
}

document.getElementById('manual-mode').addEventListener('change', function () {
  const disabled = !this.checked;
  ['c-host', 'c-port', 'c-user', 'c-pass', 'c-mount', 'c-proto'].forEach(function (id) {
    document.getElementById(id).disabled = disabled;
  });
});

document.getElementById('btn-connect-stream').addEventListener('click', function () {
  const config = buildConfig();
  document.getElementById('config-text').textContent = config;
  hideSection('section-4');
  setStep(4, 'done');
  showSection('section-done');
});

function buildConfig() {
  const proto = document.getElementById('c-proto').value;
  const host = document.getElementById('c-host').value;
  const port = document.getElementById('c-port').value;
  const user = document.getElementById('c-user').value;
  const pass = document.getElementById('c-pass').value;
  const mount = document.getElementById('c-mount').value;
  let lines = [];
  lines.push('Type: ' + proto);
  lines.push('Server: ' + host + ':' + port);
  if (user) lines.push('Username: ' + user);
  lines.push('Password: ' + pass);
  if (mount && proto === 'icecast') lines.push('Mountpoint: ' + mount);
  if (proto === 'shoutcast_v1') lines.push('Stream ID: 1');
  return lines.join('\n');
}

document.getElementById('btn-copy').addEventListener('click', function () {
  const text = document.getElementById('config-text').textContent;
  navigator.clipboard.writeText(text).then(function () {
    const b = document.getElementById('btn-copy');
    const old = b.textContent; b.textContent = 'Copied!';
    setTimeout(function () { b.textContent = old; }, 1500);
  });
});

document.getElementById('btn-reset').addEventListener('click', function () {
  [KEYS.url, KEYS.user, KEYS.key, KEYS.token, KEYS.station].forEach(lsDel);
  location.reload();
});

function escapeHtml(s) {
  return String(s == null ? '' : s).replace(/[&<>"']/g, function (m) {
    return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m];
  });
}

document.getElementById('btn-connect-ph').addEventListener('click', connectPlanetHosts);
document.getElementById('btn-auth-dj').addEventListener('click', authDj);
restore();
</script>
</body>
</html>
