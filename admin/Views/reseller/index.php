<div class="stats-grid" style="margin-bottom:20px">
<div class="stat-card"><h3>Total Resellers</h3><div class="value"><?php echo $resellerStats['total_resellers']; ?></div></div>
<div class="stat-card"><h3>Active</h3><div class="value"><?php echo $resellerStats['active_resellers']; ?></div></div>
<div class="stat-card"><h3>Owned Accounts</h3><div class="value"><?php echo $resellerStats['accounts_owned_by_resellers']; ?></div></div>
</div>
<div style="display:flex;gap:8px;margin-bottom:16px">
<a href="/admin/reseller/create" class="btn primary">Create Reseller</a>
</div>
<table><tr><th>ID</th><th>Company</th><th>Contact</th><th>Email</th><th>Status</th><th>Accounts</th><th></th></tr>
<?php if (!empty($resellers)): foreach ($resellers as $r):
$acctCount = $acctCounts[$r->id] ?? 0;
?>
<tr><td><?php echo $r->id; ?></td><td><?php echo htmlspecialchars($r->company_name); ?></td><td><?php echo htmlspecialchars($r->contact_name ?: '-'); ?></td><td><?php echo htmlspecialchars($r->email); ?></td>
<td><span class="status-badge status-<?php echo $r->is_active ? 'active' : 'terminated'; ?>"><?php echo $r->is_active ? 'Active' : 'Inactive'; ?></span></td>
<td><?php echo $acctCount; ?></td>
<td><a href="/admin/reseller/<?php echo $r->id; ?>" class="btn btn-sm primary">View</a> <a href="/admin/reseller/edit/<?php echo $r->id; ?>" class="btn btn-sm secondary">Edit</a></td></tr>
<?php endforeach; else: ?><tr><td colspan="7" style="text-align:center;padding:20px;color:#64748b">No resellers yet.</td></tr>
<?php endif; ?></table>
