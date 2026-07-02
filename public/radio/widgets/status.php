<?php
require_once __DIR__ . '/../radio_helper.php';
header('Content-Type: text/html; charset=utf-8');
$streamId = (int)($_GET['stream'] ?? 0);
$layout = $_GET['layout'] ?? 'js';
if (!$streamId) exit;

$stream = radio_get_stream($streamId);
if (!$stream) exit;

$stats = radio_fetch_stats($stream);
$online = $stats['status'];
$bitrate = $stats['bitrate'];
$uptime = $stats['uptime'] ?: 'N/A';

if ($layout === 'iframe'): ?><div style="font-family:Inter;color:#fff;font-size:12px;text-align:center;padding:10px">
<div style="font-size:14px;font-weight:700;color:<?php echo $online ? '#4ade80' : '#f87171'; ?>">● <?php echo $online ? 'ONLINE' : 'OFFLINE'; ?></div>
<div style="margin-top:4px;color:#94a3b8"><?php echo $bitrate; ?>kbps · Uptime: <?php echo htmlspecialchars($uptime); ?></div>
</div><?php exit; endif; ?>
document.write('<div style="font-family:Inter;color:#e0e0e0;font-size:12px;text-align:center;padding:10px"><div style="font-size:14px;font-weight:700;color:<?php echo $online ? "#4ade80" : "#f87171"; ?>">● <?php echo $online ? "ONLINE" : "OFFLINE"; ?></div><div style="margin-top:4px;color:#94a3b8"><?php echo $bitrate; ?>kbps · Uptime: <?php echo addslashes($uptime); ?></div></div>');
