<?php include __DIR__ . '/partials/billing_tabs.php'; ?>
<h3 style="color:var(--accent);margin-bottom:12px">↩️ Refunds — Your Clients</h3>
<div class="card">
<table class="table">
<tr><th>#</th><th>Client</th><th>Amount</th><th>Reason</th><th>Status</th><th>Date</th></tr>
<?php if (!empty($refunds)): foreach ($refunds as $r): ?>
<tr>
<td><?php echo (int)$r->id; ?></td>
<td><?php echo htmlspecialchars($r->client); ?></td>
<td>$<?php echo number_format((float)$r->amount, 2); ?></td>
<td><?php echo htmlspecialchars($r->reason ?? '-'); ?></td>
<td><span class="status-badge status-<?php echo ($r->status ?? 'completed') === 'completed' ? 'active' : 'terminated'; ?>"><?php echo htmlspecialchars($r->status ?? 'completed'); ?></span></td>
<td><?php echo $r->created_at ?? '-'; ?></td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="6" style="text-align:center;padding:20px;color:#64748b">No client refunds.</td></tr>
<?php endif; ?>
</table>
</div>
<a href="/reseller/billing-system" class="btn secondary" style="margin-top:12px">&larr; Back</a>