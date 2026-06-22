<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px">
<div>
<h2 style="margin:0">🌐 Domain Manager</h2>
<p style="color:#64748b;margin:4px 0 0">Manage all client domains.</p>
</div>
<button class="btn primary" onclick="document.getElementById('addForm').style.display='block';document.getElementById('addForm').scrollIntoView({behavior:'smooth'})"><i class="bi bi-plus-circle"></i> Add Domain</button>
</div>

<div id="addForm" class="card" style="display:none;max-width:500px;margin-bottom:20px">
<form method="POST" action="/admin/domains/store">
<h4 style="color:var(--accent);margin-bottom:12px">Add Domain</h4>
<div class="form-group"><label>Domain</label><input name="domain" required placeholder="example.com"></div>
<div class="form-group"><label>Assign to User</label>
<select name="user_id" required>
<option value="">-- Select User --</option>
<?php foreach ($users as $u): ?>
<option value="<?php echo $u->id; ?>"><?php echo htmlspecialchars($u->username . ' (' . $u->email . ')'); ?></option>
<?php endforeach; ?>
</select></div>
<div class="form-group"><label><input type="checkbox" name="is_primary" value="1"> Set as primary domain</label></div>
<button type="submit" class="btn primary">Add Domain</button>
</form>
</div>

<div class="stats-grid" style="margin-bottom:20px">
<div class="stat-card"><h3>Total Domains</h3><div class="value"><?php echo count($domains); ?></div></div>
<div class="stat-card"><h3>Active</h3><div class="value" style="color:#4ade80"><?php echo count(array_filter($domains, fn($d) => $d->status === 'active')); ?></div></div>
<div class="stat-card"><h3>SSL Enabled</h3><div class="value" style="color:#38bdf8"><?php echo count(array_filter($domains, fn($d) => $d->ssl_enabled)); ?></div></div>
<div class="stat-card"><h3>Locked</h3><div class="value" style="color:#facc15"><?php echo count(array_filter($domains, fn($d) => $d->locked)); ?></div></div>
</div>

<table>
<thead><tr><th>Domain</th><th>Owner</th><th>Status</th><th>IP</th><th>SSL</th><th>Lock</th><th>Actions</th></tr></thead>
<tbody>
<?php if (!empty($domains)): foreach ($domains as $d): 
$owner = $userMap[$d->user_id] ?? null;
?>
<tr>
<td><strong><?php echo htmlspecialchars($d->domain); ?></strong></td>
<td><?php echo $owner ? htmlspecialchars($owner->username) : 'Unknown'; ?></td>
<td><span class="status-badge status-<?php echo $d->status === 'active' ? 'active' : 'terminated'; ?>"><?php echo ucfirst($d->status); ?></span></td>
<td><?php echo $d->ip_address ?: '-'; ?></td>
<td><a href="/admin/domains/ssl/<?php echo $d->id; ?>" style="color:<?php echo $d->ssl_enabled ? '#4ade80' : '#64748b'; ?>;text-decoration:none"><?php echo $d->ssl_enabled ? '🔒 Enabled' : '🔓 Disabled'; ?></a></td>
<td><a href="/admin/domains/lock/<?php echo $d->id; ?>" style="color:<?php echo $d->locked ? '#facc15' : '#64748b'; ?>;text-decoration:none"><?php echo $d->locked ? '🔒 Locked' : '🔓 Unlocked'; ?></a></td>
<td style="white-space:nowrap">
<a href="/admin/dns/edit/<?php echo htmlspecialchars($d->domain); ?>" class="btn btn-sm secondary"><i class="bi bi-globe"></i> DNS</a>
<a href="/admin/domains/delete/<?php echo $d->id; ?>" class="btn btn-sm" style="background:rgba(248,113,113,.12);color:#f87171" onclick="return confirm('Delete <?php echo htmlspecialchars($d->domain); ?>?')"><i class="bi bi-trash"></i></a>
</td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="7" style="text-align:center;padding:20px;color:#64748b">No domains added yet.</td></tr>
<?php endif; ?>
</tbody>
</table>
