<div class="card">
<h3>Payment Methods</h3>
<p style="color:var(--text_muted);font-size:13px;margin-bottom:14px">Manage your saved payment methods for faster checkout.</p>

<form method="POST" action="/user/billing/payment-methods/add" class="row g-2" style="padding:14px;background:rgba(255,255,255,.02);border-radius:8px;margin-bottom:14px">
<div class="col-md-3"><div class="form-group"><label>Type</label><select name="type" class="form-select"><option value="card">Credit/Debit Card</option><option value="paypal">PayPal</option><option value="bank">Bank Transfer</option><option value="crypto">Cryptocurrency</option></select></div></div>
<div class="col-md-5"><div class="form-group"><label>Details</label><input name="details" class="form-control" placeholder="e.g. Visa ending in 4242 or PayPal email" required></div></div>
<div class="col-md-4"><div class="form-group"><label>Billing Address</label><input name="billing_address" class="form-control" placeholder="Street, City, ZIP"></div></div>
<div class="col-12"><button class="btn btn-primary">Add Payment Method</button></div>
</form>

<?php if (count($methods) > 0): ?>
<table class="table table-hover">
<thead><tr><th>Type</th><th>Details</th><th>Billing Address</th><th>Default</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach ($methods as $m): ?>
<tr>
<td><span class="badge bg-info"><?php echo htmlspecialchars($m->type); ?></span></td>
<td><?php echo htmlspecialchars($m->details ?? ''); ?></td>
<td><?php echo htmlspecialchars($m->billing_address ?? '-'); ?></td>
<td><?php echo $m->is_default ? '<span class="badge bg-success">Default</span>' : '<a href="/user/billing/payment-methods/default/'.$m->id.'" class="btn btn-sm btn-secondary">Set Default</a>'; ?></td>
<td><a href="/user/billing/payment-methods/delete/<?php echo $m->id; ?>" class="btn btn-sm btn-danger" onclick="return confirm(\'Delete this payment method?\')">&#128465;</a></td>
</tr>
<?php endforeach; ?>
</tbody></table>
<?php else: ?>
<p style="color:var(--text_muted);text-align:center;padding:20px">No payment methods saved yet.</p>
<?php endif; ?>
</div>
