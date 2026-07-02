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

if ($layout === 'iframe'): ?><div style="text-align:center;padding:10px;font-family:Inter;color:#fff"><div style="font-size:28px;font-weight:800;color:#38bdf8"><?php echo $listeners; ?></div><div style="font-size:11px;color:#94a3b8">Current Listeners</div><div style="margin-top:6px;font-size:11px;color:#64748b">Peak: <?php echo $peak; ?></div></div><?php exit; endif; ?>
document.write('<div style="text-align:center;padding:10px;font-family:Inter;color:#e0e0e0"><div style="font-size:28px;font-weight:800;color:#38bdf8"><?php echo $listeners; ?></div><div style="font-size:11px;color:#94a3b8">Current Listeners</div><div style="margin-top:6px;font-size:11px;color:#64748b">Peak: <?php echo $peak; ?></div></div>');
