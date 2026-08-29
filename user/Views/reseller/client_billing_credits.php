<?php include __DIR__ . '/partials/billing_tabs.php'; ?>
<h3 style="color:var(--accent);margin-bottom:12px">🏦 Credits — Your Clients</h3>
<div class="card">
<h4 style="color:var(--accent);margin-bottom:12px">➕ Add Credit</h4>
<form method="POST" action="/reseller/billing-system/credits/store">
<div class="row g-3">
<div class="col-md-4">
<label class="form-label">Client *</label>
<select name="client_id" required>
<?php foreach ($clients as $c): ?><option value="<?php echo (int)$c->id; ?>"><?php echo htmlspecialchars($c->username); ?></option><?php endforeach; ?>
</select>
</div>
<div class="col-md-3"><label class="form-label">Amount ($) *</label><input type="number" step="0.01" name="amount" required></div>
<div class="col-md-5"><label class="form-label">Description</label><input type="text" name="description" placeholder="e.g. Goodwill credit"></div>
</div>
<button type="submit" class="btn btn-primary" style="margin-top:12px">Add Credit</button>
</form>
</div>
<div class="card">
<table class="table">
<tr><th>#</th><th>Client</th><th>Amount</th><th>Description</th></tr>
<?php if (!empty($credits)): foreach ($credits as $c): ?>
<tr>
<td><?php echo (int)$c->id; ?></td>
<td><?php echo htmlspecialchars($c->client); ?></td>
<td>$<?php echo number_format((float)$c->amount, 2); ?></td>
<td><?php echo htmlspecialchars($c->description ?? '-'); ?></td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="4" style="text-align:center;padding:20px;color:#64748b">No client credits.</td></tr>
<?php endif; ?>
</table>
</div>
<a href="/reseller/billing-system" class="btn secondary" style="margin-top:12px">&larr; Back</a>