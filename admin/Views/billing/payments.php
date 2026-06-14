<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;margin-bottom:16px">
<h3 style="color:var(--accent)">Payments</h3>
<a class="btn primary" onclick="document.getElementById('payForm').classList.toggle('hidden')">Record Payment</a>
</div>
<div id="payForm" class="card hidden" style="max-width:500px;margin-bottom:20px">
<form method="POST" action="/admin/billing/payments/store">
<div class="form-group"><label>User ID</label><input name="user_id" type="number" required></div>
<div class="form-group"><label>Invoice ID (optional)</label><input name="invoice_id" type="number"></div>
<div class="form-group"><label>Amount</label><input name="amount" type="number" step="0.01" required></div>
<div class="form-group"><label>Method</label><select name="method"><option value="credit_card">Credit Card</option><option value="paypal">PayPal</option><option value="bank_transfer">Bank Transfer</option><option value="manual">Manual</option></select></div>
<div class="form-group"><label>Transaction ID</label><input name="transaction_id" placeholder="optional"></div>
<button type="submit" class="btn primary">Record</button>
</form></div>
<table><tr><th>#</th><th>User</th><th>Invoice</th><th>Amount</th><th>Method</th><th>Status</th><th>Date</th></tr>
<?php if (!empty($payments)): foreach ($payments as $p): ?>
<tr><td><?php echo $p->id; ?></td><td><?php echo $p->user_id; ?></td><td><?php echo $p->invoice_id ?? '-'; ?></td>
<td>$<?php echo number_format($p->amount, 2); ?></td><td><?php echo $p->method; ?></td>
<td><span class="status-badge status-<?php echo $p->status === 'completed' ? 'active' : 'terminated'; ?>"><?php echo $p->status; ?></span></td>
<td><?php echo $p->created_at; ?></td></tr>
<?php endforeach; else: ?><tr><td colspan="7" style="text-align:center;padding:20px;color:#64748b">No payments yet.</td></tr>
<?php endif; ?></table>
