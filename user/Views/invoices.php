<div class="card"><h3 style="color:var(--accent)">Invoices</h3>
<table><tr><th>#</th><th>Date</th><th>Due Date</th><th>Total</th><th>Status</th></tr>
<?php if (!empty($invoices)): foreach ($invoices as $inv): ?>
<tr>
<td><?php echo htmlspecialchars($inv->invoice_number); ?></td>
<td><?php echo $inv->date; ?></td>
<td><?php echo $inv->due_date; ?></td>
<td>$<?php echo number_format($inv->total, 2); ?></td>
<td><span class="status-badge status-<?php echo $inv->status === 'paid' ? 'active' : ($inv->status === 'overdue' ? 'terminated' : ''); ?>"><?php echo $inv->status; ?></span></td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="5" style="text-align:center;padding:20px;color:#64748b">No invoices found.</td></tr>
<?php endif; ?></table>
</div>
