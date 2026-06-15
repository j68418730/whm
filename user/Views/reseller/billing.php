<h3 style="color:var(--accent);margin-bottom:12px">Billing Overview</h3>
<div class="stats-grid" style="margin-bottom:16px">
<div class="stat-card"><h3>Outstanding</h3><div class="value">$<?php echo number_format($totalOwed ?? 0, 2); ?></div></div>
<div class="stat-card"><h3>Total Invoices</h3><div class="value"><?php echo count($invoices ?? []); ?></div></div>
</div>
<table><tr><th>Invoice</th><th>Date</th><th>Total</th><th>Status</th></tr>
<?php if (!empty($invoices)): foreach ($invoices as $inv): ?>
<tr><td><?php echo htmlspecialchars($inv->invoice_number); ?></td><td><?php echo $inv->date; ?></td><td>$<?php echo number_format($inv->total, 2); ?></td>
<td><span class="status-badge status-<?php echo $inv->status === 'paid' ? 'active' : 'terminated'; ?>"><?php echo $inv->status; ?></span></td></tr>
<?php endforeach; else: ?><tr><td colspan="4" style="text-align:center;padding:20px;color:#64748b">No invoices.</td></tr>
<?php endif; ?></table>
<a href="/reseller" class="btn secondary" style="margin-top:12px">&larr; Back</a>
