<?php include __DIR__ . '/partials/billing_tabs.php'; ?>
<h3 style="color:var(--accent);margin-bottom:12px">📋 Orders — Your Clients</h3>
<div class="card">
<table class="table">
<tr><th>Order</th><th>Client</th><th>Total</th><th>Status</th><th>Date</th></tr>
<?php if (!empty($orders)): foreach ($orders as $o): ?>
<tr>
<td>#<?php echo (int)$o->id; ?></td>
<td><?php echo htmlspecialchars($o->client); ?></td>
<td>$<?php echo number_format((float)$o->total, 2); ?></td>
<td><span class="status-badge status-<?php echo $o->status === 'active' ? 'active' : 'terminated'; ?>"><?php echo htmlspecialchars($o->status); ?></span></td>
<td><?php echo $o->created_at; ?></td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="5" style="text-align:center;padding:20px;color:#64748b">No client orders.</td></tr>
<?php endif; ?>
</table>
</div>
<a href="/reseller/billing-system" class="btn secondary" style="margin-top:12px">&larr; Back</a>