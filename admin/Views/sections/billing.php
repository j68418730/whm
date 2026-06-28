<style>
.section-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px}
.section-card{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:12px;padding:24px;text-align:center;text-decoration:none;color:#e0e0e0;transition:.2s}
.section-card:hover{transform:translateY(-3px);border-color:rgba(0,140,255,.3);box-shadow:0 8px 30px rgba(0,140,255,.08)}
.section-card .icon{font-size:36px;margin-bottom:8px}
.section-card .name{font-size:15px;font-weight:600;margin-bottom:4px}
.section-card .count{font-size:28px;font-weight:800;color:var(--accent);margin-bottom:2px}
.section-card .desc{font-size:11px;color:#64748b}
</style>

<h2>Billing</h2>
<p style="color:#64748b;margin-bottom:20px">Manage billing, products, orders, invoices, and payment gateways.</p>

<div class="section-grid">
<a href="/admin/billing" class="section-card"><div class="icon">📊</div><div class="name">Billing Dashboard</div><div class="desc">Billing overview & stats</div></a>
<a href="/admin/billing/cart" class="section-card"><div class="icon">🛒</div><div class="name">Shopping Cart</div><div class="desc">Cart embed & API integration</div></a>
<a href="/admin/billing/products" class="section-card"><div class="icon">🏷️</div><div class="name">Products</div><div class="desc">Product & service catalog</div></a>
<a href="/admin/billing/orders" class="section-card"><div class="icon">📋</div><div class="name">Orders</div><div class="desc">Customer orders</div></a>
<a href="/admin/billing/invoices" class="section-card"><div class="icon">🧾</div><div class="name">Invoices</div><div class="desc">Invoice management</div></a>
<a href="/admin/billing/payments" class="section-card"><div class="icon">💳</div><div class="name">Transactions</div><div class="desc">Payment transactions</div></a>
<a href="/admin/billing/credits" class="section-card"><div class="icon">💰</div><div class="name">Credits</div><div class="desc">Credit management</div></a>
<a href="/admin/billing/coupons" class="section-card"><div class="icon">🎟️</div><div class="name">Coupons</div><div class="desc">Discount coupons</div></a>
<a href="/admin/billing/taxes" class="section-card"><div class="icon">📄</div><div class="name">Taxes</div><div class="desc">Tax rules & rates</div></a>
<a href="/admin/billing/refunds" class="section-card"><div class="icon">↩️</div><div class="name">Refunds</div><div class="desc">Refund processing</div></a>
<a href="/admin/gateways" class="section-card"><div class="icon">🏦</div><div class="name">Payment Gateways</div><div class="desc">Payment processor config</div></a>
</div>
