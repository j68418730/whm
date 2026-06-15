<div class="card" style="margin-bottom:16px">
<h3 style="color:var(--accent)"><?php echo htmlspecialchars($reseller->company_name); ?></h3>
<p style="color:var(--text-secondary)"><?php echo htmlspecialchars($reseller->email); ?> &middot; <?php echo htmlspecialchars($reseller->phone ?: 'No phone'); ?></p>
<p style="color:var(--text-secondary);font-size:13px">Website: <?php echo htmlspecialchars($reseller->website ?: '-'); ?> &middot; Status: <span class="status-badge status-<?php echo $reseller->is_active ? 'active' : 'terminated'; ?>"><?php echo $reseller->is_active ? 'Active' : 'Inactive'; ?></span></p>
</div>
<h3 style="color:var(--accent);margin-bottom:12px">Hosting Accounts (<?php echo count($accounts); ?>)</h3>
<table><tr><th>Username</th><th>Domain</th><th>Email</th><th>Status</th><th>Package</th><th>Actions</th></tr>
<?php if (!empty($accounts)): foreach ($accounts as $a): $pkgName = $pkgNames[$a->id] ?? '-'; ?>
<tr><td><?php echo htmlspecialchars($a->username); ?></td><td><?php echo htmlspecialchars($a->domain ?: '-'); ?></td><td><?php echo htmlspecialchars($a->email); ?></td>
<td><span class="status-badge status-<?php echo $a->status === 'active' ? 'active' : 'terminated'; ?>"><?php echo $a->status; ?></span></td>
<td><?php echo htmlspecialchars($pkgName); ?></td>
<td><a href="/admin/account/show/<?php echo $a->id; ?>" class="btn btn-sm primary">View</a></td></tr>
<?php endforeach; else: ?><tr><td colspan="6" style="text-align:center;padding:20px;color:#64748b">No accounts owned by this reseller.</td></tr>
<?php endif; ?></table>
<a href="/admin/reseller" class="btn secondary" style="margin-top:12px">&larr; Back</a>
