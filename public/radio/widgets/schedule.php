<?php
require_once __DIR__ . '/../radio_helper.php';
header('Content-Type: text/html; charset=utf-8');
$streamId = (int)($_GET['stream'] ?? 0);
if (!$streamId) exit;
$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4','radiouser','Skylinehosting171');
$days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
$sched = $pdo->prepare("SELECT s.*, d.username AS dj_name FROM radio_dj_schedule s LEFT JOIN radio_djs d ON d.id=s.dj_id WHERE s.stream_id=? AND s.is_active=1 ORDER BY s.day_of_week, s.start_time");
$sched->execute([$streamId]);
$rows = $sched->fetchAll(PDO::FETCH_OBJ);
?>
<div style="font-family:Inter,sans-serif;font-size:12px;color:#e0e0e0">
<table style="width:100%;border-collapse:collapse">
<tr style="border-bottom:1px solid rgba(255,255,255,.06)"><th style="padding:6px 4px;text-align:left;color:#64748b;font-size:10px">Day</th><th style="padding:6px 4px;text-align:left;color:#64748b;font-size:10px">Show</th><th style="padding:6px 4px;text-align:left;color:#64748b;font-size:10px">Time</th><th style="padding:6px 4px;text-align:left;color:#64748b;font-size:10px">DJ</th></tr>
<?php foreach ($rows as $r): ?>
<tr><td style="padding:5px 4px;border-bottom:1px solid rgba(255,255,255,.03)"><?=$days[$r->day_of_week]??$r->day_of_week?></td>
<td style="padding:5px 4px;border-bottom:1px solid rgba(255,255,255,.03)"><?=htmlspecialchars($r->show_name)?></td>
<td style="padding:5px 4px;border-bottom:1px solid rgba(255,255,255,.03)"><?=htmlspecialchars($r->start_time)?>-<?=htmlspecialchars($r->end_time)?></td>
<td style="padding:5px 4px;border-bottom:1px solid rgba(255,255,255,.03)"><?=htmlspecialchars($r->dj_name?:'Auto')?></td></tr>
<?php endforeach; ?>
</table></div>
