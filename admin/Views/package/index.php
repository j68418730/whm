<div style="display:flex;gap:12px;align-items:start;flex-wrap:wrap;margin-bottom:20px">
<a href="/admin/package/create" class="btn primary">+ Create Package</a>
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
    'web_hosting' => 'Web Hosting',
    'web_reseller' => 'Web Reseller',
    'icecast' => 'Icecast',
    'icecast_reseller' => 'Icecast Reseller',
    'shoutcast' => 'SHOUTcast',
    'shoutcast_reseller' => 'SHOUTcast Reseller',
    'game_server' => 'Game Server',
    'vps' => 'VPS',
    'dedicated' => 'Dedicated',
    'dev' => 'Dev',
];
?>

<div class="pkg-grid">
<?php foreach ($packages as $p): ?>
<div class="pkg-card">
<div class="p-name"><?php echo htmlspecialchars($p->name); ?></div>
<div class="p-type"><?php echo $typeLabels[$p->type] ?? $p->type; ?></div>
<div class="p-price" style="font-size:13px;color:#94a3b8;font-weight:400"><?php echo ($productCounts[$p->id] ?? 0); ?> Product<?php echo ($productCounts[$p->id] ?? 0) !== 1 ? 's' : ''; ?></div>
<div class="p-features">
<?php if ($p->disk_space): ?>📁 <?php echo $p->disk_space; ?> GB Disk<br><?php endif; ?>
<?php if ($p->bandwidth): ?>📶 <?php echo $p->bandwidth; ?> GB BW<br><?php endif; ?>
<?php
$pFeaturesRaw = isset($p->features) ? $p->features : null;
$pFeats = is_string($pFeaturesRaw) ? json_decode($pFeaturesRaw, true) ?? [] : (is_array($pFeaturesRaw) ? $pFeaturesRaw : []);
$strPkg = $pFeats['streaming_package'] ?? [];
$gamePkg = $pFeats['game_package'] ?? [];
if (!empty($strPkg)): ?><span style="color:#0A84FF">🎧 Streaming</span>
<?php if (!empty($strPkg['max_listeners'])): ?>&nbsp;<?php echo $strPkg['max_listeners']; ?> Listeners<?php endif; ?>
<?php if (!empty($strPkg['max_stations'])): ?>, <?php echo $strPkg['max_stations']; ?> Stations<?php endif; ?>
<?php if (!empty($strPkg['max_djs'])): ?>, <?php echo $strPkg['max_djs']; ?> DJs<?php endif; ?>
<br><?php endif; ?>
<?php if (!empty($gamePkg)): ?><span style="color:#FF9500">🎮 Game</span>
<?php if (!empty($gamePkg['max_servers'])): ?>&nbsp;<?php echo $gamePkg['max_servers']; ?> Servers<?php endif; ?>
<?php if (!empty($gamePkg['max_players'])): ?>, <?php echo $gamePkg['max_players']; ?> Slots<?php endif; ?>
<br><?php endif; ?>
<?php if (!empty($pFeats['chat'])): ?>💬 Chat<?php if (!empty($pFeats['chat_voice'])): ?>+Voice<?php endif; ?><?php if (!empty($pFeats['chat_video'])): ?>+Video<?php endif; ?><br><?php endif; ?>
<?php if (!empty($pFeats['dj_panel'])): ?>🎤 DJ Panel<br><?php endif; ?>
</div>
<div><span class="p-status" style="background:<?php echo ($p->is_active ?? 1) ? 'rgba(74,222,128,.12);color:#4ade80' : 'rgba(248,113,113,.12);color:#f87171'; ?>"><?php echo ($p->is_active ?? 1) ? 'Active' : 'Inactive'; ?></span></div>
<div class="p-actions">
<a href="/admin/package/edit/<?php echo $p->id; ?>" style="background:rgba(0,140,255,.1);color:#38bdf8">Edit</a>
<a href="/admin/package/clone/<?php echo $p->id; ?>" style="background:rgba(74,222,128,.1);color:#4ade80" onclick="return confirm('Clone this package?')">Clone</a>
<a href="/admin/package/delete/<?php echo $p->id; ?>" style="background:rgba(250,204,21,.1);color:#facc15" onclick="return confirm('Deactivate this package?')">Deactivate</a>
<a href="/admin/package/hard-delete/<?php echo $p->id; ?>" style="background:rgba(248,113,113,.12);color:#f87171" onclick="return confirm('PERMANENTLY delete this package? This cannot be undone.')">Delete</a>
</div>
</div>
<?php endforeach; ?>
</div>

<?php if (empty($packages)): ?>
<div class="card"><p style="text-align:center;color:#64748b;padding:20px">No packages defined yet. <a href="/admin/package/create">Create your first package</a></p></div>
<?php endif; ?>
