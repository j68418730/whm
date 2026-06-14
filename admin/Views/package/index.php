<div style="display:flex;gap:12px;align-items:start;flex-wrap:wrap;margin-bottom:20px">
<a href="/admin/package/create" class="btn primary">+ Create Package</a>
<a href="/admin/packages/categories" class="btn secondary">Manage Categories</a>
</div>

<div class="stats-grid">
<div class="stat-card"><h3>Total Packages</h3><div class="value"><?php echo $packagesStats['total_packages']; ?></div><div class="label">All packages</div></div>
<div class="stat-card"><h3>Active</h3><div class="value"><?php echo $packagesStats['active_packages']; ?></div><div class="label">Currently available</div></div>
</div>

<?php
$typeLabels = [
    'web_hosting' => 'Web Hosting', 'web_reseller' => 'Web Hosting Reseller',
    'shoutcast' => 'SHOUTcast', 'shoutcast_reseller' => 'SHOUTcast Reseller',
    'icecast' => 'Icecast', 'icecast_reseller' => 'Icecast Reseller',
    'vps' => 'VPS', 'dedicated' => 'Dedicated',
];
foreach ($grouped as $type => $pkgs):
if (empty($pkgs)) continue;
?>
<div class="card" style="padding:16px 24px;margin-bottom:16px">
<h3 style="color:var(--accent);font-size:16px;margin-bottom:12px"><?php
$catIcon = '';
foreach ($categories as $c) { if ($c->name === $type) { $catIcon = $c->icon ?? ''; break; } }
if ($catIcon && (str_starts_with($catIcon, '/') || str_starts_with($catIcon, 'http'))) {
    echo '<img src="' . htmlspecialchars($catIcon, ENT_QUOTES, 'UTF-8') . '" style="width:24px;height:24px;vertical-align:middle;margin-right:6px;border-radius:4px">';
} elseif ($catIcon) {
    echo htmlspecialchars($catIcon, ENT_QUOTES, 'UTF-8') . ' ';
}
echo htmlspecialchars($type, ENT_QUOTES, 'UTF-8');
?></h3>
<table>
<tr><th>Name</th><th>Price</th><th>Disk</th><th>Bandwidth</th><th>Status</th><th>Actions</th></tr>
<?php foreach ($pkgs as $p): ?>
<tr>
<td><strong><?php echo htmlspecialchars($p->name, ENT_QUOTES, 'UTF-8'); ?></strong></td>
<td>$<?php echo number_format($p->monthly_price, 2); ?>/mo</td>
<td><?php echo $p->disk_space ? $p->disk_space . ' GB' : '-'; ?></td>
<td><?php echo $p->bandwidth ? $p->bandwidth . ' GB' : '-'; ?></td>
<td><span class="status-badge status-<?php echo ($p->is_active ?? 1) ? 'active' : 'suspended'; ?>"><?php echo ($p->is_active ?? 1) ? 'Active' : 'Inactive'; ?></span></td>
<td style="display:flex;gap:4px">
<a href="/admin/package/edit/<?php echo $p->id; ?>" class="btn btn-sm secondary">Edit</a>
<a href="/admin/package/delete/<?php echo $p->id; ?>" class="btn btn-sm" style="background:rgba(255,50,50,.15);color:#ff6b6b;border:1px solid rgba(255,50,50,.2)" onclick="return confirm('Delete this package?')">Delete</a>
</td>
</tr>
<?php endforeach; ?>
</table>
</div>
<?php endforeach; if (empty(array_filter($grouped))): ?>
<div class="card"><p style="text-align:center;color:#64748b;padding:20px">No packages defined yet. <a href="/admin/package/create">Create your first package</a></p></div>
<?php endif; ?>
