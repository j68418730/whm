<h3 style="color:var(--accent);margin-bottom:16px">Refunds</h3>
<table><tr><th>#</th><th>User ID</th><th>Payment</th><th>Invoice</th><th>Amount</th><th>Reason</th><th>Date</th></tr>
<?php if (!empty($refunds)): foreach ($refunds as $r): ?>
<tr><td><?php echo $r->id; ?></td><td><?php echo $r->user_id; ?></td>
<td><?php echo $r->payment_id ?? '-'; ?></td><td><?php echo $r->invoice_id ?? '-'; ?></td>
<td>$<?php echo number_format($r->amount, 2); ?></td>
<td><?php echo htmlspecialchars($r->reason ?: '-'); ?></td><td><?php echo $r->created_at; ?></td></tr>
<?php endforeach; else: ?><tr><td colspan="7" style="text-align:center;padding:20px;color:#64748b">No refunds processed.</td></tr>
<?php endif; ?></table>
