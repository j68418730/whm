<div style="display:flex;gap:12px;align-items:start;flex-wrap:wrap;margin-bottom:20px">
<a href="/admin/account/create" class="btn primary">+ Create Account</a>
</div>
<div class="stats-grid">
<div class="stat-card"><h3>Total Accounts</h3><div class="value"><?php echo $accountsStats['total_accounts']; ?></div><div class="label">All hosting accounts</div></div>
<div class="stat-card"><h3>Active</h3><div class="value"><?php echo $accountsStats['active_accounts']; ?></div><div class="label">Currently active</div></div>
<div class="stat-card"><h3>Suspended</h3><div class="value"><?php echo $accountsStats['suspended_accounts']; ?></div><div class="label">Suspended</div></div>
<div class="stat-card"><h3>Terminated</h3><div class="value"><?php echo $accountsStats['terminated_accounts']; ?></div><div class="label">Terminated</div></div>
</div>
<table>
<tr><th>Username</th><th>Email</th><th>Package</th><th>Status</th><th>Actions</th></tr>
<?php if (!empty($accounts)): foreach ($accounts as $a): ?>
<tr>
<td><strong><?php echo htmlspecialchars($a->username, ENT_QUOTES, 'UTF-8'); ?></strong></td>
<td><?php echo htmlspecialchars($a->email, ENT_QUOTES, 'UTF-8'); ?></td>
<td><?php $pkg='N/A'; if(isset($packages)){foreach($packages as $p){if($p->id==$a->package_id)$pkg=$p->name;}} echo htmlspecialchars($pkg, ENT_QUOTES, 'UTF-8'); ?></td>
<td><span class="status-badge status-<?php echo $a->status === 'active' ? 'active' : ($a->status === 'suspended' ? 'suspended' : 'terminated'); ?>"><?php echo ucfirst($a->status); ?></span></td>
<td style="display:flex;gap:4px">
<a href="/admin/account/show/<?php echo $a->id; ?>" class="btn btn-sm secondary">View</a>
<a href="/admin/account/suspend/<?php echo $a->id; ?>" class="btn btn-sm secondary" onclick="return confirm('Suspend?')">Suspend</a>
<a href="/admin/account/unsuspend/<?php echo $a->id; ?>" class="btn btn-sm secondary">Unsuspend</a>
<a href="/admin/account/terminate/<?php echo $a->id; ?>" class="btn btn-sm" style="background:rgba(255,50,50,.15);color:#ff6b6b;border:1px solid rgba(255,50,50,.2)" onclick="return confirm('Terminate?')">Terminate</a>
</td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="5" style="text-align:center;padding:2rem;color:#64748b">No accounts created yet.</td></tr>
<?php endif; ?>
</table>
