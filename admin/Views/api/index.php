<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<div class="stats-grid">
<div class="stat-card"><h3>Total Keys</h3><div class="value"><?php echo count($keys); ?></div></div>
</div>
<div class="card" style="max-width:500px;margin-bottom:20px">
<form method="POST" action="/admin/api"><div style="display:flex;gap:10px;flex-wrap:wrap;align-items:end">
<div class="form-group" style="flex:2"><label>Key Name</label><input name="name" required placeholder="e.g. Dev API"></div>
<div class="form-group" style="flex:1"><label>Permissions</label><select name="permissions"><option>read</option><option>read,write</option><option>admin</option></select></div>
<div class="form-group"><button type="submit" class="btn primary">Generate</button></div>
</div></form>
</div>
<table><tr><th>Name</th><th>Key (hash)</th><th>Permissions</th><th>Created</th><th></th></tr>
<?php if (!empty($keys)): foreach ($keys as $k): ?>
<tr><td><?php echo htmlspecialchars($k->name); ?></td><td style="font-family:monospace;font-size:12px"><?php echo substr($k->key_hash, 0, 16); ?>...</td>
<td><?php echo htmlspecialchars($k->permissions ?? 'read'); ?></td><td><?php echo $k->created_at ?? '-'; ?></td>
<td><a href="/admin/api/delete/<?php echo $k->id; ?>" class="btn btn-sm danger" onclick="return confirm('Delete?')">Revoke</a></td></tr>
<?php endforeach; else: ?><tr><td colspan="5" style="text-align:center;padding:20px;color:#64748b">No API keys yet.</td></tr>
<?php endif; ?></table>
