<div style="display:flex;gap:4px;flex-wrap:wrap;margin-bottom:16px;border-bottom:1px solid rgba(255,255,255,.06);padding-bottom:8px">
<a href="/admin/billing" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">📊 Dashboard</a>
<a href="/admin/billing/cart" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">🛒 Cart</a>
<a href="/admin/billing/products" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">📦 Products</a>
<a href="/admin/billing/orders" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">📋 Orders</a>
<a href="/admin/billing/services" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">🖥 Services</a>
<a href="/admin/billing/invoices" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">💰 Invoices</a>
<a href="/admin/billing/payments" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">💳 Payments</a>
<a href="/admin/billing/taxes" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">🏛️ Taxes</a>
<a href="/admin/billing/coupons" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">🎟️ Coupons</a>
<a href="/admin/billing/credits" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">🏦 Credits</a>
<a href="/admin/billing/refunds" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;background:rgba(0,191,255,.1);color:#00bfff;border-bottom:2px solid #008cff">↩️ Refunds</a>
<a href="/admin/billing/reports" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">📈 Reports</a>
</div>
<h3 style="color:var(--accent);margin-bottom:12px">Refunds</h3>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:10px">
<?php if (!empty($refunds)): foreach ($refunds as $r): ?>
<div class="card" style="margin-bottom:0;padding:14px">
<div style="display:flex;justify-content:space-between">
<div><span style="font-weight:600;font-size:14px">#<?php echo $r->id; ?> · <?php echo htmlspecialchars($r->username ?? "User #{$r->user_id}"); ?></span></div>
<span style="font-size:16px;font-weight:700;color:#f87171">-$<?php echo number_format($r->amount, 2); ?></span>
</div>
<div style="font-size:11px;color:#64748b;margin-top:4px">Reason: <?php echo htmlspecialchars($r->reason ?: '-'); ?></div>
<div style="font-size:11px;color:#64748b">Payment: <?php echo $r->payment_id ?? '-'; ?> · Invoice: <?php echo $r->invoice_id ?? '-'; ?></div>
<div style="font-size:10px;color:#64748b;margin-top:2px"><?php echo $r->created_at; ?></div>
<div style="margin-top:6px"><a href="/admin/billing/refunds/delete/<?php echo $r->id; ?>" class="btn btn-sm danger" onclick="return confirm('Delete refund record?')">Delete</a></div>
</div>
<?php endforeach; else: ?>
<div class="card" style="text-align:center;padding:20px;grid-column:1/-1;color:#64748b">No refunds processed.</div>
<?php endif; ?>
</div>