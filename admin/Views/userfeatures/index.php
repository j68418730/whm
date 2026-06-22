<div class="stats-grid" style="margin-bottom:20px">
<div class="stat-card"><h3>Package Types</h3><div class="value"><?php echo count($packagesByType); ?></div></div>
<div class="stat-card"><h3>Total Plans</h3><div class="value"><?php echo array_sum(array_map('count', $packagesByType)); ?></div></div>
</div>

<div class="card">
<h3 style="color:var(--accent);margin-bottom:16px">📋 Package Features by Type</h3>
<p style="color:#64748b;font-size:13px;margin-bottom:16px">Each package type has relevant features. Toggle features ON/OFF per package.</p>

<?php foreach ($packagesByType as $typeName => $pkgs): ?>
<div style="background:rgba(8,16,28,.4);border:1px solid rgba(0,191,255,.08);border-radius:12px;padding:16px;margin-bottom:16px">
<h4 style="color:var(--accent);margin-bottom:12px"><?php echo htmlspecialchars($typeName); ?> (<?php echo count($pkgs); ?> plans)</h4>
<div style="overflow-x:auto">
<table style="font-size:12px">
<thead><tr><th>Plan Name</th>
<?php foreach ($allFeatures as $fKey => $fLabel): ?>
<th style="text-align:center;white-space:nowrap;font-size:10px"><?php echo htmlspecialchars($fLabel); ?></th>
<?php endforeach; ?>
</tr></thead>
<tbody>
<?php foreach ($pkgs as $pkg): 
$feats = json_decode($pkg->features ?? '{}', true);
?>
<tr>
<td><strong style="font-size:12px"><?php echo htmlspecialchars($pkg->name); ?></strong>
<?php if ($pkg->monthly_price > 0): ?><span style="color:#64748b;font-size:10px"> $<?php echo number_format($pkg->monthly_price, 2); ?>/mo</span><?php endif; ?>
</td>
<?php foreach ($allFeatures as $fKey => $fLabel): 
$val = $feats[$fKey] ?? null;
$isNumeric = in_array($fKey, ['disk_space','bandwidth','email_accounts','ftp_accounts','databases','subdomains','addon_domains','listener_limit','bitrate','storage_limit','dj_accounts']);
$dbVal = $pkg->$fKey ?? 0;
$enabled = $val !== null ? $val : ($dbVal > 0 ? 1 : 0);
$display = $dbVal > 0 ? $dbVal : ($enabled ? 'ON' : 'OFF');
$color = $enabled ? '#4ade80' : '#f87171';
?>
<td style="text-align:center">
<a href="/admin/userfeatures/toggle/<?php echo $fKey; ?>?package=<?php echo $pkg->id; ?>" style="text-decoration:none">
<span class="status-badge status-<?php echo $enabled ? 'active' : 'terminated'; ?>" style="font-size:10px;padding:1px 6px"><?php echo $display; ?></span>
</a>
</td>
<?php endforeach; ?>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</div>
<?php endforeach; ?>
</div>
