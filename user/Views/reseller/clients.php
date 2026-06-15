<h3 style="color:var(--accent);margin-bottom:12px">Client Accounts</h3>
<table><tr><th>Username</th><th>Domain</th><th>Email</th><th>Status</th><th>Package</th></tr>
<?php if (!empty($accounts)): foreach ($accounts as $a): $pn = $pkgNames[$a->id] ?? '-'; ?>
<tr><td><?php echo htmlspecialchars($a->username); ?></td><td><?php echo htmlspecialchars($a->domain ?: '-'); ?></td><td><?php echo htmlspecialchars($a->email); ?></td>
<td><span class="status-badge status-<?php echo $a->status === 'active' ? 'active' : 'terminated'; ?>"><?php echo $a->status; ?></span></td>
<td><?php echo htmlspecialchars($pn); ?></td></tr>
<?php endforeach; else: ?><tr><td colspan="5" style="text-align:center;padding:20px;color:#64748b">No clients yet.</td></tr>
<?php endif; ?></table>
<a href="/reseller" class="btn secondary" style="margin-top:12px">&larr; Back</a>
