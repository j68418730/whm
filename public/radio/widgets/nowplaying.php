<?php
header('Content-Type: text/html; charset=utf-8');
$streamId = (int)($_GET['stream'] ?? 0);
$layout = $_GET['layout'] ?? 'js';
if (!$streamId) { echo '<!-- no stream -->'; exit; }
try {
    $pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
    $s = $pdo->prepare("SELECT * FROM radio_streams WHERE id = ?");
    $s->execute([$streamId]);
    $stream = $s->fetch(PDO::FETCH_OBJ);
} catch (Exception $e) { $stream = null; }
if (!$stream) { echo '<!-- stream not found -->'; exit; }
$name = htmlspecialchars($stream->server_name ?? 'Radio');
$song = htmlspecialchars($stream->current_song ?? 'No song data');
$artist = htmlspecialchars($stream->current_artist ?? '');
$listeners = (int)($stream->listener_count ?? 0);
$bitrate = (int)($stream->bitrate ?? 128);
$status = $stream->status === 'running' ? 'Online' : 'Offline';
$color = $stream->status === 'running' ? '#4ade80' : '#f87171';

if ($layout === 'iframe'): ?>
<!DOCTYPE html><html><head><style>body{margin:0;background:transparent;font-family:Inter,sans-serif;color:#fff;font-size:13px}</style></head><body>
<div style="text-align:center;padding:10px"><div style="font-size:24px;margin-bottom:4px"><?php echo $name; ?></div>
<div style="color:#94a3b8;font-size:12px"><?php echo $song; ?></div>
<div style="margin-top:6px;display:flex;justify-content:center;gap:12px;font-size:11px">
<span><span style="color:#38bdf8">&#9679;</span> <?php echo $listeners; ?> listeners</span>
<span><?php echo $bitrate; ?>kbps</span>
<span style="color:<?php echo $color; ?>"><?php echo $status; ?></span>
</div></div></body></html>
<?php exit; endif; ?>
document.getElementById('ph-nowplaying').innerHTML='<div style="background:linear-gradient(135deg,rgba(0,140,255,.08),transparent);border-radius:10px;padding:14px;font-family:Inter,sans-serif;color:#e0e0e0;max-width:320px"><div style="font-size:13px;font-weight:700;color:#008cff"><?php echo $name; ?></div><div style="font-size:15px;font-weight:600;margin:4px 0"><?php echo $song; ?></div><div style="font-size:12px;color:#94a3b8"><?php echo $artist; ?></div><div style="margin-top:6px;display:flex;gap:10px;font-size:11px"><span style="color:#38bdf8">&#9679; <?php echo $listeners; ?> listeners</span><span><?php echo $bitrate; ?>kbps</span><span style="color:<?php echo $color; ?>"><?php echo $status; ?></span></div></div>';
