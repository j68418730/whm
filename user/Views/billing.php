<div class="card"><div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap"><h3 style="margin:0">Billing Overview</h3><a href="/user/billing/payment-methods" class="btn btn-sm btn-secondary">Manage Payment Methods</a></div>
<div class="stats-grid" style="margin:12px 0">
<div class="stat-card"><h3>Outstanding</h3><div class="value">$<?php echo number_format($outstanding ?? 0, 2); ?></div></div>
<div class="stat-card"><h3>Total Invoices</h3><div class="value"><?php echo count($invoices ?? []); ?></div></div>
</div>
<table><tr><th>Invoice</th><th>Date</th><th>Total</th><th>Status</th><th></th></tr>
<?php if (!empty($invoices)): foreach ($invoices as $inv): ?>
<tr>
<td><?php echo htmlspecialchars($inv->invoice_number); ?></td>
<td><?php echo $inv->date; ?></td>
<td>$<?php echo number_format($inv->total, 2); ?></td>
<td><span class="status-badge status-<?php echo $inv->status === 'paid' ? 'active' : ($inv->status === 'overdue' ? 'terminated' : ''); ?>"><?php echo $inv->status; ?></span></td>
<td><?php if ($inv->status === 'sent' || $inv->status === 'overdue'): ?><a href="/user/billing/pay/<?php echo $inv->id; ?>" class="btn btn-sm primary" onclick="return confirm('Mark as paid?')">Pay Now</a><?php endif; ?></td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="5" style="text-align:center;padding:20px;color:#64748b">No invoices yet.</td></tr>
<?php endif; ?></table>
</div>
