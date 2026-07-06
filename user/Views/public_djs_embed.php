<!DOCTYPE html>
<html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Our DJs</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Inter',sans-serif;background:#070b14;color:#e0e0e0;padding:16px}
.dj-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:10px}
.dj-card{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:10px;padding:14px;text-align:center;text-decoration:none;color:#e0e0e0}
.dj-card .avatar{width:48px;height:48px;border-radius:50%;margin:0 auto 6px;background:linear-gradient(135deg,#0A84FF,#a855f7);display:flex;align-items:center;justify-content:center;font-size:18px}
.dj-card .name{font-size:13px;font-weight:600}
.dj-card .status{font-size:10px;margin-top:2px}
h2{font-size:16px;font-weight:700;margin-bottom:12px;color:#0A84FF}
</style></head><body>
<?php
$pdo = new PDO('mysql:host=localhost;dbname=radiohosting;charset=utf8mb4', 'radiouser', 'Skylinehosting171');
$djs = $pdo->query("SELECT d.* FROM radio_djs d JOIN radio_stations s ON d.stream_id = s.id WHERE d.status='active' ORDER BY d.name ASC")->fetchAll(PDO::FETCH_OBJ);
?>
<h2>🎧 Our DJs</h2>
<div class="dj-grid">
<?php if(empty($djs)):?><p style="color:#64748b;font-size:12px">No DJs.</p>
<?php else: foreach($djs as $dj): $init = strtoupper(substr($dj->name ?? $dj->username, 0, 1)); $online = $dj->last_active && (time()-strtotime($dj->last_active)) < 300; ?>
<div class="dj-card"><div class="avatar"><?php echo $init;?></div>
<div class="name"><?php echo htmlspecialchars($dj->name ?? $dj->username);?></div>
<div class="status" style="color:<?php echo $online?'#4ade80':'#64748b';?>">● <?php echo $online?'Online':'Offline';?></div></div>
<?php endforeach; endif;?>
</div></body></html>
