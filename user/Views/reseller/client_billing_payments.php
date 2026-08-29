<?php include __DIR__ . '/partials/billing_tabs.php'; ?>
<h3 style="color:var(--accent);margin-bottom:12px">💳 Payments — Your Clients</h3>
<div class="card">
<table class="table">
<tr><th>#</th><th>Client</th><th>Amount</th><th>Method</th><th>Status</th><th>Date</th></tr>
<?php if (!empty($payments)): foreach ($payments as $p): ?>
<tr>
<td><?php echo (int)$p->id; ?></td>
<td><?php echo htmlspecialchars($p->client); ?></td>
<td>$<?php echo number_format((float)$p->amount, 2); ?></td>
<td><?php echo htmlspecialchars($p->method ?? 'manual'); ?></td>
<td><span class="status-badge status-<?php echo $p->status === 'completed' ? 'active' : 'terminated'; ?>"><?php echo htmlspecialchars($p->status); ?></span></td>
<td><?php echo $p->created_at ?? '-'; ?></td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="6" style="text-align:center;padding:20px;color:#64748b">No client payments.</td></tr>
<?php endif; ?>
</table>
</div>
<a href="/reseller/billing-system" class="btn secondary" style="margin-top:12px">&larr; Back</a>