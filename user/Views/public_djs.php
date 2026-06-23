<style>
.dj-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:14px}
.dj-card{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:12px;padding:20px;text-align:center;text-decoration:none;color:#e0e0e0;transition:.2s}
.dj-card:hover{transform:translateY(-3px);border-color:rgba(0,140,255,.3)}
.dj-card .avatar{width:64px;height:64px;border-radius:50%;margin:0 auto 10px;background:linear-gradient(135deg,#0A84FF,#a855f7);display:flex;align-items:center;justify-content:center;font-size:24px}
.dj-card .name{font-size:15px;font-weight:700}
.dj-card .status{font-size:11px;margin-top:2px}
.dj-card .genres{font-size:10px;color:#64748b;margin-top:4px;display:flex;gap:4px;flex-wrap:wrap;justify-content:center}
.dj-card .genre-tag{padding:2px 6px;background:rgba(0,140,255,.08);border-radius:4px;color:#0A84FF}
</style>
<h2>🎧 Our DJs</h2>
<p style="color:#64748b;margin-bottom:16px">Browse our roster of talented radio DJs.</p>
<?php
$allDjs = [];
try {
    $allDjs = $this->db->table('radio_djs')->where('status', 'active')->orderBy('name', 'ASC')->get() ?: [];
} catch(\Exception $e) {}
?>
<div class="dj-grid">
<?php if (empty($allDjs)): ?>
<div style="grid-column:1/-1;text-align:center;padding:40px;color:#64748b">No DJs registered yet.</div>
<?php else: foreach($allDjs as $dj): 
$initial = strtoupper(substr($dj->name ?? $dj->username, 0, 1));
$isOnline = $dj->last_active && (time() - strtotime($dj->last_active)) < 300;
$genres = $dj->bio ? array_filter(explode(',', $dj->bio)) : [];
?>
<a href="/user/public-dj/<?php echo $dj->id;?>" class="dj-card">
<div class="avatar"><?php echo $initial;?></div>
<div class="name"><?php echo htmlspecialchars($dj->name ?? $dj->username);?></div>
<div class="status" style="color:<?php echo $isOnline?'#4ade80':'#64748b';?>">● <?php echo $isOnline?'Online':'Offline';?></div>
<?php if (!empty($genres)): ?><div class="genres"><?php foreach(array_slice($genres,0,3) as $g):?><span class="genre-tag"><?php echo htmlspecialchars(trim($g));?></span><?php endforeach;?></div><?php endif;?>
</a>
<?php endforeach; endif; ?>
</div>