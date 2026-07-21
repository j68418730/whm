<?php
require_once __DIR__ . '/../radio_helper.php';
header('Content-Type: text/html; charset=utf-8');
$streamId = (int)($_GET['stream'] ?? 0);
if (!$streamId) exit;
$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4','radiouser','Skylinehosting171');
$sched = $pdo->prepare("SELECT s.*, d.username AS dj_name, d.name AS dj_display FROM radio_dj_schedule s LEFT JOIN radio_djs d ON d.id=s.dj_id WHERE s.stream_id=? ORDER BY s.scheduled_date, s.time_slot LIMIT 20");
$sched->execute([$streamId]);
$rows = $sched->fetchAll(PDO::FETCH_OBJ);
?>
<div style="font-family:Inter,sans-serif;font-size:12px;color:#e0e0e0">
<?php if (empty($rows)): ?>
<div style="text-align:center;padding:10px;color:#64748b;font-size:12px">No upcoming shows</div>
<?php else: ?>
<table style="width:100%;border-collapse:collapse">
<tr style="border-bottom:1px solid rgba(255,255,255,.06)"><th style="padding:6px 4px;text-align:left;color:#64748b;font-size:10px">Date</th><th style="padding:6px 4px;text-align:left;color:#64748b;font-size:10px">Time</th><th style="padding:6px 4px;text-align:left;color:#64748b;font-size:10px">DJ</th></tr>
<?php foreach ($rows as $r): ?>
<tr><td style="padding:5px 4px;border-bottom:1px solid rgba(255,255,255,.03)"><?=htmlspecialchars($r->scheduled_date)?></td>
<td style="padding:5px 4px;border-bottom:1px solid rgba(255,255,255,.03)"><?=htmlspecialchars($r->time_slot)?></td>
<td style="padding:5px 4px;border-bottom:1px solid rgba(255,255,255,.03)"><?=htmlspecialchars($r->dj_display?:$r->dj_name?:'DJ')?></td></tr>
<?php endforeach; ?>
</table>
<?php endif; ?>
</div>
