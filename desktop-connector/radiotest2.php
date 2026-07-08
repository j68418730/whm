<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Planet Hosts Studio — Full Layout</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:Inter,-apple-system,sans-serif;background:radial-gradient(ellipse at top,#0d1117,#010409);color:#e6edf3;overflow:hidden;height:100vh;font-size:14px}

/* Menu Bar */
#menu{background:rgba(22,27,34,.85);backdrop-filter:blur(16px);border-bottom:1px solid rgba(48,54,61,.15);padding:6px 14px;display:flex;gap:18px;font-size:12px;color:#8b949e;height:34px;align-items:center}
#menu span{cursor:default;padding:2px 4px}
#menu span:hover{color:#e6edf3}

/* Tabs */
#tabs{background:rgba(22,27,34,.6);backdrop-filter:blur(12px);border-bottom:1px solid rgba(48,54,61,.1);padding:4px 10px;display:flex;gap:4px;height:36px;align-items:center}
#tabs button{padding:5px 12px;border-radius:6px;border:none;background:transparent;color:#8b949e;font-size:11px;cursor:default;font-weight:500}
#tabs button.act{background:rgba(88,166,255,.1);color:#58a6ff;font-weight:600}

/* Tab Content */
.tab{display:none;height:calc(100vh - 70px);padding:6px}
.tab.act{display:block}

/* Studio Grid */
.studio-grid{display:grid;grid-template-columns:1fr 1fr 220px;gap:6px;height:100%}
.studio-grid .full{grid-column:1/-1}

/* Panels */
.panel{background:rgba(22,27,34,.55);backdrop-filter:blur(8px);border:1px solid rgba(48,54,61,.1);border-radius:10px;display:flex;flex-direction:column}
.panel-hdr{background:rgba(48,54,61,.08);padding:5px 8px;font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#8b949e;display:flex;justify-content:space-between;align-items:center;border-radius:10px 10px 0 0;cursor:default}
.panel-hdr .grip{font-size:10px;color:#3d4452;margin-right:4px;cursor:grab}
.panel-hdr .grip:hover{color:#8b949e}
.panel-hdr .win-btns{display:flex;gap:2px;margin-left:4px}
.panel-hdr .win-btns span{width:12px;height:12px;border-radius:3px;display:flex;align-items:center;justify-content:center;font-size:7px;color:#475569;cursor:default}
.panel-hdr .win-btns span:hover{background:rgba(255,255,255,.06);color:#8b949e}
.panel-hdr .win-btns .close:hover{background:rgba(248,81,73,.15);color:#f85149}
.panel-body{padding:6px;flex:1;overflow:hidden}

/* Decks */
.deck{flex:1;display:flex;flex-direction:column;padding:8px;background:rgba(22,27,34,.4);border-radius:8px;border:1px solid rgba(48,54,61,.06);margin-bottom:4px}
.deck .dh{display:flex;justify-content:space-between;align-items:center;margin-bottom:4px}
.deck .dh .l{font-size:10px;font-weight:800;letter-spacing:.5px}
.deck .dh .l.a{color:#58a6ff}
.deck .dh .l.b{color:#a855f7}
.deck .dh .s{font-size:9px;padding:1px 6px;border-radius:3px;background:rgba(255,255,255,.04);color:#64748b}

/* Voice FX */
.vx-row{display:flex;align-items:center;gap:4px;padding:2px 0;font-size:10px;color:#8b949e}
.vx-row label{min-width:50px;font-size:9px}
.vx-row input[type=range]{flex:1;height:2px;-webkit-appearance:none;background:rgba(255,255,255,.06);border-radius:2px;outline:none}
.vx-row input::-webkit-slider-thumb{-webkit-appearance:none;width:8px;height:8px;border-radius:50%;background:#8b949e}
.vx-btn{width:100%;padding:4px;border-radius:5px;border:none;font-size:10px;font-weight:600;background:rgba(88,166,255,.1);color:#58a6ff;cursor:default;margin-top:4px}

/* Jingle Cart */
.jingle-grid{display:grid;grid-template-columns:1fr 1fr;gap:3px}
.jingle-grid button{padding:5px;border-radius:5px;border:none;font-size:9px;font-weight:600;cursor:default}
.jing{background:rgba(88,166,255,.1);color:#58a6ff}
.adv{background:rgba(168,85,247,.1);color:#d8b4fe}
.idc{background:rgba(63,185,80,.1);color:#3fb950}
.emd{background:rgba(248,81,73,.1);color:#f85149}

/* Playlist / Library table */
table{width:100%;border-collapse:collapse;font-size:10px}
th{padding:3px 6px;text-align:left;font-weight:600;color:#475569;font-size:8px;text-transform:uppercase;border-bottom:1px solid rgba(48,54,61,.06)}
td{padding:3px 6px;border-bottom:1px solid rgba(48,54,61,.03);color:#8b949e;font-size:10px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}

/* Queue row */
.q-row{display:flex;gap:6px;height:calc(100% - 24px)}
.q-row .q-panel{flex:1}
.q-row .s-panel{flex:0.5}

/* Status row */
.stat-row{display:flex;gap:6px;margin-top:4px;flex-shrink:0}
.stat-row div{flex:1;background:rgba(13,17,23,.3);border-radius:6px;padding:4px 8px;text-align:center}
.stat-row .n{font-size:16px;font-weight:700;color:#58a6ff}
.stat-row .l{font-size:8px;color:#475569;text-transform:uppercase;letter-spacing:.5px}

/* Pipeline SVG area */
.pipe-area{display:flex;align-items:center;justify-content:center;height:100%;gap:0;flex-wrap:wrap}
.pipe-node{background:rgba(22,27,34,.6);border:1px solid rgba(48,54,61,.15);border-radius:8px;padding:8px 14px;text-align:center;font-size:10px;color:#8b949e}
.pipe-node.act{background:rgba(88,166,255,.08);border-color:rgba(88,166,255,.2);color:#58a6ff}
.pipe-arrow{font-size:16px;color:#3d4452;padding:0 4px}

/* Login form */
.login-form{max-width:340px;margin:60px auto}
.login-form .fg{margin-bottom:10px}
.login-form .fg label{font-size:10px;color:#64748b;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:3px}
.login-form .fg input{width:100%;padding:8px 10px;border-radius:6px;border:1px solid rgba(48,54,61,.2);background:rgba(13,17,23,.5);color:#e6edf3;font-size:13px;outline:none}
.login-form button{width:100%;padding:10px;border-radius:8px;border:none;background:linear-gradient(135deg,#58a6ff,#1f6feb);color:#fff;font-weight:700;font-size:13px;cursor:default;transition:.2s}
</style>
</head>
<body>

<div id="menu">
<span>File</span><span>Edit</span><span>Broadcast</span><span>Tools</span><span>Window</span><span>Help</span>
<span style="flex:1"></span>
<span id="muteBtn" onclick="toggleMute()" style="cursor:default;padding:2px 8px;border-radius:4px;font-size:13px">🔊</span>
<span style="color:#475569;font-size:10px">Spectre</span>
</div>

<div id="tabs">
<button class="act" onclick="switchTab('dashboard')">Dashboard A</button>
<button onclick="switchTab('dashboardb')">Dashboard B</button>
<button onclick="switchTab('dashboardc')">Dashboard C</button>
<button onclick="switchTab('crossfade')">Crossfade</button>
<button onclick="switchTab('station')">Station</button>
<button onclick="switchTab('login')">Planet Hosts</button>
<button onclick="switchTab('stats')">Statistics</button>
<button onclick="switchTab('encoder')">Encoders</button>
<button onclick="switchTab('scheduler')">Scheduler</button>
<button onclick="switchTab('events')">Events</button>
<button onclick="switchTab('requests')">Requests</button>
<button onclick="switchTab('ai')">AI Assistant</button>
</div>

<!-- CLOCK BAR -->
<div class="clock-bar" style="display:flex;gap:12px;padding:4px 0 6px;align-items:center;flex-wrap:wrap;border-bottom:1px solid rgba(48,54,61,.06);margin-bottom:4px">
<div class="clock-cluster" style="display:flex;gap:8px;align-items:center">
<div class="clock-tz" style="text-align:center"><div style="font-size:16px;font-weight:700;font-family:monospace;color:#e6edf3">14:32</div><div style="font-size:8px;color:#475569;text-transform:uppercase">Local</div></div>
<div class="clock-tz" style="text-align:center"><div style="font-size:16px;font-weight:700;font-family:monospace;color:#58a6ff">19:32</div><div style="font-size:8px;color:#475569;text-transform:uppercase">UTC</div></div>
<div class="clock-tz" style="text-align:center"><div style="font-size:16px;font-weight:700;font-family:monospace;color:#a855f7">06:32</div><div style="font-size:8px;color:#475569;text-transform:uppercase">Tokyo</div></div>
</div>
<span style="color:#3d4452">|</span>
<button style="padding:3px 8px;border-radius:5px;border:none;background:rgba(88,166,255,.08);color:#58a6ff;font-size:9px;cursor:default;font-weight:600">+ Timezone</button>
<span style="flex:1"></span>
<button class="add-widget-btn" style="padding:3px 8px;border-radius:5px;border:none;background:rgba(255,255,255,.03);color:#8b949e;font-size:9px;cursor:default">➕ Widget</button>
</div>

<!-- DASHBOARD A: Deck A, Deck B, Voice FX, Playlist, Queue, History, Volume, Clock -->
<div id="tab-dashboard" class="tab act">
<div style="display:grid;grid-template-columns:1fr 1fr 220px;gap:4px;height:100%">

<div class="panel" style="grid-row:span 3"><div class="panel-hdr"><span><span class="grip">⠿</span>Deck A</span><span class="win-btns"><span>─</span><span>□</span><span class="close">✕</span></span></div><div class="panel-body" style="display:flex;flex-direction:column;gap:2px">
<div class="deck"><div class="dh"><span class="l a">DECK A</span><span class="s">Playing</span></div>
<div style="display:flex;gap:6px">
<div style="width:48px;height:48px;border-radius:8px;background:linear-gradient(135deg,rgba(88,166,255,.08),rgba(168,85,247,.04));border:1px solid rgba(48,54,61,.1);display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0">🎸</div>
<div style="flex:1"><div style="font-size:12px;font-weight:700">Back in Black</div><div style="font-size:10px;color:#8b949e">AC/DC</div></div></div>
<div style="display:flex;gap:2px;margin:2px 0">
<button style="flex:1;height:16px;border-radius:3px;border:none;font-size:8px;background:rgba(255,255,255,.04);color:#8b949e;cursor:default">▶</button>
<button style="flex:1;height:16px;border-radius:3px;border:none;font-size:8px;background:rgba(255,255,255,.04);color:#8b949e;cursor:default">⏸</button>
<button style="flex:1;height:16px;border-radius:3px;border:none;font-size:8px;background:rgba(255,255,255,.04);color:#8b949e;cursor:default">⏹</button>
<button style="flex:1;height:16px;border-radius:3px;border:none;font-size:8px;background:rgba(255,255,255,.04);color:#8b949e;cursor:default">⏮</button>
<button style="flex:1;height:16px;border-radius:3px;border:none;font-size:8px;background:rgba(255,255,255,.04);color:#8b949e;cursor:default">⏭</button>
</div>
<div style="height:2px;background:rgba(255,255,255,.04);border-radius:2px"><div style="width:62%;height:100%;background:linear-gradient(90deg,#58a6ff,#79c0ff);border-radius:2px"></div></div>
<div style="display:flex;justify-content:space-between;font-size:8px;color:#64748b"><span>1:52</span><span>-1:08</span></div>
</div></div></div>

<div class="panel" style="grid-row:span 3"><div class="panel-hdr"><span><span class="grip">⠿</span>Deck B</span><span class="win-btns"><span>─</span><span>□</span><span class="close">✕</span></span></div><div class="panel-body" style="display:flex;flex-direction:column;gap:2px">
<div class="deck"><div class="dh"><span class="l b">DECK B</span><span class="s">Cued</span></div>
<div style="display:flex;gap:6px">
<div style="width:48px;height:48px;border-radius:8px;background:linear-gradient(135deg,rgba(168,85,247,.08),rgba(88,166,255,.04));border:1px solid rgba(48,54,61,.1);display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0">🎵</div>
<div style="flex:1"><div style="font-size:12px;font-weight:700">Stairway to Heaven</div><div style="font-size:10px;color:#8b949e">Led Zeppelin</div></div></div>
<div style="display:flex;gap:2px;margin:2px 0">
<button style="flex:1;height:16px;border-radius:3px;border:none;font-size:8px;background:rgba(255,255,255,.04);color:#8b949e;cursor:default">▶</button>
<button style="flex:1;height:16px;border-radius:3px;border:none;font-size:8px;background:rgba(255,255,255,.04);color:#8b949e;cursor:default">⏸</button>
<button style="flex:1;height:16px;border-radius:3px;border:none;font-size:8px;background:rgba(255,255,255,.04);color:#8b949e;cursor:default">⏹</button>
<button style="flex:1;height:16px;border-radius:3px;border:none;font-size:8px;background:rgba(255,255,255,.04);color:#8b949e;cursor:default">⏮</button>
<button style="flex:1;height:16px;border-radius:3px;border:none;font-size:8px;background:rgba(255,255,255,.04);color:#8b949e;cursor:default">⏭</button>
</div>
<div style="height:2px;background:rgba(255,255,255,.04);border-radius:2px"><div style="width:0%;height:100%;background:linear-gradient(90deg,#a855f7,#d8b4fe);border-radius:2px"></div></div>
<div style="display:flex;justify-content:space-between;font-size:8px;color:#64748b"><span>0:00</span><span>-8:02</span></div>
</div></div></div>

<div style="display:flex;flex-direction:column;gap:4px">
<div class="panel" style="flex:1"><div class="panel-hdr"><span><span class="grip">⠿</span>Voice FX</span><span class="win-btns"><span>─</span><span>□</span><span class="close">✕</span></span></div><div class="panel-body" style="padding:4px;display:flex;flex-direction:column;gap:2px;font-size:9px">
<div class="vx-row"><label>Mic</label><select style="flex:1;padding:2px;border-radius:3px;border:1px solid rgba(48,54,61,.15);background:rgba(13,17,23,.4);color:#e6edf3;font-size:8px;outline:none"><option>Microphone</option></select></div>
<div class="vx-row"><label>Gain</label><input type="range" value="80"><span style="color:#58a6ff">80%</span></div>
<div class="vx-row"><label>Duck</label><input type="range" value="30"><span>30%</span></div>
<div class="vx-row"><label>Comp</label><input type="range" value="30"><span>30%</span></div>
<div class="vx-row"><label>Gate</label><input type="range" value="20"><span>20%</span></div>
<button class="vx-btn">🎤 PTT</button>
</div></div>
<div class="panel" style="flex:0.8"><div class="panel-hdr"><span><span class="grip">⠿</span>Volume</span><span class="win-btns"><span>─</span><span>□</span><span class="close">✕</span></span></div><div class="panel-body" style="padding:4px;display:flex;flex-direction:column;gap:3px;font-size:9px">
<div class="vx-row"><label>Master</label><input type="range" value="75" style="flex:1"><span>75%</span></div>
<div class="vx-row"><label>Deck A</label><input type="range" value="80" style="flex:1"><span>80%</span></div>
<div class="vx-row"><label>Deck B</label><input type="range" value="70" style="flex:1"><span>70%</span></div>
<div class="vx-row"><label>Mic</label><input type="range" value="85" style="flex:1"><span>85%</span></div>
</div></div>
<div class="panel" style="flex:0.6"><div class="panel-hdr"><span><span class="grip">⠿</span>Clock</span><span class="win-btns"><span>─</span><span>□</span><span class="close">✕</span></span></div><div class="panel-body" style="padding:4px;display:flex;gap:6px;justify-content:center;align-items:center">
<div style="text-align:center"><div style="font-size:14px;font-weight:700;font-family:monospace;color:#e6edf3">14:32</div><div style="font-size:7px;color:#475569">12h</div></div>
<div style="text-align:center"><div style="font-size:14px;font-weight:700;font-family:monospace;color:#58a6ff">19:32</div><div style="font-size:7px;color:#475569">24h</div></div>
</div></div>
</div>

<div class="panel full" style="flex:1;grid-column:1/-1"><div class="panel-hdr"><span><span class="grip">⠿</span>Playlist</span><span style="display:flex;align-items:center;gap:3px;font-size:8px"><span style="color:#58a6ff">Music</span><span style="color:#8b949e">Content</span><span style="color:#8b949e">Special</span><span style="color:#8b949e">Adds</span><span class="win-btns"><span>─</span><span>□</span><span class="close">✕</span></span></span></div>
<div class="panel-body"><div style="display:flex;gap:4px;height:100%">
<div style="width:140px;border-right:1px solid rgba(48,54,61,.06);padding-right:4px;overflow-y:auto;font-size:10px">
<div style="color:#58a6ff;font-weight:600;padding:2px 4px">📁 Music</div>
<div style="color:#8b949e;padding:2px 4px">📁 Content</div>
<div style="color:#8b949e;padding:2px 4px">📁 Special</div>
<div style="color:#8b949e;padding:2px 4px">📁 Adds</div>
</div>
<div style="flex:1;overflow:auto">
<table><thead><tr><th style="width:16px"></th><th>Title</th><th>Artist</th><th style="width:35px">Time</th></tr></thead>
<tbody>
<tr><td>🎵</td><td>Back in Black</td><td>AC/DC</td><td>4:15</td></tr>
<tr><td>🎵</td><td>Highway to Hell</td><td>AC/DC</td><td>3:28</td></tr>
<tr><td>🎵</td><td>Thunderstruck</td><td>AC/DC</td><td>4:52</td></tr>
<tr><td>🎵</td><td>Whole Lotta Love</td><td>Led Zeppelin</td><td>5:34</td></tr>
</tbody></table>
</div>
<div style="width:200px;display:flex;flex-direction:column;gap:3px">
<div class="panel" style="flex:1"><div class="panel-hdr"><span><span class="grip">⠿</span>Queue</span><span class="win-btns"><span>─</span><span>□</span><span class="close">✕</span></span></div><div class="panel-body" style="padding:2px 4px;font-size:9px;color:#8b949e">
<div>▶ Bohemian Rhapsody</div>
<div>Enter Sandman</div>
<div>Smells Like Teen Spirit</div>
</div></div>
<div class="panel" style="flex:1"><div class="panel-hdr"><span><span class="grip">⠿</span>History</span><span class="win-btns"><span>─</span><span>□</span><span class="close">✕</span></span></div><div class="panel-body" style="padding:2px 4px;font-size:8px;color:#475569">
<div>14:32 Back in Black</div>
<div>14:28 Stairway to Heaven</div>
<div>14:22 Comfortably Numb</div>
</div></div>
</div>
</div></div></div>

</div></div>

<!-- DASHBOARD B: Statistics, Encoders, Event Logs, Requests, Scheduler, FTP Logs -->
<div id="tab-dashboardb" class="tab">

<!-- DASHBOARD B TAB -->
<div id="tab-dashboardb" class="tab">
<div class="clock-bar" style="display:flex;gap:12px;padding:4px 0 6px;align-items:center;flex-wrap:wrap;border-bottom:1px solid rgba(48,54,61,.06);margin-bottom:4px">
<div class="clock-cluster" style="display:flex;gap:8px;align-items:center">
<div style="text-align:center"><div style="font-size:16px;font-weight:700;font-family:monospace;color:#e6edf3">14:32</div><div style="font-size:8px;color:#475569;text-transform:uppercase">EST</div></div>
<div style="text-align:center"><div style="font-size:16px;font-weight:700;font-family:monospace;color:#58a6ff">11:32</div><div style="font-size:8px;color:#475569;text-transform:uppercase">PST</div></div>
</div>
<button style="padding:3px 8px;border-radius:5px;border:none;background:rgba(88,166,255,.08);color:#58a6ff;font-size:9px;cursor:default">+ Timezone</button>
<span style="flex:1"></span><button style="padding:3px 8px;border-radius:5px;border:none;background:rgba(255,255,255,.03);color:#8b949e;font-size:9px;cursor:default">➕ Widget</button>
</div>
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:4px;height:100%">
<div class="panel" style="grid-row:span 3"><div class="panel-hdr"><span><span class="grip">⠿</span>Statistics</span><span class="win-btns"><span>─</span><span>□</span><span class="close">✕</span></span></div><div class="panel-body" style="padding:4px">
<div style="display:grid;grid-template-columns:1fr 1fr;gap:4px;margin-bottom:4px">
<div style="background:rgba(13,17,23,.3);border-radius:5px;padding:6px;text-align:center"><div style="font-size:16px;font-weight:700;color:#3fb950">3</div><div style="font-size:7px;color:#475569;text-transform:uppercase">Current</div></div>
<div style="background:rgba(13,17,23,.3);border-radius:5px;padding:6px;text-align:center"><div style="font-size:16px;font-weight:700;color:#d29922">12</div><div style="font-size:7px;color:#475569;text-transform:uppercase">Peak</div></div>
</div>
<div style="height:60px;background:rgba(13,17,23,.2);border-radius:4px;margin-bottom:4px"></div>
<div style="display:flex;justify-content:space-between;font-size:8px;color:#475569"><span>Bandwidth: 1.2 Mbps</span><span>Avg: 4.2 min</span></div>
</div></div>
<div class="panel" style="grid-row:span 2"><div class="panel-hdr"><span><span class="grip">⠿</span>Encoders</span><span class="win-btns"><span>─</span><span>□</span><span class="close">✕</span></span></div><div class="panel-body" style="padding:4px;font-size:9px">
<div style="display:flex;justify-content:space-between;padding:2px 4px;border-bottom:1px solid rgba(48,54,61,.04)"><span>Main</span><span style="color:#3fb950">✓ Connected</span><span>128k</span></div>
<div style="display:flex;justify-content:space-between;padding:2px 4px"><span>Backup</span><span style="color:#64748b">○ Standby</span><span>128k</span></div>
</div></div>
<div class="panel" style="grid-row:span 2"><div class="panel-hdr"><span><span class="grip">⠿</span>Requests</span><span style="display:flex;align-items:center;gap:4px"><span style="font-size:9px;color:#58a6ff">2</span><span class="win-btns"><span>─</span><span>□</span><span class="close">✕</span></span></span></div><div class="panel-body" style="padding:4px;font-size:9px">
<div style="display:flex;align-items:center;gap:4px;padding:2px"><span style="flex:1">Free Bird</span><button style="padding:1px 5px;border-radius:3px;border:none;background:rgba(63,185,80,.1);color:#3fb950;font-size:8px;cursor:default">✓</button><button style="padding:1px 5px;border-radius:3px;border:none;background:rgba(248,81,73,.1);color:#f85149;font-size:8px;cursor:default">✕</button></div>
<div style="display:flex;align-items:center;gap:4px;padding:2px"><span style="flex:1">Sweet Child</span><button style="padding:1px 5px;border-radius:3px;border:none;background:rgba(63,185,80,.1);color:#3fb950;font-size:8px;cursor:default">✓</button><button style="padding:1px 5px;border-radius:3px;border:none;background:rgba(248,81,73,.1);color:#f85149;font-size:8px;cursor:default">✕</button></div>
</div></div>
<div class="panel" style="grid-row:span 2"><div class="panel-hdr"><span><span class="grip">⠿</span>Event Log</span><span class="win-btns"><span>─</span><span>□</span><span class="close">✕</span></span></div><div class="panel-body" style="padding:4px;font-family:monospace;font-size:8px;color:#8b949e;overflow:auto">
<div>14:32 ▶ Track start</div>
<div>14:31 🔴 Stream started</div>
<div>14:30 ✓ Request approved</div>
<div>14:28 🎤 Mic on</div>
<div>14:27 🔔 Jingle played</div>
</div></div>
<div class="panel" style="grid-row:span 3"><div class="panel-hdr"><span><span class="grip">⠿</span>Scheduler</span><span class="win-btns"><span>─</span><span>□</span><span class="close">✕</span></span></div><div class="panel-body" style="padding:4px">
<div style="display:grid;grid-template-columns:repeat(7,1fr);gap:2px;font-size:8px;text-align:center;color:#475569">
<div>Sun</div><div>Mon</div><div>Tue</div><div>Wed</div><div>Thu</div><div style="color:#58a6ff">Fri</div><div>Sat</div></div>
<div style="margin-top:4px;font-size:9px;color:#8b949e">Fri: Morning 8-10 · Rock 10-12</div>
</div></div>
<div class="panel" style="grid-row:span 1"><div class="panel-hdr"><span><span class="grip">⠿</span>FTP Log</span><span class="win-btns"><span>─</span><span>□</span><span class="close">✕</span></span></div><div class="panel-body" style="padding:4px;font-size:8px;color:#475569;font-family:monospace">
<div>14:30 Upload: podcast.mp3 ✓</div>
<div>14:15 Upload: show.mp3 ✓</div>
</div></div>
</div></div>

<!-- DASHBOARD C: Same as A + Fade Control -->
<div id="tab-dashboardc" class="tab">
<div class="clock-bar" style="display:flex;gap:12px;padding:4px 0 6px;align-items:center;flex-wrap:wrap;border-bottom:1px solid rgba(48,54,61,.06);margin-bottom:4px">
<div class="clock-cluster" style="display:flex;gap:8px;align-items:center">
<div style="text-align:center"><div style="font-size:16px;font-weight:700;font-family:monospace;color:#e6edf3">14:32</div><div style="font-size:8px;color:#475569;text-transform:uppercase">Local</div></div>
<div style="text-align:center"><div style="font-size:16px;font-weight:700;font-family:monospace;color:#58a6ff">19:32</div><div style="font-size:8px;color:#475569;text-transform:uppercase">UTC</div></div>
</div>
<button style="padding:3px 8px;border-radius:5px;border:none;background:rgba(88,166,255,.08);color:#58a6ff;font-size:9px;cursor:default">+ Timezone</button>
<span style="flex:1"></span><button style="padding:3px 8px;border-radius:5px;border:none;background:rgba(255,255,255,.03);color:#8b949e;font-size:9px;cursor:default">➕ Widget</button>
</div>
<div style="display:grid;grid-template-columns:1fr 1fr 200px;gap:4px;height:100%">
<div class="panel" style="grid-row:span 2"><div class="panel-hdr"><span><span class="grip">⠿</span>Deck A</span><span class="win-btns"><span>─</span><span>□</span><span class="close">✕</span></span></div><div class="panel-body" style="display:flex;flex-direction:column;gap:2px">
<div class="deck"><div class="dh"><span class="l a">DECK A</span><span class="s">Playing</span></div>
<div style="display:flex;gap:6px"><div style="width:48px;height:48px;border-radius:8px;background:linear-gradient(135deg,rgba(88,166,255,.08),rgba(168,85,247,.04));border:1px solid rgba(48,54,61,.1);display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0">🎸</div>
<div style="flex:1"><div style="font-size:12px;font-weight:700">Back in Black</div><div style="font-size:10px;color:#8b949e">AC/DC</div></div></div>
<div style="display:flex;gap:2px;margin:2px 0"><button style="flex:1;height:16px;border-radius:3px;border:none;font-size:8px;background:rgba(255,255,255,.04);color:#8b949e;cursor:default">▶</button><button style="flex:1;height:16px;border-radius:3px;border:none;font-size:8px;background:rgba(255,255,255,.04);color:#8b949e;cursor:default">⏸</button><button style="flex:1;height:16px;border-radius:3px;border:none;font-size:8px;background:rgba(255,255,255,.04);color:#8b949e;cursor:default">⏹</button><button style="flex:1;height:16px;border-radius:3px;border:none;font-size:8px;background:rgba(255,255,255,.04);color:#8b949e;cursor:default">⏮</button><button style="flex:1;height:16px;border-radius:3px;border:none;font-size:8px;background:rgba(255,255,255,.04);color:#8b949e;cursor:default">⏭</button></div>
<div style="height:2px;background:rgba(255,255,255,.04);border-radius:2px"><div style="width:62%;height:100%;background:linear-gradient(90deg,#58a6ff,#79c0ff);border-radius:2px"></div></div>
<div style="display:flex;justify-content:space-between;font-size:8px;color:#64748b"><span>1:52</span><span>-1:08</span></div>
</div></div></div>
<div class="panel" style="grid-row:span 2"><div class="panel-hdr"><span><span class="grip">⠿</span>Deck B</span><span class="win-btns"><span>─</span><span>□</span><span class="close">✕</span></span></div><div class="panel-body" style="display:flex;flex-direction:column;gap:2px">
<div class="deck"><div class="dh"><span class="l b">DECK B</span><span class="s">Cued</span></div>
<div style="display:flex;gap:6px"><div style="width:48px;height:48px;border-radius:8px;background:linear-gradient(135deg,rgba(168,85,247,.08),rgba(88,166,255,.04));border:1px solid rgba(48,54,61,.1);display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0">🎵</div>
<div style="flex:1"><div style="font-size:12px;font-weight:700">Stairway to Heaven</div><div style="font-size:10px;color:#8b949e">Led Zeppelin</div></div></div>
<div style="display:flex;gap:2px;margin:2px 0"><button style="flex:1;height:16px;border-radius:3px;border:none;font-size:8px;background:rgba(255,255,255,.04);color:#8b949e;cursor:default">▶</button><button style="flex:1;height:16px;border-radius:3px;border:none;font-size:8px;background:rgba(255,255,255,.04);color:#8b949e;cursor:default">⏸</button><button style="flex:1;height:16px;border-radius:3px;border:none;font-size:8px;background:rgba(255,255,255,.04);color:#8b949e;cursor:default">⏹</button><button style="flex:1;height:16px;border-radius:3px;border:none;font-size:8px;background:rgba(255,255,255,.04);color:#8b949e;cursor:default">⏮</button><button style="flex:1;height:16px;border-radius:3px;border:none;font-size:8px;background:rgba(255,255,255,.04);color:#8b949e;cursor:default">⏭</button></div>
<div style="height:2px;background:rgba(255,255,255,.04);border-radius:2px"><div style="width:0%;height:100%;background:linear-gradient(90deg,#a855f7,#d8b4fe);border-radius:2px"></div></div>
<div style="display:flex;justify-content:space-between;font-size:8px;color:#64748b"><span>0:00</span><span>-8:02</span></div>
</div></div></div>
<div style="display:flex;flex-direction:column;gap:4px">
<div class="panel" style="flex:1"><div class="panel-hdr"><span><span class="grip">⠿</span>Voice FX</span><span class="win-btns"><span>─</span><span>□</span><span class="close">✕</span></span></div><div class="panel-body" style="padding:4px;display:flex;flex-direction:column;gap:2px;font-size:9px">
<div class="vx-row"><label>Mic</label><select style="flex:1;padding:2px;border-radius:3px;border:1px solid rgba(48,54,61,.15);background:rgba(13,17,23,.4);color:#e6edf3;font-size:8px;outline:none"><option>Microphone</option></select></div>
<div class="vx-row"><label>Gain</label><input type="range" value="80"><span>80%</span></div>
<div class="vx-row"><label>Duck</label><input type="range" value="30"><span>30%</span></div>
<button class="vx-btn">🎤 PTT</button>
</div></div>
<div class="panel" style="flex:0.8"><div class="panel-hdr"><span><span class="grip">⠿</span>Volume</span><span class="win-btns"><span>─</span><span>□</span><span class="close">✕</span></span></div><div class="panel-body" style="padding:4px;display:flex;flex-direction:column;gap:2px;font-size:9px">
<div class="vx-row"><label>Master</label><input type="range" value="75"><span>75%</span></div>
<div class="vx-row"><label>Deck A</label><input type="range" value="80"><span>80%</span></div>
<div class="vx-row"><label>Deck B</label><input type="range" value="70"><span>70%</span></div>
</div></div>
<div class="panel" style="flex:0.8"><div class="panel-hdr"><span><span class="grip">⠿</span>Fade Control</span><span class="win-btns"><span>─</span><span>□</span><span class="close">✕</span></span></div><div class="panel-body" style="padding:4px;display:flex;flex-direction:column;gap:2px;font-size:9px">
<div style="display:flex;align-items:center;gap:4px"><span style="color:#58a6ff">A</span><input type="range" style="flex:1"><span style="color:#a855f7">B</span></div>
<div style="display:flex;justify-content:space-between;color:#475569"><span>Fade: 3s</span><span>Cosine</span></div>
</div></div>
</div>
<div class="panel full" style="flex:1;grid-column:1/-1"><div class="panel-hdr"><span><span class="grip">⠿</span>Playlist</span><span style="display:flex;align-items:center;gap:3px;font-size:8px"><span style="color:#58a6ff">Music</span><span style="color:#8b949e">Content</span><span style="color:#8b949e">Special</span><span style="color:#8b949e">Adds</span><span class="win-btns"><span>─</span><span>□</span><span class="close">✕</span></span></span></div>
<div class="panel-body"><div style="display:flex;gap:4px;height:100%">
<div style="width:120px;border-right:1px solid rgba(48,54,61,.06);padding-right:4px;font-size:9px">
<div style="color:#58a6ff;padding:2px">📁 Music</div>
<div style="color:#8b949e;padding:2px">📁 Content</div>
<div style="color:#8b949e;padding:2px">📁 Special</div>
<div style="color:#8b949e;padding:2px">📁 Adds</div></div>
<div style="flex:1;overflow:auto"><table><thead><tr><th></th><th>Title</th><th>Artist</th><th style="width:30px">Time</th></tr></thead>
<tbody><tr><td>🎵</td><td>Back in Black</td><td>AC/DC</td><td>4:15</td></tr>
<tr><td>🎵</td><td>Highway to Hell</td><td>AC/DC</td><td>3:28</td></tr></tbody></table></div>
<div style="width:180px;display:flex;flex-direction:column;gap:3px">
<div class="panel" style="flex:1"><div class="panel-hdr"><span><span class="grip">⠿</span>Queue</span><span class="win-btns"><span>─</span><span>□</span><span class="close">✕</span></span></div><div class="panel-body" style="padding:2px 4px;font-size:9px;color:#8b949e"><div>▶ Bohemian Rhapsody</div><div>Enter Sandman</div></div></div>
<div class="panel" style="flex:1"><div class="panel-hdr"><span><span class="grip">⠿</span>History</span><span class="win-btns"><span>─</span><span>□</span><span class="close">✕</span></span></div><div class="panel-body" style="padding:2px 4px;font-size:8px;color:#475569"><div>14:32 Back in Black</div><div>14:28 Stairway</div></div></div>
</div></div></div></div>
</div></div>

<!-- PIPELINE TAB -->
<div id="tab-pipeline" class="tab">
<div class="panel" style="height:100%">
<div class="panel-hdr"><span><span class="grip">⠿</span>Audio Pipeline</span><span class="win-btns"><span>─</span><span>□</span><span class="close">✕</span></span></div>
<div class="panel-body" style="display:flex;align-items:center;justify-content:center">
<div class="pipe-area">
<div class="pipe-node act">Deck A</div><div class="pipe-arrow">↓</div>
<div class="pipe-node">Mixer</div><div class="pipe-arrow">↓</div>
<div class="pipe-node">EQ</div><div class="pipe-arrow">↓</div>
<div class="pipe-node">Compressor</div><div class="pipe-arrow">↓</div>
<div class="pipe-node">Limiter</div><div class="pipe-arrow">↓</div>
<div class="pipe-node">Encoder</div><div class="pipe-arrow">↓</div>
<div class="pipe-node act" style="border-color:rgba(63,185,80,.3);color:#3fb950">SHOUTcast v2</div>
</div>
</div>
</div>
</div>

<!-- CROSSFADE TAB -->
<div id="tab-crossfade" class="tab">
<div class="panel" style="height:100%">
<div class="panel-hdr"><span><span class="grip">⠿</span>Crossfade Engine</span><span class="win-btns"><span>─</span><span>□</span><span class="close">✕</span></span></div>
<div class="panel-body" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;padding:20px">
<div><div style="font-size:11px;color:#8b949e;margin-bottom:6px">Fade In</div><input type="range" value="2" style="width:100%"><div style="font-size:10px;color:#64748b">2.0s</div></div>
<div><div style="font-size:11px;color:#8b949e;margin-bottom:6px">Fade Out</div><input type="range" value="3" style="width:100%"><div style="font-size:10px;color:#64748b">3.0s</div></div>
<div><div style="font-size:11px;color:#8b949e;margin-bottom:6px">Curve</div><select style="width:100%;padding:5px;border-radius:5px;border:1px solid rgba(48,54,61,.15);background:rgba(13,17,23,.4);color:#e6edf3;font-size:11px;outline:none"><option>Cosine</option><option>Linear</option><option>Logarithmic</option></select></div>
<div><div style="font-size:11px;color:#8b949e;margin-bottom:6px">Trigger</div><select style="width:100%;padding:5px;border-radius:5px;border:1px solid rgba(48,54,61,.15);background:rgba(13,17,23,.4);color:#e6edf3;font-size:11px;outline:none"><option>Time</option><option>Volume Threshold</option><option>Smart</option></select></div>
<div style="grid-column:span 2"><canvas style="width:100%;height:80px;background:rgba(13,17,23,.3);border-radius:6px"></canvas></div>
</div>
</div>
</div>

<!-- STATION TAB -->
<div id="tab-station" class="tab">
<div class="panel" style="max-width:500px;margin:20px auto">
<div class="panel-hdr"><span><span class="grip">⠿</span>Station Information</span><span class="win-btns"><span>─</span><span>□</span><span class="close">✕</span></span></div>
<div class="panel-body" style="padding:14px">
<div style="text-align:center;margin-bottom:12px"><div style="font-size:22px;font-weight:800;background:linear-gradient(135deg,#e6edf3,#58a6ff);-webkit-background-clip:text;-webkit-text-fill-color:transparent">jttest</div><div style="font-size:11px;color:#8b949e">Planet Hosts Radio</div></div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;font-size:11px">
<div><div style="color:#64748b;font-size:9px;text-transform:uppercase">Description</div><div style="color:#c9d1d9">The best rock station</div></div>
<div><div style="color:#64748b;font-size:9px;text-transform:uppercase">Genres</div><div style="color:#c9d1d9">Rock, Classic Rock</div></div>
<div><div style="color:#64748b;font-size:9px;text-transform:uppercase">Website</div><div style="color:#58a6ff">planet-hosts.com</div></div>
<div><div style="color:#64748b;font-size:9px;text-transform:uppercase">Contact</div><div style="color:#c9d1d9">dj@planet-hosts.com</div></div>
</div>
</div>
</div>
</div>

<!-- LOGIN TAB -->
<div id="tab-login" class="tab">
<div class="login-form">
<div style="text-align:center;margin-bottom:16px"><div style="font-size:28px;font-weight:800;background:linear-gradient(135deg,#e6edf3,#58a6ff);-webkit-background-clip:text;-webkit-text-fill-color:transparent">PLANET HOSTS</div><div style="font-size:12px;color:#64748b">Sign in to your account</div></div>
<div class="fg"><label>Email / DJ Username</label><input placeholder="your@email.com" value="spectre"></div>
<div class="fg"><label>Password</label><input type="password" placeholder="Your password"></div>
<button>Connect to Studio</button>
</div>
</div>

<!-- STATS TAB -->
<div id="tab-stats" class="tab">
<div class="panel" style="height:100%">
<div class="panel-hdr"><span><span class="grip">⠿</span>Stream Statistics</span><span class="win-btns"><span>─</span><span>□</span><span class="close">✕</span></span></div>
<div class="panel-body">
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-bottom:12px">
<div style="background:rgba(13,17,23,.3);border-radius:8px;padding:12px;text-align:center"><div style="font-size:28px;font-weight:700;color:#3fb950">3</div><div style="font-size:9px;color:#475569;text-transform:uppercase">Current</div></div>
<div style="background:rgba(13,17,23,.3);border-radius:8px;padding:12px;text-align:center"><div style="font-size:28px;font-weight:700;color:#d29922">12</div><div style="font-size:9px;color:#475569;text-transform:uppercase">Peak</div></div>
<div style="background:rgba(13,17,23,.3);border-radius:8px;padding:12px;text-align:center"><div style="font-size:28px;font-weight:700;color:#58a6ff">128</div><div style="font-size:9px;color:#475569;text-transform:uppercase">Kbps</div></div>
<div style="background:rgba(13,17,23,.3);border-radius:8px;padding:12px;text-align:center"><div style="font-size:22px;font-weight:700;color:#c9d1d9">1:24</div><div style="font-size:9px;color:#475569;text-transform:uppercase">Uptime</div></div>
</div>
<canvas style="width:100%;height:120px;background:rgba(13,17,23,.3);border-radius:8px"></canvas>
</div>
</div>
</div>

<!-- ENCODERS TAB -->
<div id="tab-encoder" class="tab">
<div class="panel" style="max-width:400px;margin:20px auto">
<div class="panel-hdr"><span><span class="grip">⠿</span>Encoder</span><span class="win-btns"><span>─</span><span>□</span><span class="close">✕</span></span></div>
<div class="panel-body" style="padding:14px">
<div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;font-size:11px">
<div style="color:#64748b">Codec</div><div style="color:#c9d1d9">MP3</div>
<div style="color:#64748b">Bitrate</div><div style="color:#c9d1d9">128 kbps</div>
<div style="color:#64748b">Status</div><div style="color:#3fb950">Connected</div>
<div style="color:#64748b">Server</div><div style="color:#c9d1d9">45.61.59.55:9002</div>
</div>
</div>
</div>
</div>

<!-- SCHEDULER TAB -->
<div id="tab-scheduler" class="tab">
<div class="panel" style="height:100%">
<div class="panel-hdr"><span><span class="grip">⠿</span>Scheduler</span><span class="win-btns"><span>─</span><span>□</span><span class="close">✕</span></span></div>
<div class="panel-body" style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr 1fr 1fr 1fr;gap:4px;padding:10px">
<div style="background:rgba(13,17,23,.3);border-radius:6px;padding:8px;text-align:center;font-size:9px;color:#475569">Sun</div>
<div style="background:rgba(13,17,23,.3);border-radius:6px;padding:8px;text-align:center;font-size:9px;color:#475569">Mon</div>
<div style="background:rgba(13,17,23,.3);border-radius:6px;padding:8px;text-align:center;font-size:9px;color:#475569">Tue</div>
<div style="background:rgba(13,17,23,.3);border-radius:6px;padding:8px;text-align:center;font-size:9px;color:#475569">Wed</div>
<div style="background:rgba(13,17,23,.3);border-radius:6px;padding:8px;text-align:center;font-size:9px;color:#475569">Thu</div>
<div style="background:rgba(13,17,23,.3);border-radius:6px;padding:8px;text-align:center;font-size:9px;color:#58a6ff;border:1px solid rgba(88,166,255,.2)">Fri</div>
<div style="background:rgba(13,17,23,.3);border-radius:6px;padding:8px;text-align:center;font-size:9px;color:#475569">Sat</div>
<div style="grid-column:span 7;text-align:center;padding:20px;color:#64748b;font-size:11px">Friday — Morning Show 8:00-10:00 · Rock Block 10:00-12:00 · Lunch Mix 12:00-14:00</div>
</div>
</div>
</div>

<!-- EVENTS TAB -->
<div id="tab-events" class="tab">
<div class="panel" style="height:100%">
<div class="panel-hdr"><span><span class="grip">⠿</span>Event Log</span><span class="win-btns"><span>─</span><span>□</span><span class="close">✕</span></span></div>
<div class="panel-body" style="padding:6px;font-family:monospace;font-size:10px;color:#8b949e;overflow:auto">
<div style="padding:3px 6px;border-bottom:1px solid rgba(48,54,61,.04)"><span style="color:#475569">14:32:01</span> ▶ Track: Back in Black — AC/DC</div>
<div style="padding:3px 6px;border-bottom:1px solid rgba(48,54,61,.04)"><span style="color:#475569">14:31:02</span> 🔴 Stream started — Live DJ on air</div>
<div style="padding:3px 6px;border-bottom:1px solid rgba(48,54,61,.04)"><span style="color:#475569">14:30:45</span> ✓ Request approved: Free Bird</div>
<div style="padding:3px 6px;border-bottom:1px solid rgba(48,54,61,.04)"><span style="color:#475569">14:30:10</span> ⏹ Track ended: Stairway to Heaven</div>
<div style="padding:3px 6px;border-bottom:1px solid rgba(48,54,61,.04)"><span style="color:#475569">14:28:00</span> 🎤 Mic activated</div>
<div style="padding:3px 6px;border-bottom:1px solid rgba(48,54,61,.04)"><span style="color:#475569">14:27:30</span> 🔔 AUX: Jingle played</div>
</div>
</div>
</div>

<!-- REQUESTS TAB -->
<div id="tab-requests" class="tab">
<div class="panel" style="max-width:500px;margin:20px auto">
<div class="panel-hdr"><span><span class="grip">⠿</span>Requests</span><span style="display:flex;align-items:center;gap:4px"><span style="font-size:10px;color:#58a6ff">2 pending</span><span class="win-btns"><span>─</span><span>□</span><span class="close">✕</span></span></span></div>
<div class="panel-body" style="padding:8px">
<div style="display:flex;align-items:center;gap:6px;padding:6px;border-bottom:1px solid rgba(48,54,61,.06)">
<span style="flex:1;font-size:11px">Free Bird — Lynyrd Skynyrd</span>
<button style="padding:3px 8px;border-radius:4px;border:none;background:rgba(63,185,80,.1);color:#3fb950;font-size:10px;cursor:default">✓</button>
<button style="padding:3px 8px;border-radius:4px;border:none;background:rgba(248,81,73,.1);color:#f85149;font-size:10px;cursor:default">✕</button>
</div>
<div style="display:flex;align-items:center;gap:6px;padding:6px;border-bottom:1px solid rgba(48,54,61,.06)">
<span style="flex:1;font-size:11px">Sweet Child O Mine — Guns N Roses</span>
<button style="padding:3px 8px;border-radius:4px;border:none;background:rgba(63,185,80,.1);color:#3fb950;font-size:10px;cursor:default">✓</button>
<button style="padding:3px 8px;border-radius:4px;border:none;background:rgba(248,81,73,.1);color:#f85149;font-size:10px;cursor:default">✕</button>
</div>
</div>
</div>
</div>

<!-- AI TAB -->
<div id="tab-ai" class="tab">
<div class="panel" style="max-width:500px;margin:40px auto;text-align:center">
<div class="panel-hdr"><span><span class="grip">⠿</span>AI Assistant</span><span class="win-btns"><span>─</span><span>□</span><span class="close">✕</span></span></div>
<div class="panel-body" style="padding:24px">
<div style="font-size:40px;margin-bottom:12px">🤖</div>
<div style="font-size:16px;font-weight:700;margin-bottom:6px">Planet Hosts AI</div>
<div style="font-size:11px;color:#64748b;margin-bottom:16px">Playlist suggestions · Metadata cleanup · Show generation · Social media posts</div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:6px">
<button style="padding:8px;border-radius:6px;border:none;background:rgba(88,166,255,.08);color:#58a6ff;font-size:11px;cursor:default">Suggest Playlist</button>
<button style="padding:8px;border-radius:6px;border:none;background:rgba(168,85,247,.08);color:#d8b4fe;font-size:11px;cursor:default">Clean Metadata</button>
<button style="padding:8px;border-radius:6px;border:none;background:rgba(63,185,80,.08);color:#3fb950;font-size:11px;cursor:default">Generate Show</button>
<button style="padding:8px;border-radius:6px;border:none;background:rgba(248,81,73,.08);color:#f85149;font-size:11px;cursor:default">Social Post</button>
</div>
</div>
</div>
</div>

<script>
function switchTab(name){
 document.querySelectorAll('.tab').forEach(t=>t.classList.remove('act'));
 document.querySelectorAll('#tabs button').forEach(b=>b.classList.remove('act'));
 document.getElementById('tab-'+name).classList.add('act');
 event.target.classList.add('act');
}
function toggleMute(){
 var b=document.getElementById('muteBtn');
 if(b.textContent=='🔊'){b.textContent='🔇';b.style.color='#f85149'}
 else{b.textContent='🔊';b.style.color=''}
}
</script>
</body>
</html>