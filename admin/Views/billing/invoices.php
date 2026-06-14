<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;margin-bottom:16px">
<h3 style="color:var(--accent)">Invoices</h3>
<a class="btn primary" onclick="document.getElementById('invForm').classList.toggle('hidden')">Create Invoice</a>
</div>
<div id="invForm" class="card hidden" style="max-width:500px;margin-bottom:20px">
<form method="POST" action="/admin/billing/invoices/create">
<div class="form-group"><label>User ID</label><input name="user_id" type="number" required></div>
<div class="form-group"><label>Total</label><input name="total" type="number" step="0.01" required></div>
<div class="form-group"><label>Due Date</label><input name="due_date" type="date" value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>"></div>
<button type="submit" class="btn primary">Create</button>
</form></div>
<table><tr><th>#</th><th>Invoice #</th><th>User</th><th>Date</th><th>Due</th><th>Total</th><th>Status</th><th></th></tr>
<?php if (!empty($invoices)): foreach ($invoices as $inv): ?>
<tr><td><?php echo $inv->id; ?></td><td><?php echo htmlspecialchars($inv->invoice_number); ?></td><td><?php echo $inv->user_id; ?></td>
<td><?php echo $inv->date; ?></td><td><?php echo $inv->due_date; ?></td><td>$<?php echo number_format($inv->total, 2); ?></td>
<td><span class="status-badge status-<?php echo $inv->status === 'paid' ? 'active' : ($inv->status === 'overdue' ? 'terminated' : ''); ?>"><?php echo $inv->status; ?></span></td>
<td><form method="POST" action="/admin/billing/invoices/status/<?php echo $inv->id; ?>" style="display:flex;gap:4px">
<select name="status"><option value="draft" <?php echo $inv->status==='draft'?'selected':''; ?>>Draft</option><option value="sent" <?php echo $inv->status==='sent'?'selected':''; ?>>Sent</option><option value="paid" <?php echo $inv->status==='paid'?'selected':''; ?>>Paid</option><option value="overdue" <?php echo $inv->status==='overdue'?'selected':''; ?>>Overdue</option><option value="cancelled" <?php echo $inv->status==='cancelled'?'selected':''; ?>>Cancelled</option></select>
<button type="submit" class="btn btn-sm primary">Update</button></form></td></tr>
<?php endforeach; else: ?><tr><td colspan="8" style="text-align:center;padding:20px;color:#64748b">No invoices yet.</td></tr>
<?php endif; ?></table>
