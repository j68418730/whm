<div class="stats-grid" style="margin-bottom:20px">
<div class="stat-card"><h3>Total Revenue</h3><div class="value">$<?php echo number_format($totalRevenue ?? 0, 2); ?></div></div>
<div class="stat-card"><h3>Active Services</h3><div class="value"><?php echo $activeServices ?? 0; ?></div></div>
<div class="stat-card"><h3>Pending Orders</h3><div class="value"><?php echo $pendingOrders ?? 0; ?></div></div>
<div class="stat-card"><h3>Invoices</h3><div class="value"><?php echo $totalInvoices ?? 0; ?></div></div>
</div>
<div class="page-grid" style="margin-bottom:20px">
<a href="/admin/billing/products" class="action-card"><div class="icon">📦</div><div class="name">Products</div></a>
<a href="/admin/billing/orders" class="action-card"><div class="icon">📋</div><div class="name">Orders</div></a>
<a href="/admin/billing/services" class="action-card"><div class="icon">🖥</div><div class="name">Services</div></a>
<a href="/admin/billing/invoices" class="action-card"><div class="icon">💰</div><div class="name">Invoices</div></a>
<a href="/admin/billing/payments" class="action-card"><div class="icon">💳</div><div class="name">Payments</div></a>
<a href="/admin/billing/taxes" class="action-card"><div class="icon">🏛️</div><div class="name">Taxes</div></a>
<a href="/admin/billing/coupons" class="action-card"><div class="icon">🎟️</div><div class="name">Coupons</div></a>
<a href="/admin/billing/credits" class="action-card"><div class="icon">🏦</div><div class="name">Credits</div></a>
<a href="/admin/billing/refunds" class="action-card"><div class="icon">↩️</div><div class="name">Refunds</div></a>
</div>