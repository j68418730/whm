<?php
require_once __DIR__ . '/radio_helper.php';

$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
$streams = $pdo->query("SELECT id, name AS server_name, server_type, port, status, mount_point FROM streaming_stations ORDER BY id ASC")->fetchAll(PDO::FETCH_OBJ);
$baseUrl = radio_host();

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Radio - Planet Hosts</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:Inter,sans-serif;background:#02050e;color:#e0e0e0;min-height:100vh}
.header{background:rgba(8,16,28,.95);border-bottom:1px solid rgba(0,191,255,.08);padding:16px 24px;display:flex;justify-content:space-between;align-items:center}
.header .logo{font-size:18px;font-weight:800;color:#e0e0e0}
.header .logo span{color:#008cff}
.header .sub{font-size:12px;color:#64748b}
.container{max-width:1200px;margin:0 auto;padding:24px}
.stream-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:16px;margin-top:16px}
.stream-card{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:14px;padding:20px;transition:.25s;position:relative;overflow:hidden}
.stream-card:hover{border-color:rgba(0,191,255,.2);box-shadow:0 8px 32px rgba(0,0,0,.3)}
.stream-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px}
.stream-card.online::before{background:linear-gradient(90deg,#22c55e,#4ade80)}
.stream-card.offline::before{background:linear-gradient(90deg,#ef4444,#f87171)}
.stream-top{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px}
.stream-name{font-size:16px;font-weight:700;color:#f1f5f9}
.stream-type{font-size:11px;color:#64748b;font-family:monospace}
.stream-badge{display:inline-flex;align-items:center;gap:6px;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600}
.online .stream-badge{background:rgba(34,197,94,.12);color:#4ade80}
.offline .stream-badge{background:rgba(239,68,68,.12);color:#f87171}
.stream-status-dot{width:8px;height:8px;border-radius:50%}
.online .stream-status-dot{background:#4ade80;box-shadow:0 0 8px rgba(34,197,94,.5);animation:pulse 2s infinite}
.offline .stream-status-dot{background:#f87171}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.5}}
.stream-stats{display:flex;gap:16px;font-size:12px;color:#94a3b8;margin-bottom:12px}
.player-section{margin-top:8px}
audio{width:100%;border-radius:8px}
.widget-links{display:flex;flex-wrap:wrap;gap:6px;margin-top:12px}
.widget-link{font-size:11px;color:#008cff;text-decoration:none;padding:4px 10px;border-radius:6px;background:rgba(0,140,255,.06);border:1px solid rgba(0,140,255,.12);transition:.2s}
.widget-link:hover{background:rgba(0,140,255,.12)}
h2{font-size:20px;font-weight:700;color:#f1f5f9}
p.desc{color:#64748b;font-size:13px;margin-top:4px}
</style>
</head><body>
<div class="header">
<div><span class="logo">PLANET <span>HOSTS</span></span><div class="sub">Radio Streaming</div></div>
<div style="font-size:12px;color:#64748b"><?php echo count($streams); ?> stream<?php echo count($streams) !== 1 ? 's' : ''; ?></div>
</div>
<div class="container">
<h2>📻 Streams</h2>
<p class="desc">Select a stream to listen or grab embed codes for your website.</p>

<div class="stream-grid">
<?php foreach ($streams as $s):
    $stats = radio_fetch_stats($s);
    $name = htmlspecialchars($s->server_name ?: "Stream #{$s->id}");
    $type = radio_server_type($s);
    $online = $stats['status'];
    $cls = $online ? 'online' : 'offline';
    $sUrl = radio_stream_url($s);
    $listeners = $stats['listeners'];
    $bitrate = $stats['bitrate'];
    $song = htmlspecialchars($stats['song'] ?: 'No data');
?>
<div class="stream-card <?php echo $cls; ?>">
<div class="stream-top">
<div><div class="stream-name"><?php echo $name; ?></div><div class="stream-type"><?php echo $type; ?> · port <?php echo $s->port; ?></div></div>
<span class="stream-badge"><span class="stream-status-dot"></span><?php echo $online ? 'LIVE' : 'OFFLINE'; ?></span>
</div>
<div class="stream-stats">
<span>👥 <?php echo $listeners; ?> listeners</span>
<span>📊 <?php echo $bitrate; ?>kbps</span>
</div>
<div class="stream-stats" style="font-size:11px;color:#64748b;margin-bottom:8px">
<?php echo $song; ?>
</div>
<div class="player-section">
<audio src="<?php echo $sUrl; ?>" controls preload="none" style="width:100%;height:40px"></audio>
</div>
<div class="widget-links">
<a class="widget-link" href="<?php echo $baseUrl; ?>/radio/embed.php?stream=<?php echo $s->id; ?>" target="_blank">🎵 Player</a>
<a class="widget-link" href="<?php echo $baseUrl; ?>/radio/widgets/nowplaying.php?stream=<?php echo $s->id; ?>&layout=iframe" target="_blank">📻 Now Playing</a>
<a class="widget-link" href="<?php echo $baseUrl; ?>/radio/widgets/player.php?stream=<?php echo $s->id; ?>" target="_blank">▶️ Mini Player</a>
<a class="widget-link" href="<?php echo $baseUrl; ?>/radio/widgets/status.php?stream=<?php echo $s->id; ?>&layout=iframe" target="_blank">🔵 Status</a>
<a class="widget-link" href="<?php echo $baseUrl; ?>/radio/widgets/listeners.php?stream=<?php echo $s->id; ?>&layout=iframe" target="_blank">👥 Listeners</a>
<a class="widget-link" href="<?php echo $baseUrl; ?>/radio/widgets/songhistory.php?stream=<?php echo $s->id; ?>&layout=iframe" target="_blank">📋 History</a>
<a class="widget-link" href="<?php echo $baseUrl; ?>/radio/widgets/stats.php?stream=<?php echo $s->id; ?>&layout=iframe" target="_blank">📊 Stats</a>
</div>
</div>
<?php endforeach; ?>
</div>
</div>
</body></html>
