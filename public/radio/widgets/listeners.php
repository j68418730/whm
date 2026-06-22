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
$listeners = (int)($stream->listener_count ?? 0);
$peak = (int)($stream->peak_listeners ?? 0);
if ($layout === 'iframe'): ?><div style="text-align:center;padding:10px;font-family:Inter;color:#fff"><div style="font-size:28px;font-weight:800;color:#38bdf8"><?php echo $listeners; ?></div><div style="font-size:11px;color:#94a3b8">Current Listeners</div><div style="margin-top:6px;font-size:11px;color:#64748b">Peak: <?php echo $peak; ?></div></div><?php exit; endif; ?>
document.write('<div style="text-align:center;padding:10px;font-family:Inter;color:#e0e0e0"><div style="font-size:28px;font-weight:800;color:#38bdf8"><?php echo $listeners; ?></div><div style="font-size:11px;color:#94a3b8">Current Listeners</div><div style="margin-top:6px;font-size:11px;color:#64748b">Peak: <?php echo $peak; ?></div></div>');
