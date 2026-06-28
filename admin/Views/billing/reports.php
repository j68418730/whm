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
<a href="/admin/billing/refunds" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">↩️ Refunds</a>
<a href="/admin/billing/reports" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;background:rgba(0,191,255,.1);color:#00bfff;border-bottom:2px solid #008cff">📈 Reports</a>
</div>
<h3 style="color:var(--accent);margin-bottom:16px">Billing Reports</h3>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:10px;margin-bottom:20px">
<div class="card" style="margin-bottom:0;padding:14px">
<div style="font-size:11px;color:#64748b">Total Tax Collected</div>
<div style="font-size:24px;font-weight:700;color:var(--accent);margin-top:4px">$<?php echo number_format($taxTotal, 2); ?></div>
</div>
<div class="card" style="margin-bottom:0;padding:14px">
<div style="font-size:11px;color:#64748b">Invoice Status Breakdown</div>
<?php if (!empty($invoiceStats)): foreach ($invoiceStats as $is): ?>
<div style="display:flex;justify-content:space-between;font-size:12px;padding:2px 0">
<span><?php echo ucfirst($is->status); ?></span>
<span><?php echo $is->count; ?> ($<?php echo number_format($is->total, 2); ?>)</span>
</div>
<?php endforeach; else: ?>
<div style="font-size:12px;color:#64748b">No invoices</div>
<?php endif; ?>
</div>
</div>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:10px;margin-bottom:20px">
<div class="card" style="margin-bottom:0;padding:14px">
<h5 style="margin-bottom:8px">Monthly Revenue (12 months)</h5>
<?php if (!empty($monthlyRevenue)): ?>
<div style="display:flex;flex-direction:column;gap:4px">
<?php foreach ($monthlyRevenue as $m): ?>
<div style="display:flex;justify-content:space-between;font-size:11px;padding:2px 0;border-bottom:1px solid rgba(255,255,255,.05)">
<span><?php echo $m->month; ?></span>
<span style="font-weight:600">$<?php echo number_format($m->total, 2); ?></span>
</div>
<?php endforeach; ?>
</div>
<?php else: ?><div style="font-size:12px;color:#64748b">No data</div><?php endif; ?>
</div>
<div class="card" style="margin-bottom:0;padding:14px">
<h5 style="margin-bottom:8px">Payment Methods</h5>
<?php if (!empty($paymentMethods)): foreach ($paymentMethods as $pm): ?>
<div style="display:flex;justify-content:space-between;font-size:11px;padding:3px 0;border-bottom:1px solid rgba(255,255,255,.05)">
<span><?php echo htmlspecialchars(ucfirst($pm->method)); ?> (<?php echo $pm->count; ?>)</span>
<span style="font-weight:600">$<?php echo number_format($pm->total, 2); ?></span>
</div>
<?php endforeach; else: ?><div style="font-size:12px;color:#64748b">No payments</div><?php endif; ?>
</div>
<div class="card" style="margin-bottom:0;padding:14px">
<h5 style="margin-bottom:8px">Top Customers by Revenue</h5>
<?php if (!empty($topCustomers)): $i = 1; foreach ($topCustomers as $tc): ?>
<div style="display:flex;justify-content:space-between;font-size:11px;padding:3px 0;border-bottom:1px solid rgba(255,255,255,.05)">
<span>#<?php echo $i++; ?> <?php echo htmlspecialchars($tc->username); ?><?php echo $tc->domain ? ' ('.htmlspecialchars($tc->domain).')' : ''; ?></span>
<span style="font-weight:600">$<?php echo number_format($tc->total_spent, 2); ?> (<?php echo $tc->payment_count; ?>)</span>
</div>
<?php endforeach; else: ?><div style="font-size:12px;color:#64748b">No data</div><?php endif; ?>
</div>
<div class="card" style="margin-bottom:0;padding:14px">
<h5 style="margin-bottom:8px">Product Sales</h5>
<?php if (!empty($productSales)): foreach ($productSales as $ps): ?>
<div style="display:flex;justify-content:space-between;font-size:11px;padding:3px 0;border-bottom:1px solid rgba(255,255,255,.05)">
<span><?php echo htmlspecialchars($ps->name ?: 'N/A'); ?> (<?php echo $ps->order_count; ?>)</span>
<span style="font-weight:600">$<?php echo number_format($ps->total_revenue, 2); ?></span>
</div>
<?php endforeach; else: ?><div style="font-size:12px;color:#64748b">No sales data</div><?php endif; ?>
</div>
<div class="card" style="margin-bottom:0;padding:14px">
<h5 style="margin-bottom:8px">Coupon Usage</h5>
<?php if (!empty($couponStats)): foreach ($couponStats as $cs): ?>
<div style="display:flex;justify-content:space-between;font-size:11px;padding:3px 0;border-bottom:1px solid rgba(255,255,255,.05)">
<span style="font-family:monospace;font-weight:600"><?php echo htmlspecialchars($cs->code); ?></span>
<span>Used: <?php echo $cs->used_count; ?>/<?php echo $cs->max_uses ?: '∞'; ?> (<?php echo $cs->type === 'percentage' ? $cs->value.'%' : '$'.$cs->value; ?>)</span>
</div>
<?php endforeach; else: ?><div style="font-size:12px;color:#64748b">No coupons used</div><?php endif; ?>
</div>
</div>