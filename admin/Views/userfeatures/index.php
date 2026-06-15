<div class="stats-grid" style="margin-bottom:20px">
<div class="stat-card"><h3>Total Packages</h3><div class="value"><?php echo count($packages); ?></div></div>
</div>
<div class="card"><h3 style="color:var(--accent);margin-bottom:16px">Feature Toggles by Package</h3>
<table><tr><th>Package</th><?php foreach ($features as $f): ?><th><?php echo htmlspecialchars($featureLabels[$f] ?? $f); ?></th><?php endforeach; ?></tr>
<?php if (!empty($packages)): foreach ($packages as $pkg): $feats = json_decode($pkg->features ?? '{}', true); ?>
<tr><td><strong><?php echo htmlspecialchars($pkg->name); ?></strong></td>
<?php foreach ($features as $f): $enabled = $feats[$f] ?? 1; ?>
<td><a href="/admin/userfeatures/toggle/<?php echo $f; ?>?package=<?php echo $pkg->id; ?>" style="text-decoration:none"><span class="status-badge status-<?php echo $enabled ? 'active' : 'terminated'; ?>"><?php echo $enabled ? 'ON' : 'OFF'; ?></span></a></td>
<?php endforeach; ?></tr>
<?php endforeach; else: ?><tr><td colspan="<?php echo count($features)+1; ?>" style="text-align:center;padding:20px;color:#64748b">No packages yet.</td></tr>
<?php endif; ?></table></div>
