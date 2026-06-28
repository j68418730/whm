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