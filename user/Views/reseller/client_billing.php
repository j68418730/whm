<h3 style="color:var(--accent);margin-bottom:12px">Billing System</h3>
<p style="color:var(--text_muted,#94a3b8);font-size:13px;margin-bottom:16px">Invoice <b>your own clients</b> from here. These are your retail invoices — completely separate from the invoices Planet Hosts sends you.</p>
<div class="stats-grid" style="margin-bottom:16px">
<div class="stat-card"><h3>Outstanding</h3><div class="value">$<?php echo number_format($totalOutstanding ?? 0, 2); ?></div></div>
<div class="stat-card"><h3>Invoices</h3><div class="value"><?php echo count($invoices ?? []); ?></div></div>
</div>

<div class="card">
<h4 style="color:var(--accent);margin-bottom:12px">➕ Issue Invoice</h4>
<form method="POST" action="/reseller/billing-system/create">
<div class="row g-3">
<div class="col-md-4">
<label class="form-label">Client *</label>
<select name="client_id" required>
<?php foreach ($clients as $c): ?><option value="<?php echo (int)$c->id; ?>"><?php echo htmlspecialchars($c->username); ?> (<?php echo htmlspecialchars($c->email); ?>)</option><?php endforeach; ?>
</select>
</div>
<div class="col-md-4"><label class="form-label">Amount ($) *</label><input type="number" step="0.01" name="total" min="0.01" required></div>
<div class="col-md-4"><label class="form-label">Description / Notes</label><input type="text" name="description" placeholder="e.g. Monthly hosting"></div>
</div>
<button type="submit" class="btn btn-primary" style="margin-top:12px">Issue Invoice</button>
</form>
</div>

<div class="card">
<h4 style="color:var(--accent);margin-bottom:12px">Client Invoices</h4>
<table class="table">
<tr><th>Invoice</th><th>Client</th><th>Date</th><th>Due</th><th>Total</th><th>Status</th><th></th></tr>
<?php if (!empty($invoices)): foreach ($invoices as $inv): ?>
<tr>
<td><?php echo htmlspecialchars($inv->invoice_number); ?></td>
<td><?php echo htmlspecialchars($inv->client); ?></td>
<td><?php echo $inv->date; ?></td>
<td><?php echo $inv->due_date; ?></td>
<td>$<?php echo number_format((float)$inv->total, 2); ?></td>
<td><span class="status-badge status-<?php echo $inv->status === 'paid' ? 'active' : 'terminated'; ?>"><?php echo htmlspecialchars($inv->status); ?></span></td>
<td>
<?php if ($inv->status !== 'paid'): ?>
<a href="/reseller/billing-system/paid/<?php echo (int)$inv->id; ?>" class="btn btn-sm btn-success" onclick="return confirm('Mark invoice #<?php echo htmlspecialchars($inv->invoice_number); ?> paid?')">Mark Paid</a>
<?php endif; ?>
</td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="7" style="text-align:center;padding:20px;color:#64748b">No client invoices yet.</td></tr>
<?php endif; ?>
</table>
</div>
<a href="/reseller" class="btn secondary" style="margin-top:12px">&larr; Back</a>