<?php
require_once __DIR__ . '/radio_helper.php';
$streamId = (int)($_GET['stream'] ?? 0);
if (!$streamId) $streamId = (int)($_GET['id'] ?? 0);
if (!$streamId) { echo '<!DOCTYPE html><html><body style="background:#0a0e1a;color:#e0e0e0;font-family:sans-serif;padding:40px;text-align:center"><h1>Station Status</h1><p>No stream specified. Use ?stream=ID</p></body></html>'; exit; }
$stream = radio_get_stream($streamId);
if (!$stream) { echo '<!DOCTYPE html><html><body style="background:#0a0e1a;color:#e0e0e0;font-family:sans-serif;padding:40px;text-align:center"><h1>Station Not Found</h1></body></html>'; exit; }
$stats = radio_fetch_stats($stream);
$name = htmlspecialchars($stream->server_name ?: 'Radio');
$online = $stats['status'];
$song = htmlspecialchars($stats['song'] ?: 'Not Playing');
$artist = htmlspecialchars($stats['artist']);
$listeners = $stats['listeners'];
$peak = $stats['peak'];
$bitrate = $stats['bitrate'];
$uptime = $stats['uptime'];
$sUrl = radio_stream_url($stream);
$sSlUrl = radio_ssl_stream_url($streamId);
$engine = strtoupper($stream->server_type ?? 'ICECAST');
$port = $stream->port ?? 8000;
$mount = $stream->mount_point ?? '/live';
if (!str_starts_with($mount, '/')) $mount = "/{$mount}";
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?php echo $name; ?> — Station Status</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:Inter,system-ui,sans-serif;background:#0a0e1a;color:#e0e0e0;min-height:100vh}
.topbar{background:linear-gradient(135deg,rgba(0,140,255,.1),rgba(168,85,247,.05));border-bottom:1px solid rgba(0,191,255,.1);padding:16px 24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px}
.topbar .logo{font-size:18px;font-weight:800;letter-spacing:-.5px}.topbar .logo span{color:#008cff}
.topbar .links{display:flex;gap:10px}
.topbar .links a{padding:6px 14px;border-radius:6px;text-decoration:none;font-size:12px;font-weight:600;transition:.15s}
.topbar .links .btn-player{background:rgba(0,140,255,.2);color:#0A84FF}
.topbar .links .btn-player:hover{background:rgba(0,140,255,.3)}
.topbar .links .btn-stream{background:rgba(255,255,255,.06);color:#94a3b8}
.topbar .links .btn-stream:hover{background:rgba(255,255,255,.1)}
.container{max-width:800px;margin:0 auto;padding:24px}
.hero{text-align:center;padding:32px 0 24px}
.hero h1{font-size:28px;font-weight:800;margin-bottom:4px}
.hero .sub{color:#64748b;font-size:13px}
.hero .status-badge{display:inline-block;padding:4px 16px;border-radius:20px;font-size:12px;font-weight:600;margin-top:8px}
.hero .status-badge.online{background:rgba(74,222,128,.12);color:#4ade80}
.hero .status-badge.offline{background:rgba(248,113,113,.12);color:#f87171}
.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-bottom:24px}
.card{background:rgba(8,16,28,.6);border:1px solid rgba(255,255,255,.04);border-radius:12px;padding:20px;text-align:center}
.card .num{font-size:24px;font-weight:700;color:#38bdf8;display:block}
.card .lbl{font-size:10px;text-transform:uppercase;color:#64748b;letter-spacing:1px;margin-top:4px;font-weight:600}
.nowplaying{background:linear-gradient(135deg,rgba(0,140,255,.06),rgba(168,85,247,.03));border:1px solid rgba(0,191,255,.08);border-radius:16px;padding:28px;text-align:center;margin-bottom:24px}
.nowplaying .icon{font-size:48px;margin-bottom:8px}
.nowplaying .title{font-size:20px;font-weight:700;margin-bottom:2px}
.nowplaying .artist{font-size:14px;color:#94a3b8;margin-bottom:12px}
.player-bar{display:flex;gap:8px;justify-content:center;flex-wrap:wrap}
.player-bar a{padding:10px 24px;border-radius:10px;text-decoration:none;font-weight:600;font-size:13px;transition:.15s}
.player-bar .listen{background:linear-gradient(135deg,#008cff,#0066cc);color:#fff}
.player-bar .listen:hover{box-shadow:0 4px 15px rgba(0,140,255,.3)}
.player-bar .direct{background:rgba(255,255,255,.06);color:#94a3b8;border:1px solid rgba(255,255,255,.08)}
.player-bar .direct:hover{background:rgba(255,255,255,.1)}
.details{background:rgba(8,16,28,.6);border:1px solid rgba(255,255,255,.04);border-radius:12px;padding:20px;margin-bottom:24px}
.details h3{font-size:14px;margin-bottom:12px;color:#e0e0e0}
.details table{width:100%;border-collapse:collapse;font-size:12px}
.details td{padding:8px 0;border-bottom:1px solid rgba(255,255,255,.04)}
.details td:first-child{color:#64748b;width:140px}
.details td:last-child{color:#e0e0e0}
.details tr:last-child td{border-bottom:none}
.footer{text-align:center;padding:20px;color:#64748b;font-size:11px;border-top:1px solid rgba(255,255,255,.04)}
.footer a{color:#008cff;text-decoration:none}
audio{width:100%;height:40px;border-radius:8px;margin-top:12px}
@media(max-width:600px){.grid{grid-template-columns:1fr 1fr}.hero h1{font-size:22px}}
</style></head><body>
<div class="topbar">
<div class="logo">PLANET <span>HOSTS</span></div>
<div class="links">
<a href="https://planet-hosts.com:2083/radio/embed.php?stream=<?php echo $streamId; ?>" class="btn-player" target="_blank">🎵 Player</a>
<a href="<?php echo $sUrl; ?>" class="btn-stream" target="_blank">🔗 Direct</a>
</div>
</div>
<div class="container">
<div class="hero">
<h1><?php echo $name; ?></h1>
<div class="sub"><?php echo $engine; ?> · Port <?php echo $port; ?><?php if (!radio_is_shoutcast($stream)): echo ' · ' . $mount; endif; ?></div>
<span class="status-badge <?php echo $online ? 'online' : 'offline'; ?>">● <?php echo $online ? 'LIVE' : 'OFFLINE'; ?></span>
</div>
<div class="nowplaying">
<div class="icon"><?php echo $online ? '🎵' : '🔇'; ?></div>
<div class="title"><?php echo $song; ?></div>
<?php if ($artist): ?><div class="artist"><?php echo $artist; ?></div><?php endif; ?>
<div class="player-bar">
<a href="<?php echo $sSlUrl; ?>" class="listen" target="_blank">▶ Listen Live</a>
<a href="<?php echo $sUrl; ?>" class="direct" target="_blank">Direct Stream</a>
</div>
<audio src="<?php echo $sSlUrl; ?>" preload="none" controls></audio>
</div>
<div class="grid">
<div class="card"><span class="num"><?php echo $listeners; ?></span><span class="lbl">Listeners</span></div>
<div class="card"><span class="num"><?php echo $peak; ?></span><span class="lbl">Peak</span></div>
<div class="card"><span class="num"><?php echo $bitrate; ?></span><span class="lbl">Kbps</span></div>
<div class="card"><span class="num"><?php echo $uptime ?: '-'; ?></span><span class="lbl">Uptime</span></div>
</div>
<div class="details">
<h3>Station Details</h3>
<table>
<tr><td>Server Type</td><td><?php echo $engine; ?></td></tr>
<tr><td>Port</td><td><?php echo $port; ?></td></tr>
<?php if (!radio_is_shoutcast($stream)): ?><tr><td>Mount Point</td><td><?php echo htmlspecialchars($mount); ?></td></tr><?php endif; ?>
<tr><td>Bitrate</td><td><?php echo $bitrate; ?> kbps</td></tr>
<tr><td>Format</td><td>MP3</td></tr>
<tr><td>Status</td><td style="color:<?php echo $online ? '#4ade80' : '#f87171'; ?>"><?php echo $online ? 'Online' : 'Offline'; ?></td></tr>
<tr><td>Current Song</td><td><?php echo $song; ?></td></tr>
</table>
</div>
</div>
<div class="footer">Powered by <a href="https://planet-hosts.com">Planet Hosts</a> · <a href="https://planet-hosts.com:2083/user/radio">Dashboard</a></div>
</body></html>