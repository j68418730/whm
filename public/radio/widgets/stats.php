<?php
require_once __DIR__ . '/../radio_helper.php';
header('Content-Type: text/html; charset=utf-8');
$streamId = (int)($_GET['stream'] ?? 0);
$layout = $_GET['layout'] ?? 'js';
if (!$streamId) exit;

$stream = radio_get_stream($streamId);
if (!$stream) exit;

$stats = radio_fetch_stats($stream);
$listeners = $stats['listeners'];
$peak = $stats['peak'];
$bitrate = $stats['bitrate'];
$online = $stats['status'];

try {
    $pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
    $q = $pdo->prepare("SELECT COUNT(*) as c FROM radio_song_history WHERE stream_id = ?");
    $q->execute([$streamId]);
    $totalSongs = $q->fetchColumn();
} catch (Exception $e) { $totalSongs = 0; }

$uptime = $stats['uptime'] ?: 'N/A';
$bandwidth = $stream->bandwidth_used ?? 0;

if ($layout === 'iframe'): ?><div style="font-family:Inter;color:#fff;font-size:12px;padding:10px">
<div style="display:grid;grid-template-columns:1fr 1fr;gap:6px">
<div><span style="color:#64748b">Listeners:</span> <?php echo $listeners; ?></div><div><span style="color:#64748b">Peak:</span> <?php echo $peak; ?></div>
<div><span style="color:#64748b">Bitrate:</span> <?php echo $bitrate; ?>k</div><div><span style="color:#64748b">Songs:</span> <?php echo $totalSongs; ?></div>
<div><span style="color:#64748b">Status:</span> <span style="color:<?php echo $online ? '#4ade80' : '#f87171'; ?>"><?php echo $online ? 'Online' : 'Offline'; ?></span></div>
<div><span style="color:#64748b">BW:</span> <?php echo $bandwidth > 1048576 ? round($bandwidth/1073741824,2).' GB' : round($bandwidth/1048576,2).' MB'; ?></div>
</div></div><?php exit; endif; ?>
document.write('<div style="font-family:Inter;color:#e0e0e0;font-size:12px;padding:10px"><div style="display:grid;grid-template-columns:1fr 1fr;gap:6px"><div><span style="color:#64748b">Listeners:</span> <?php echo $listeners; ?></div><div><span style="color:#64748b">Peak:</span> <?php echo $peak; ?></div><div><span style="color:#64748b">Bitrate:</span> <?php echo $bitrate; ?>k</div><div><span style="color:#64748b">Songs:</span> <?php echo $totalSongs; ?></div><div style="grid-column:1/-1"><span style="color:#64748b">Uptime:</span> <?php echo addslashes($uptime); ?></div></div></div>');
