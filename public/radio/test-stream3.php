<?php require_once __DIR__ . '/radio_helper.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Stream #3 Test — Planet Hosts</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:Inter,sans-serif;background:#02050e;color:#e0e0e0;padding:30px}
h1{font-size:22px;margin-bottom:6px}
.desc{color:#64748b;font-size:13px;margin-bottom:20px}
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:20px}
.card{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:14px;padding:16px}
.card h2{font-size:13px;color:#008cff;margin-bottom:10px;text-transform:uppercase;letter-spacing:1px}
.card iframe{width:100%;border-radius:8px;border:none}
audio{width:100%;margin-top:10px}
.links{margin-top:20px;display:flex;gap:10px;flex-wrap:wrap}
.links a{padding:8px 16px;border-radius:8px;background:rgba(0,140,255,.1);border:1px solid rgba(0,140,255,.2);color:#008cff;text-decoration:none;font-size:13px}
.links a:hover{background:rgba(0,140,255,.2)}
</style>
</head>
<body>

<h1>Stream #3 — jttest (SHOUTcast)</h1>
<p class="desc">Port 11000 · Live preview of all widget types</p>

<div class="grid">

<div class="card">
<h2>▶️ Mini Player (iframe)</h2>
<iframe src="widgets/player.php?stream=3&layout=iframe" height="200"></iframe>
</div>

<div class="card">
<h2>📻 Now Playing (iframe)</h2>
<iframe src="widgets/nowplaying.php?stream=3&layout=iframe" height="140"></iframe>
</div>

<div class="card">
<h2>🔵 Status</h2>
<iframe src="widgets/status.php?stream=3&layout=iframe" height="60"></iframe>
</div>

<div class="card">
<h2>👥 Listeners</h2>
<iframe src="widgets/listeners.php?stream=3&layout=iframe" height="60"></iframe>
</div>

<div class="card">
<h2>📊 Stats</h2>
<iframe src="widgets/stats.php?stream=3&layout=iframe" height="120"></iframe>
</div>

<div class="card">
<h2>📋 Song History</h2>
<iframe src="widgets/songhistory.php?stream=3&layout=iframe" height="160"></iframe>
</div>

</div>

<div class="links">
<a href="embed.php?stream=3" target="_blank">🎵 Full Player Page</a>
<a href="index.php" target="_blank">📡 Radio Portal</a>
<a href="widgets/player.php?stream=3" target="_blank">JS Player Embed</a>
</div>

</body>
</html>
