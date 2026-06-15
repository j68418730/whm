<div class="stats-grid" style="margin-bottom:20px">
<div class="stat-card"><h3>DNS Zones</h3><div class="value"><?php echo $stats['total_zones'] ?? 0; ?></div></div>
<div class="stat-card"><h3>Records</h3><div class="value"><?php echo $stats['total_records'] ?? 0; ?></div></div>
</div>
<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px">
<a class="btn primary" onclick="document.getElementById('zoneForm').classList.toggle('hidden')">Create Zone</a>
</div>
<div id="zoneForm" class="card hidden" style="max-width:500px;margin-bottom:20px">
<form method="POST" action="/admin/dns/create-zone">
<div class="form-group"><label>Domain</label><input name="domain" required placeholder="example.com"></div>
<div class="form-group"><label>Admin Email</label><input name="admin_email" value="admin@planet-hosts.com"></div>
<button type="submit" class="btn primary">Create</button>
</form></div>
<table><tr><th>Domain</th><th>Records</th><th>Serial</th><th>Created</th><th></th></tr>
<?php if (!empty($zones)): foreach ($zones as $z): ?>
<tr><td><strong><?php echo htmlspecialchars($z->domain); ?></strong></td><td><?php echo $z->record_count ?? 0; ?></td><td style="font-family:monospace;font-size:12px"><?php echo $z->serial ?? '-'; ?></td><td><?php echo $z->created_at ?? '-'; ?></td>
<td><a href="/admin/dns/edit/<?php echo $z->id; ?>" class="btn btn-sm primary">Edit</a> <a href="/admin/dns/delete/<?php echo $z->id; ?>" class="btn btn-sm danger" onclick="return confirm('Delete zone?')">Delete</a></td></tr>
<?php endforeach; else: ?><tr><td colspan="5" style="text-align:center;padding:20px;color:#64748b">No DNS zones yet.</td></tr>
<?php endif; ?></table>
