<?php
require_once __DIR__ . '/../radio_helper.php';
header('Content-Type: text/html; charset=utf-8');
$streamId = (int)($_GET['stream'] ?? 0);
if (!$streamId) exit;
$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4','radiouser','Skylinehosting171');
$djs = $pdo->prepare("SELECT name, username, last_active FROM radio_djs WHERE stream_id=? AND status='active' ORDER BY name");
$djs->execute([$streamId]);
$rows = $djs->fetchAll(PDO::FETCH_OBJ);
?>
<div style="font-family:Inter,sans-serif">
<?php if (empty($rows)): ?>
<div style="color:#64748b;font-size:12px;text-align:center;padding:10px">No DJs listed</div>
<?php else: ?>
<?php foreach ($rows as $dj): ?>
<div style="display:flex;align-items:center;gap:8px;padding:6px 0;border-bottom:1px solid rgba(255,255,255,.04)">
<div style="width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,#008cff,#a855f7);display:flex;align-items:center;justify-content:center;font-size:12px;color:#fff;font-weight:700"><?=strtoupper(substr($dj->name?:$dj->username,0,1))?></div>
<div><div style="font-size:13px;font-weight:600;color:#e0e0e0"><?=htmlspecialchars($dj->name?:$dj->username)?></div>
<div style="font-size:10px;color:#64748b"><?=$dj->last_active ? 'Active: '.date('M j',strtotime($dj->last_active)) : 'Never online'?></div></div>
</div>
<?php endforeach; ?>
<?php endif; ?>
</div>
