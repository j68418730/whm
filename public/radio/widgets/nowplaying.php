<?php
require_once __DIR__ . '/../radio_helper.php';
header('Content-Type: text/html; charset=utf-8');
$streamId = (int)($_GET['stream'] ?? 0);
$layout = $_GET['layout'] ?? 'js';
if (!$streamId) { echo '<!-- no stream -->'; exit; }

$stream = radio_get_stream($streamId);
if (!$stream) { echo '<!-- stream not found -->'; exit; }

$stats = radio_fetch_stats($stream);
$name = htmlspecialchars($stream->server_name ?: 'Radio');
$song = htmlspecialchars($stats['song'] ?: 'No song data');
$artist = htmlspecialchars($stats['artist']);
$listeners = $stats['listeners'];
$bitrate = $stats['bitrate'];
$online = $stats['status'];
$color = $online ? '#4ade80' : '#f87171';
$type = radio_server_type($stream);

if ($layout === 'iframe'): ?>
<!DOCTYPE html><html><head><style>body{margin:0;background:transparent;font-family:Inter,sans-serif;color:#fff;font-size:13px}</style></head><body>
<div style="text-align:center;padding:10px">
<div style="font-size:24px;margin-bottom:4px"><?php echo $name; ?></div>
<div style="color:#94a3b8;font-size:12px"><?php if ($artist): ?><?php echo $artist; ?> - <?php endif; ?><?php echo $song; ?></div>
<div style="margin-top:6px;display:flex;justify-content:center;gap:12px;font-size:11px">
<span><span style="color:#38bdf8">●</span> <?php echo $listeners; ?> listeners</span>
<span><?php echo $bitrate; ?>kbps</span>
<span style="color:<?php echo $color; ?>"><?php echo $online ? 'Online' : 'Offline'; ?></span>
</div></div></body></html>
<?php exit; endif; ?>
document.getElementById('ph-nowplaying').innerHTML='<div style="background:linear-gradient(135deg,rgba(0,140,255,.08),transparent);border-radius:10px;padding:14px;font-family:Inter,sans-serif;color:#e0e0e0;max-width:320px"><div style="font-size:13px;font-weight:700;color:#008cff"><?php echo $name; ?></div><div style="font-size:15px;font-weight:600;margin:4px 0"><?php if ($artist): ?><?php echo $artist; ?> - <?php endif; ?><?php echo $song; ?></div><div style="margin-top:6px;display:flex;gap:10px;font-size:11px"><span style="color:#38bdf8">● <?php echo $listeners; ?> listeners</span><span><?php echo $bitrate; ?>kbps</span><span style="color:<?php echo $color; ?>"><?php echo $online ? 'Online' : 'Offline'; ?></span></div></div>';
