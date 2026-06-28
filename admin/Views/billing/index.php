<div style="display:flex;gap:4px;flex-wrap:wrap;margin-bottom:16px;border-bottom:1px solid rgba(255,255,255,.06);padding-bottom:8px">
<a href="/admin/billing" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;background:rgba(0,191,255,.1);color:#00bfff;border-bottom:2px solid #008cff">📊 Dashboard</a>
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
<a href="/admin/billing/reports" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">📈 Reports</a>
</div>
<div class="stats-grid" style="margin-bottom:20px">
<div class="stat-card"><h3>Total Collected</h3><div class="value">$<?php echo number_format($totalCollected ?? 0, 2); ?></div></div>
<div class="stat-card"><h3>Outstanding</h3><div class="value" style="color:#f87171">$<?php echo number_format($outstandingBalance ?? 0, 2); ?></div></div>
<div class="stat-card"><h3>Monthly Recurring</h3><div class="value" style="color:#4ade80">$<?php echo number_format($monthlyRecurring ?? 0, 2); ?></div></div>
<div class="stat-card"><h3>Active Services</h3><div class="value"><?php echo $activeServices ?? 0; ?></div></div>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px">
<a href="/admin/billing/products" class="card" style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px 16px;text-align:center;text-decoration:none;gap:6px;transition:.15s">
<div style="font-size:32px">📦</div>
<div style="font-size:14px;font-weight:600;color:var(--text-primary)">Products</div>
<div style="font-size:24px;font-weight:700;color:var(--accent)"><?php echo $productCount ?? 0; ?></div>
</a>
<a href="/admin/billing/orders" class="card" style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px 16px;text-align:center;text-decoration:none;gap:6px;transition:.15s">
<div style="font-size:32px">📋</div>
<div style="font-size:14px;font-weight:600;color:var(--text-primary)">Orders</div>
<div style="font-size:24px;font-weight:700;color:var(--accent)"><?php echo $orderCount ?? 0; ?></div>
</a>
<a href="/admin/billing/services" class="card" style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px 16px;text-align:center;text-decoration:none;gap:6px;transition:.15s">
<div style="font-size:32px">🖥</div>
<div style="font-size:14px;font-weight:600;color:var(--text-primary)">Services</div>
<div style="font-size:24px;font-weight:700;color:var(--accent)"><?php echo $serviceCount ?? 0; ?></div>
</a>
<a href="/admin/billing/invoices" class="card" style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px 16px;text-align:center;text-decoration:none;gap:6px;transition:.15s">
<div style="font-size:32px">💰</div>
<div style="font-size:14px;font-weight:600;color:var(--text-primary)">Invoices</div>
<div style="font-size:24px;font-weight:700;color:var(--accent)"><?php echo $invoiceCount ?? 0; ?></div>
</a>
<a href="/admin/billing/payments" class="card" style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px 16px;text-align:center;text-decoration:none;gap:6px;transition:.15s">
<div style="font-size:32px">💳</div>
<div style="font-size:14px;font-weight:600;color:var(--text-primary)">Payments</div>
<div style="font-size:24px;font-weight:700;color:var(--accent)"><?php echo $paymentCount ?? 0; ?></div>
</a>
<a href="/admin/billing/taxes" class="card" style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px 16px;text-align:center;text-decoration:none;gap:6px;transition:.15s">
<div style="font-size:32px">🏛️</div>
<div style="font-size:14px;font-weight:600;color:var(--text-primary)">Taxes</div>
<div style="font-size:24px;font-weight:700;color:var(--accent)"><?php echo $taxCount ?? 0; ?></div>
</a>
<a href="/admin/billing/coupons" class="card" style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px 16px;text-align:center;text-decoration:none;gap:6px;transition:.15s">
<div style="font-size:32px">🎟️</div>
<div style="font-size:14px;font-weight:600;color:var(--text-primary)">Coupons</div>
<div style="font-size:24px;font-weight:700;color:var(--accent)"><?php echo $couponCount ?? 0; ?></div>
</a>
<a href="/admin/billing/credits" class="card" style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px 16px;text-align:center;text-decoration:none;gap:6px;transition:.15s">
<div style="font-size:32px">🏦</div>
<div style="font-size:14px;font-weight:600;color:var(--text-primary)">Credits</div>
<div style="font-size:24px;font-weight:700;color:var(--accent)"><?php echo $creditCount ?? 0; ?></div>
</a>
<a href="/admin/billing/refunds" class="card" style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px 16px;text-align:center;text-decoration:none;gap:6px;transition:.15s">
<div style="font-size:32px">↩️</div>
<div style="font-size:14px;font-weight:600;color:var(--text-primary)">Refunds</div>
<div style="font-size:24px;font-weight:700;color:var(--accent)"><?php echo $refundCount ?? 0; ?></div>
</a>
</div>