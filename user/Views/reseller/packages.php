<h3 style="color:var(--accent);margin-bottom:12px">Available Packages</h3>
<table><tr><th>Name</th><th>Disk Space</th><th>Bandwidth</th><th>Price</th><th>Status</th></tr>
<?php if (!empty($packages)): foreach ($packages as $p): ?>
<tr><td><?php echo htmlspecialchars($p->name); ?></td><td><?php echo $p->disk_space ?? '-'; ?> GB</td><td><?php echo $p->bandwidth ?? '-'; ?> GB</td>
<td><?php echo isset($p->price) ? '$'.number_format($p->price,2) : '-'; ?></td>
<td><span class="status-badge status-<?php echo ($p->is_active ?? 1) ? 'active' : 'terminated'; ?>"><?php echo ($p->is_active ?? 1) ? 'Active' : 'Inactive'; ?></span></td></tr>
<?php endforeach; else: ?><tr><td colspan="5" style="text-align:center;padding:20px;color:#64748b">No packages available.</td></tr>
<?php endif; ?></table>
<a href="/reseller" class="btn secondary" style="margin-top:12px">&larr; Back</a>
