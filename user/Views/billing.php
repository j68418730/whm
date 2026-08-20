<style>
.cred-hero{background:linear-gradient(135deg,rgba(0,140,255,.14),rgba(0,191,255,.05));border:1px solid rgba(0,191,255,.2);border-radius:12px;padding:18px 20px;margin-bottom:16px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px}
.cred-hero .bal{font-size:28px;font-weight:800;color:#4ade80}
.cred-hero .lbl{font-size:11px;color:#64748b}
.cred-form{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
.cred-form input{width:130px;padding:8px 10px;border-radius:8px;border:1px solid rgba(255,255,255,.12);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:13px;outline:none}
.cred-form input:focus{border-color:#0A84FF}
.cred-hist{max-height:180px;overflow-y:auto;margin-top:8px}
.cred-hist .row{display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px solid rgba(255,255,255,.04);font-size:12px}
.cred-hist .row .am{font-weight:700}
</style>

<div class="cred-hero">
<div><div class="lbl">Available Credits</div><div class="bal">$<?php echo number_format($creditBalance ?? 0, 2); ?></div></div>
<form method="POST" action="/user/billing/credits/add" class="cred-form" onsubmit="return confirm('A deposit invoice will be created. Pay it to add credits to your balance.')">
<input type="number" name="amount" min="1" step="0.01" placeholder="Amount ($)" required>
<button type="submit" class="btn btn-sm primary">+ Add Credits</button>
</form>
</div>

<?php if (!empty($creditHistory)): ?>
<div class="card"><h3>Credit History</h3>
<div class="cred-hist">
<?php foreach ($creditHistory as $c): $isCredit = $c->kind === 'credit'; ?>
<div class="row"><span><?php echo $isCredit ? '+' : ''; ?><?php echo htmlspecialchars($c->description); ?></span>
<span class="am" style="color:<?php echo $isCredit ? '#4ade80' : '#f87171'; ?>"><?php echo ($isCredit ? '+' : '') . '$' . number_format(abs((float)$c->amount), 2); ?></span></div>
<?php endforeach; ?>
</div>
</div>
<?php endif; ?>

<div class="card"><div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap"><h3 style="margin:0">Billing Overview</h3><a href="/user/billing/payment-methods" class="btn btn-sm btn-secondary">Manage Payment Methods</a></div>
<div class="stats-grid" style="margin:12px 0">
<div class="stat-card"><h3>Outstanding</h3><div class="value">$<?php echo number_format($outstanding ?? 0, 2); ?></div></div>
<div class="stat-card"><h3>Total Invoices</h3><div class="value"><?php echo count($invoices ?? []); ?></div></div>
</div>
<table><tr><th>Invoice</th><th>Date</th><th>Total</th><th>Status</th><th></th></tr>
<?php if (!empty($invoices)): foreach ($invoices as $inv): ?>
<tr>
<td><?php echo htmlspecialchars($inv->invoice_number); ?><?php echo trim((string)($inv->notes ?? '')) === 'CREDIT_DEPOSIT' ? ' <span style="font-size:10px;color:#facc15">(credit deposit)</span>' : ''; ?></td>
<td><?php echo $inv->date; ?></td>
<td>$<?php echo number_format($inv->total, 2); ?><?php if ((float)($inv->credit_applied ?? 0) > 0): ?> <span style="font-size:10px;color:#4ade80">(-$<?php echo number_format((float)$inv->credit_applied, 2); ?> credit)</span><?php endif; ?></td>
<td><span class="status-badge status-<?php echo $inv->status === 'paid' ? 'active' : ($inv->status === 'overdue' ? 'terminated' : ''); ?>"><?php echo $inv->status; ?></span></td>
<td><?php if ($inv->status === 'sent' || $inv->status === 'overdue'): ?>
<div style="display:flex;gap:4px">
<?php if ((float)$creditBalance > 0 && trim((string)($inv->notes ?? '')) !== 'CREDIT_DEPOSIT'): ?>
<a href="/user/billing/use-credits/<?php echo $inv->id; ?>" class="btn btn-sm btn-secondary" style="color:#4ade80" onclick="return confirm('Apply available credits to this invoice?')">Use Credits</a>
<?php endif; ?>
<a href="/user/billing/pay/<?php echo $inv->id; ?>" class="btn btn-sm primary" onclick="return confirm('Mark as paid?')">Pay Now</a>
</div>
<?php endif; ?></td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="5" style="text-align:center;padding:20px;color:#64748b">No invoices yet.</td></tr>
<?php endif; ?></table>
</div>