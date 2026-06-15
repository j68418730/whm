<div class="card" style="margin-bottom:16px">
<h3 style="color:var(--accent)">API Permissions</h3>
<p style="color:var(--text-secondary);font-size:13px">Manage what each API key can access.</p>
</div>
<table><tr><th>Name</th><th>Permissions</th><th>Rate Limit</th><th>Status</th><th></th></tr>
<?php if (!empty($keys)): foreach ($keys as $k): ?>
<tr>
<td><?php echo htmlspecialchars($k->name); ?></td>
<td>
<form method="POST" action="/admin/api/permissions/update/<?php echo $k->id; ?>" style="display:flex;gap:4px;align-items:center">
<select name="permissions"><option value="read" <?php echo $k->permissions==='read'?'selected':''; ?>>Read</option><option value="read,write" <?php echo $k->permissions==='read,write'?'selected':''; ?>>Read/Write</option><option value="admin" <?php echo $k->permissions==='admin'?'selected':''; ?>>Admin</option></select>
</td>
<td><input name="rate_limit" type="number" value="<?php echo $k->rate_limit ?? 60; ?>" style="width:70px"> req/min</td>
<td><select name="is_active"><option value="1" <?php echo ($k->is_active ?? 1) ? 'selected':''; ?>>Active</option><option value="0" <?php echo !($k->is_active ?? 1) ? 'selected':''; ?>>Inactive</option></select></td>
<td><button type="submit" class="btn btn-sm primary">Save</button></td></form></tr>
<?php endforeach; else: ?><tr><td colspan="5" style="text-align:center;padding:20px;color:#64748b">No API keys to configure.</td></tr>
<?php endif; ?></table>
