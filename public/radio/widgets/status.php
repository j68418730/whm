<?php
header('Content-Type: text/html; charset=utf-8');
$streamId = (int)($_GET['stream'] ?? 0);
$layout = $_GET['layout'] ?? 'js';
if (!$streamId) exit;
try {
    $pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
    $s = $pdo->prepare("SELECT * FROM radio_streams WHERE id = ?");
    $s->execute([$streamId]);
    $stream = $s->fetch(PDO::FETCH_OBJ);
} catch (Exception $e) { $stream = null; }
if (!$stream) exit;
$status = $stream->status === 'running';
$uptime = $stream->uptime ?? 'N/A';
$bitrate = (int)($stream->bitrate ?? 128);
if ($layout === 'iframe'): ?><div style="font-family:Inter;color:#fff;font-size:12px;text-align:center;padding:10px">
<div style="font-size:14px;font-weight:700;color:<?php echo $status ? '#4ade80' : '#f87171'; ?>">● <?php echo $status ? 'ONLINE' : 'OFFLINE'; ?></div>
<div style="margin-top:4px;color:#94a3b8"><?php echo $bitrate; ?>kbps &middot; Uptime: <?php echo $uptime; ?></div>
</div><?php exit; endif; ?>
document.write('<div style="font-family:Inter;color:#e0e0e0;font-size:12px;text-align:center;padding:10px"><div style="font-size:14px;font-weight:700;color:<?php echo $status ? "#4ade80" : "#f87171"; ?>">● <?php echo $status ? "ONLINE" : "OFFLINE"; ?></div><div style="margin-top:4px;color:#94a3b8"><?php echo $bitrate; ?>kbps &middot; Uptime: <?php echo addslashes($uptime); ?></div></div>');
