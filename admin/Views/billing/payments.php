<div style="display:flex;gap:8px;margin-bottom:12px">
<a class="btn primary" onclick="document.getElementById('payForm').classList.toggle('hidden')">+ Record Payment</a>
</div>
<div id="payForm" class="card hidden" style="max-width:500px;margin-bottom:16px">
<form method="POST" action="/admin/billing/payments/store">
<h3 style="color:var(--accent);margin-bottom:8px">Record Payment</h3>
<div class="form-group"><label>User</label>
<select name="user_id" required>
<option value="">-- Select User --</option>
<?php if (isset($hostingUsers)): foreach ($hostingUsers as $h): ?>
<option value="<?php echo $h->id; ?>"><?php echo htmlspecialchars($h->username . ' (' . ($h->domain ?? '') . ')'); ?></option>
<?php endforeach; endif; ?>
</select></div>
<div class="form-group"><label>Invoice ID (optional)</label><input name="invoice_id" type="number"></div>
<div class="form-group"><label>Amount</label><input name="amount" type="number" step="0.01" required></div>
<div class="form-group"><label>Method</label><select name="method"><option value="credit_card">Credit Card</option><option value="paypal">PayPal</option><option value="bank_transfer">Bank Transfer</option><option value="manual">Manual</option></select></div>
<div class="form-group"><label>Transaction ID</label><input name="transaction_id" placeholder="optional"></div>
<button type="submit" class="btn primary">Record</button>
</form></div>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:10px">
<?php if (!empty($payments)): foreach ($payments as $p): ?>
<div class="card" style="margin-bottom:0;padding:14px">
<div style="font-weight:600;font-size:14px">#<?php echo $p->id; ?> · User <?php echo $p->user_id; ?></div>
<div style="font-size:12px;color:#94a3b8;margin-top:4px">Method: <?php echo $p->method; ?> · Invoice: <?php echo $p->invoice_id ?? '-'; ?></div>
<div style="display:flex;justify-content:space-between;margin-top:6px">
<span style="font-size:15px;font-weight:600">$<?php echo number_format($p->amount, 2); ?></span>
<span class="status-badge status-<?php echo $p->status === 'completed' ? 'active' : 'terminated'; ?>"><?php echo $p->status; ?></span>
</div>
<div style="font-size:10px;color:#64748b;margin-top:4px"><?php echo $p->created_at; ?></div>
<div style="margin-top:6px"><a href="/admin/billing/payments/delete/<?php echo $p->id; ?>" class="btn btn-sm danger" onclick="return confirm('Delete payment?')">Delete</a></div>
</div>
<?php endforeach; else: ?>
<div class="card" style="text-align:center;padding:20px;grid-column:1/-1;color:#64748b">No payments yet.</div>
<?php endif; ?>
</div>