<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<div class="page-header">
<div class="d-flex justify-content-between align-items-center">
<h2 style="margin:0; color:var(--accent, #008cff)">👥 Account Management</h2>
<a href="/admin/account/create" class="btn btn-primary"><i class="bi bi-plus me-2"></i> Create Account</a>
</div>
</div>

<?php
$totalAccounts = count($accountGroups ?? []);
$activeCount = count(array_filter(array_values(array_reduce($accountGroups ?? [], function($carry, $group) {
    return $carry + count(array_filter($group, function($a) { return ($a->status ?? 'active') === 'active'; });
}), []);
$suspendedCount = count(array_filter(array_values(array_reduce($accountGroups ?? [], function($carry, $group) {
    return $carry + count(array_filter($group, function($a) { return ($a->status ?? 'active') === 'suspended'; });
}), []));
$terminatedCount = count(array_filter(array_values(array_reduce($accountGroups ?? [], function($carry, $group) {
    return $carry + count(array_filter($group, function($a) { return ($a->status ?? 'active') === 'terminated'; });
}), []));
?>
<div class="stats-grid">
<div class="stat-card"><h3>Total Accounts</h3><div class="value"><?php echo $totalAccounts; ?></div></div>
<div class="stat-card"><h3>Active</h3><div class="value" style="color:var(--success, #4ade80)"><?php echo $activeCount; ?></div></div>
<div class="stat-card"><h3>Suspended</h3><div class="value" style="color:var(--warning, #facc15)"><?php echo $suspendedCount; ?></div></div>
<div class="stat-card"><h3>Terminated</h3><div class="value" style="color:var(--danger, #f87171)"><?php echo $terminatedCount; ?></div></div>
</div>
</div>

<div class="table-responsive">
<table class="table table-bordered align-middle mb-0">
<thead><tr><th>Owner</th><th>Username</th><th>Domain</th><th>Package</th><th>Status</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach ($accountGroups as $ownerName => $group): ?>
<tr><td colspan="6" style="background:rgba(0,191,255,.1);border-top:1px solid rgba(0,191,255,.2);padding:12px;font-weight:600;color:var(--primary, #008cff);font-size:13px;"><?php echo htmlspecialchars($ownerName); ?> <span class="badge bg-primary"><?php echo count($group); ?> accounts</span></td></tr>
<?php foreach ($group as $a): ?>
<tr>
<td style="width:120px"><?php echo htmlspecialchars($a->reseller_id ? 'Reseller ' . $a->reseller_id : 'Unassigned'); ?></td>
<td><strong><?php echo htmlspecialchars($a->username); ?></strong></td>
<td><?php echo htmlspecialchars($a->domain ?: '—'); ?></td>
<td><?php echo $a->package_id ? 'Package ' . $a->package_id : 'Free'; ?></td>
<td><span class="status-badge status-<?php echo $a->status ?: 'active'; ?>"><?php echo $a->status ?: 'Active'; ?></span></td>
<td style="white-space:nowrap">
<a href="/admin/account/show/<?php echo (int)$a->id; ?>" class="btn btn-sm btn-primary"><i class="bi bi-eye me-1"></i> Show</a>
<a href="/admin/account/edit/<?php echo (int)$a->id; ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil me-1"></i> Edit</a>
<form method="POST" style="display:inline" action="/admin/account/suspend/<?php echo (int)$a->id; ?>" onsubmit="return confirm('Suspend this account?')">
<button class="btn btn-sm btn-danger">Suspend</button>
</form>
</td>
</tr>
<?php endforeach; ?>
<?php endforeach; ?>
</tbody>
</table>
</div>

<div class="mt-4">
<p class="text-muted small">Accounts grouped by owner. Use the owner filter or search to find specific accounts.</p>
</div>
</div>