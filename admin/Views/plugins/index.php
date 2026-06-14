<div class="stats-grid">
<div class="stat-card"><h3>Total Plugins</h3><div class="value"><?php echo count($plugins); ?></div></div>
<div class="stat-card"><h3>Active</h3><div class="value" style="color:#4ade80"><?php echo count(array_filter($plugins, fn($p) => $p->is_active)); ?></div></div>
</div>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:16px">
<?php foreach (['Radio' => '📡', 'Billing' => '💰', 'WebsiteBuilder' => '🌐'] as $name => $icon):
$dbPlugin = null;
foreach ($plugins as $p) { if (stripos($p->name ?? '', $name) !== false) $dbPlugin = $p; }
$active = $dbPlugin ? $dbPlugin->is_active : false;
?>
<div class="card">
<div style="font-size:32px"><?php echo $icon; ?></div>
<h3 style="color:var(--accent);margin:8px 0"><?php echo $name; ?> Plugin</h3>
<p style="color:var(--text-muted);font-size:13px;margin-bottom:12px">Status: <span class="status-badge status-<?php echo $active ? 'active' : 'terminated'; ?>"><?php echo $active ? 'Enabled' : 'Disabled'; ?></span></p>
<?php if ($dbPlugin): ?><a href="/admin/plugins/toggle/<?php echo $dbPlugin->id; ?>" class="btn btn-sm secondary">Toggle</a><?php endif; ?>
</div>
<?php endforeach; ?>
</div>
