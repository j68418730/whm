<div style="display:flex;gap:4px;flex-wrap:wrap;margin-bottom:16px;border-bottom:1px solid rgba(255,255,255,.06);padding-bottom:8px">
<a href="/admin/billing" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">📊 Dashboard</a>
<a href="/admin/billing/products" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">📦 Products</a>
<a href="/admin/billing/orders" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">📋 Orders</a>
<a href="/admin/billing/services" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">🖥 Services</a>
<a href="/admin/billing/invoices" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">💰 Invoices</a>
<a href="/admin/billing/payments" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;background:rgba(0,191,255,.1);color:#00bfff;border-bottom:2px solid #008cff">💳 Payments</a>
<a href="/admin/billing/taxes" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">🏛️ Taxes</a>
<a href="/admin/billing/coupons" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">🎟️ Coupons</a>
<a href="/admin/billing/credits" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">🏦 Credits</a>
<a href="/admin/billing/refunds" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">↩️ Refunds</a>
<a href="/admin/billing/reports" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">📈 Reports</a>
</div>
<div style="display:flex;gap:8px;margin-bottom:12px;align-items:center">
<a class="btn primary" onclick="document.getElementById('payForm').classList.toggle('hidden')">+ Record Payment</a>
<div style="flex:1"></div>
<form method="GET" style="display:flex;gap:6px;align-items:center">
<input type="text" name="q" placeholder="🔍 Transaction ID, user, invoice, amount..." value="<?php echo htmlspecialchars($searchQuery ?? ''); ?>" style="padding:8px 12px;border-radius:6px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;outline:none;width:260px;font-size:12px">
<button type="submit" class="btn btn-sm primary">Lookup</button>
<?php if (!empty($searchQuery)): ?>
<a href="/admin/billing/payments" class="btn btn-sm secondary">Clear</a>
<?php endif; ?>
</form>
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
<div style="display:flex;justify-content:space-between">
<div style="font-weight:600;font-size:14px">#<?php echo $p->id; ?></div>
<span style="font-size:11px;color:#64748b"><?php echo htmlspecialchars($p->username ?? "User #{$p->user_id}"); ?></span>
</div>
<div style="font-size:12px;color:#94a3b8;margin-top:4px">Method: <?php echo $p->method; ?> · Invoice: <?php echo $p->invoice_id ?? '-'; ?><?php if ($p->transaction_id): ?> · TXN: <code style="font-size:11px;background:rgba(0,0,0,.3);padding:1px 6px;border-radius:4px"><?php echo htmlspecialchars($p->transaction_id); ?></code><?php endif; ?></div>
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