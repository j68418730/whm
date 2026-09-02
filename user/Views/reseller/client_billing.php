<style>
.section-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:12px;margin-bottom:20px}
.section-card{background:var(--card_bg,rgba(8,16,28,.85));border:1px solid rgba(0,191,255,.08);border-radius:12px;padding:18px;text-align:center;text-decoration:none;color:var(--text,#e0e0e0);transition:.2s}
.section-card:hover{transform:translateY(-3px);border-color:rgba(0,140,255,.3);box-shadow:0 8px 30px rgba(0,140,255,.08)}
.section-card .icon{font-size:28px;margin-bottom:6px}
.section-card .name{font-size:13px;font-weight:600;margin-bottom:2px}
.section-card .count{font-size:26px;font-weight:800;color:var(--accent);margin-bottom:2px}
.section-card .desc{font-size:10px;color:#64748b}
</style>

<div class="section-grid">
<a href="/reseller/billing-system" class="section-card"><div class="icon">📊</div><div class="name">Dashboard</div><div class="desc">Billing overview &amp; stats</div></a>
<a href="/reseller/billing-system/orders" class="section-card"><div class="icon">📋</div><div class="name">Orders</div><div class="count"><?php echo (int)($counts['orders'] ?? 0); ?></div><div class="desc">Client orders</div></a>
<a href="/reseller/billing-system/services" class="section-card"><div class="icon">🖥</div><div class="name">Services</div><div class="count"><?php echo (int)($counts['services'] ?? 0); ?></div><div class="desc">Active services</div></a>
<a href="/reseller/billing-system" class="section-card"><div class="icon">💰</div><div class="name">Invoices</div><div class="count"><?php echo (int)($counts['invoices'] ?? 0); ?></div><div class="desc">Client invoices</div></a>
<a href="/reseller/billing-system/payments" class="section-card"><div class="icon">💳</div><div class="name">Payments</div><div class="count"><?php echo (int)($counts['payments'] ?? 0); ?></div><div class="desc">Transactions</div></a>
<a href="/reseller/billing-system/credits" class="section-card"><div class="icon">🏦</div><div class="name">Credits</div><div class="count"><?php echo (int)($counts['credits'] ?? 0); ?></div><div class="desc">Client credits</div></a>
<a href="/reseller/billing-system/refunds" class="section-card"><div class="icon">↩️</div><div class="name">Refunds</div><div class="count"><?php echo (int)($counts['refunds'] ?? 0); ?></div><div class="desc">Refund processing</div></a>
</div>

<div style="display:flex;gap:4px;flex-wrap:wrap;margin-bottom:16px;border-bottom:1px solid var(--border,rgba(0,191,255,.08));padding-bottom:8px">
<?php foreach ($billingTabs as $tab): ?>
<a href="<?php echo $tab['url']; ?>" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;<?php echo (basename(parse_url($tab['url'], PHP_URL_PATH)) === basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) ? 'background:rgba(0,191,255,.1);color:#00bfff;border-bottom:2px solid #008cff' : 'color:var(--text-secondary)'; ?>"><?php echo $tab['label']; ?></a>
<?php endforeach; ?>
</div>
<h3 style="color:var(--accent);margin-bottom:12px">Billing System</h3>
<p style="color:var(--text_muted,#94a3b8);font-size:13px;margin-bottom:16px">Billing for <b>your own clients</b>. Reuses the Planet Hosts billing engine scoped to your clients — separate from the invoices Planet Hosts sends you.</p>
<div class="stats-grid" style="margin-bottom:20px">
<div class="stat-card"><h3>Total Collected</h3><div class="value">$<?php echo number_format($totalCollected ?? 0, 2); ?></div></div>
<div class="stat-card"><h3>Outstanding</h3><div class="value" style="color:#f87171">$<?php echo number_format($outstanding ?? 0, 2); ?></div></div>
<div class="stat-card"><h3>Monthly Recurring</h3><div class="value" style="color:#4ade80">$<?php echo number_format($mrr ?? 0, 2); ?></div></div>
<div class="stat-card"><h3>Active Services</h3><div class="value"><?php echo $activeServices ?? 0; ?></div></div>
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
<h4 style="color:var(--accent);margin-bottom:12px">Recent Client Invoices</h4>
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