<div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;margin-bottom:20px">
<a href="/admin/account/create" class="btn btn-primary"><i class="bi bi-person-plus"></i> Create Account</a>
<span style="color:var(--text_muted);font-size:13px"><?php echo $accountsStats['total_accounts']; ?> accounts · <?php echo $accountsStats['active_accounts']; ?> active · <?php echo $accountsStats['suspended_accounts']; ?> suspended</span>
</div>

<table class="table table-hover" style="color:#fff">
<thead><tr>
<th>Username</th><th>Domain</th><th>Package</th><th>Actions</th><th>Status</th>
</tr></thead>
<tbody>
<?php if (!empty($accounts)): foreach ($accounts as $a): 
$pkgName = 'N/A';
if (isset($packages)) {
    foreach ($packages as $p) { if ($p->id == $a->package_id) { $pkgName = $p->name; break; } }
}
?>
<tr>
<td><strong><?php echo htmlspecialchars($a->username); ?></strong></td>
<td><?php echo htmlspecialchars($a->domain ?? '-'); ?></td>
<td><?php echo htmlspecialchars($pkgName); ?></td>
<td style="white-space:nowrap">
<a href="/admin/account/show/<?php echo $a->id; ?>" class="btn btn-sm btn-secondary"><i class="bi bi-eye"></i> View</a>
<?php if ($a->status === 'active'): ?>
<a href="/admin/account/suspend/<?php echo $a->id; ?>" class="btn btn-sm btn-secondary" style="background:rgba(250,204,21,.1);color:#facc15;border-color:rgba(250,204,21,.2)" onclick="return confirm('Suspend <?php echo htmlspecialchars($a->username); ?>?')"><i class="bi bi-pause-circle"></i></a>
<?php elseif ($a->status === 'suspended'): ?>
<a href="/admin/account/unsuspend/<?php echo $a->id; ?>" class="btn btn-sm btn-secondary" style="background:rgba(74,222,128,.1);color:#4ade80;border-color:rgba(74,222,128,.2)"><i class="bi bi-play-circle"></i></a>
<?php endif; ?>
</td>
<td><span class="badge bg-<?php echo $a->status === 'active' ? 'success' : ($a->status === 'suspended' ? 'warning' : 'danger'); ?>"><?php echo ucfirst($a->status); ?></span></td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="5" style="text-align:center;padding:2rem;color:var(--text_muted)">No accounts created yet.</td></tr>
<?php endif; ?>
</tbody>
</table>
