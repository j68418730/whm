<div style="display:flex;gap:12px;align-items:start;flex-wrap:wrap;margin-bottom:20px">
<a href="/admin/package/create" class="btn primary">+ Create Package</a>
<a href="/admin/packages/categories" class="btn secondary">Manage Categories</a>
</div>

<div class="stats-grid">
<div class="stat-card"><h3>Total Packages</h3><div class="value"><?php echo $packagesStats['total_packages']; ?></div><div class="label">All packages</div></div>
<div class="stat-card"><h3>Active</h3><div class="value"><?php echo $packagesStats['active_packages']; ?></div><div class="label">Currently available</div></div>
</div>

<style>
.pkg-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:12px;margin-top:12px}
.pkg-card{background:rgba(8,16,28,.6);border:1px solid rgba(0,191,255,.08);border-radius:10px;padding:16px;transition:.3s}
.pkg-card:hover{border-color:rgba(0,191,255,.2);transform:translateY(-2px)}
.pkg-card .p-name{font-size:15px;font-weight:700;margin-bottom:4px}
.pkg-card .p-type{font-size:11px;color:#64748b;margin-bottom:6px}
.pkg-card .p-price{font-size:20px;font-weight:800;color:#4ade80;margin-bottom:8px}
.pkg-card .p-price small{font-size:11px;color:#64748b;font-weight:400}
.pkg-card .p-features{font-size:11px;color:#94a3b8;margin-bottom:10px;line-height:1.6}
.pkg-card .p-status{display:inline-block;padding:2px 8px;border-radius:4px;font-size:10px;font-weight:600}
.pkg-card .p-actions{display:flex;gap:6px;margin-top:10px}
.pkg-card .p-actions a{padding:4px 12px;border-radius:5px;font-size:11px;text-decoration:none;font-weight:600}
</style>

<?php
$typeLabels = [
    'web_hosting' => 'Web Hosting', 'web_reseller' => 'Web Hosting Reseller',
    'icecast' => 'Icecast Streaming', 'icecast_reseller' => 'Icecast Reseller',
    'vps' => 'VPS', 'dedicated' => 'Dedicated',
    'game_server' => 'Game Server',
];
foreach ($grouped as $type => $pkgs):
if (empty($pkgs)) continue;
$catIcon = '';
foreach ($categories as $c) { if ($c->name === $type) { $catIcon = $c->icon ?? ''; break; } }
?>
<div class="card" style="padding:16px 20px;margin-bottom:16px">
<h3 style="color:var(--accent);font-size:15px;margin-bottom:4px"><?php echo $catIcon ? htmlspecialchars($catIcon) . ' ' : ''; ?><?php echo htmlspecialchars($typeLabels[$type] ?? $type); ?></h3>
<div class="pkg-grid">
<?php foreach ($pkgs as $p): ?>
<div class="pkg-card">
<div class="p-name"><?php echo htmlspecialchars($p->name); ?></div>
<div class="p-type"><?php echo htmlspecialchars($p->description ? substr($p->description, 0, 60) : ''); ?></div>
<div class="p-price">$<?php echo number_format($p->monthly_price, 2); ?><small>/mo</small></div>
<div class="p-features">
<?php if ($p->disk_space): ?>📁 <?php echo $p->disk_space; ?> GB Disk<br><?php endif; ?>
<?php if ($p->bandwidth): ?>📶 <?php echo $p->bandwidth; ?> GB BW<br><?php endif; ?>
<?php if ($p->listener_limit): ?>🎧 <?php echo $p->listener_limit; ?> Listeners<br><?php endif; ?>
<?php if ($p->dj_accounts): ?>🎤 <?php echo $p->dj_accounts; ?> DJs<br><?php endif; ?>
<?php if ($p->chatroom_enabled): ?>💬 Chat Room<br><?php endif; ?>
<?php if ($p->chatroom_voice_enabled): ?>🎤 Chat Voice<br><?php endif; ?>
</div>
<div><span class="p-status" style="background:<?php echo ($p->is_active ?? 1) ? 'rgba(74,222,128,.12);color:#4ade80' : 'rgba(248,113,113,.12);color:#f87171'; ?>"><?php echo ($p->is_active ?? 1) ? 'Active' : 'Inactive'; ?></span></div>
<div class="p-actions">
<a href="/admin/package/edit/<?php echo $p->id; ?>" style="background:rgba(0,140,255,.1);color:#38bdf8">Edit</a>
<a href="/admin/package/delete/<?php echo $p->id; ?>" style="background:rgba(248,113,113,.12);color:#f87171" onclick="return confirm('Delete?')">Delete</a>
</div>
</div>
<?php endforeach; ?>
</div>
</div>
<?php endforeach; if (empty(array_filter($grouped))): ?>
<div class="card"><p style="text-align:center;color:#64748b;padding:20px">No packages defined yet. <a href="/admin/package/create">Create your first package</a></p></div>
<?php endif; ?>
