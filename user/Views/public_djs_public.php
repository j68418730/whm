<!DOCTYPE html>
<html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Our DJs - <?php echo htmlspecialchars($stationName);?></title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Inter',sans-serif;background:#070b14;color:#e0e0e0;padding:16px}
.dj-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:10px}
.dj-card{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:10px;padding:14px;text-align:center;text-decoration:none;color:#e0e0e0}
.dj-card .avatar{width:48px;height:48px;border-radius:50%;margin:0 auto 6px;background:linear-gradient(135deg,#0A84FF,#a855f7);display:flex;align-items:center;justify-content:center;font-size:18px}
.dj-card .name{font-size:13px;font-weight:600}
.dj-card .status{font-size:10px;margin-top:2px}
.dj-card .genres{font-size:9px;color:#64748b;margin-top:3px}
h2{font-size:16px;font-weight:700;margin-bottom:12px;color:#0A84FF}
</style></head><body>
<h2>🎧 <?php echo htmlspecialchars($stationName);?> DJs</h2>
<div class="dj-grid">
<?php if(empty($djs)):?><p style="color:#64748b;font-size:12px;grid-column:1/-1;text-align:center;padding:20px">No DJs found.</p>
<?php else: foreach($djs as $dj): $init = strtoupper(substr($dj->display_name ?? $dj->username, 0, 1)); $online = $dj->last_active && (time()-strtotime($dj->last_active)) < 300; ?>
<div class="dj-card"><div class="avatar"><?php echo $init;?></div>
<div class="name"><?php echo htmlspecialchars($dj->display_name ?? $dj->username);?></div>
<div class="status" style="color:<?php echo $online?'#4ade80':'#64748b';?>">● <?php echo $online ? 'Online' : 'Offline';?></div>
<?php if($dj->genres):?><div class="genres"><?php echo htmlspecialchars($dj->genres);?></div><?php endif;?>
</div><?php endforeach; endif;?>
</div></body></html>
