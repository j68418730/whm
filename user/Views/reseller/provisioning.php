<h3 style="color:var(--accent);margin-bottom:12px">Provisioning</h3>
<p style="color:var(--text_muted,#94a3b8);font-size:13px;margin-bottom:16px">
Orders placed by your clients are activated through the <b>Planet Hosts backend</b> — you never need SSH or root.
Click <b>Run Provisioning</b> to create the account, allocate resources and start services for that order.
</p>
<div class="stats-grid" style="margin-bottom:16px">
<div class="stat-card"><h3>Total Orders</h3><div class="value"><?php echo count($orders ?? []); ?></div></div>
<div class="stat-card"><h3>Provisioned</h3><div class="value"><?php echo $provisioned ?? 0; ?></div></div>
<div class="stat-card"><h3>Pending</h3><div class="value"><?php echo $pending ?? 0; ?></div></div>
</div>
<table>
<tr><th>Order</th><th>Client</th><th>Domain</th><th>Package</th><th>Total</th><th>Status</th><th>Account</th><th></th></tr>
<?php if (!empty($orders)): foreach ($orders as $o): ?>
<tr>
<td>#<?php echo $o->id; ?></td>
<td><?php echo htmlspecialchars($o->username ?: $o->user_id); ?></td>
<td><?php echo htmlspecialchars($o->domain ?: '-'); ?></td>
<td><?php echo htmlspecialchars($pkgNames[$o->id] ?? '-'); ?></td>
<td>$<?php echo number_format((float)$o->total, 2); ?></td>
<td><span class="status-badge status-<?php echo in_array($o->status, ['active','completed']) ? 'active' : 'terminated'; ?>"><?php echo htmlspecialchars($o->status); ?></span></td>
<td><?php echo htmlspecialchars($o->account_status ?? '-'); ?></td>
<td>
<?php if (in_array($o->status, ['pending','unpaid'])): ?>
<form method="POST" action="/reseller/provisioning/run/<?php echo (int)$o->id; ?>" onsubmit="return confirm('Run Planet Hosts provisioning for order #<?php echo (int)$o->id; ?>?');">
<button type="submit" class="btn btn-sm btn-primary">▶ Run Provisioning</button>
</form>
<?php else: ?>
<span style="color:#64748b;font-size:13px">—</span>
<?php endif; ?>
</td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="8" style="text-align:center;padding:20px;color:#64748b">No orders from your clients yet.</td></tr>
<?php endif; ?>
</table>
<a href="/reseller" class="btn secondary" style="margin-top:12px">&larr; Back</a>