<div style="display:flex;gap:4px;flex-wrap:wrap;margin-bottom:16px;border-bottom:1px solid rgba(255,255,255,.06);padding-bottom:8px">
<a href="/admin/billing" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">📊 Dashboard</a>
<a href="/admin/billing/cart" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">🛒 Cart</a>
<a href="/admin/billing/products" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">📦 Products</a>
<a href="/admin/billing/orders" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;background:rgba(0,191,255,.1);color:#00bfff;border-bottom:2px solid #008cff">📋 Orders</a>
<a href="/admin/billing/services" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">🖥 Services</a>
<a href="/admin/billing/invoices" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">💰 Invoices</a>
<a href="/admin/billing/payments" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">💳 Payments</a>
<a href="/admin/billing/taxes" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">🏛️ Taxes</a>
<a href="/admin/billing/coupons" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">🎟️ Coupons</a>
<a href="/admin/billing/credits" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">🏦 Credits</a>
<a href="/admin/billing/refunds" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">↩️ Refunds</a>
<a href="/admin/billing/reports" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">📈 Reports</a>
</div>
<h3 style="color:var(--accent);margin-bottom:12px">Orders</h3>
<div style="display:flex;gap:8px;margin-bottom:12px">
<a class="btn primary" onclick="document.getElementById('ordForm').classList.toggle('hidden')">+ New Order</a>
</div>
<div id="ordForm" class="card hidden" style="max-width:600px;margin-bottom:16px">
<form method="POST" action="/admin/billing/orders/store">
<h3 style="color:var(--accent);margin-bottom:8px">Create Order from Product</h3>
<div class="form-group"><label>User</label>
<select name="user_id" id="ord_user" required>
<option value="">-- Select User --</option>
<?php foreach ($userMap as $h): ?>
<option value="<?php echo $h->id; ?>"><?php echo htmlspecialchars($h->username . ' (' . ($h->domain ?? '') . ')'); ?></option>
<?php endforeach; ?>
</select></div>
<div class="form-group"><label>Product</label>
<select name="product_id" id="ord_product" onchange="updateOrderProduct()">
<option value="">-- Select Product (optional) --</option>
<?php foreach ($products as $p): ?>
<option value="<?php echo $p->id; ?>" data-price="<?php echo $p->price; ?>" data-cycle="<?php echo htmlspecialchars($p->billing_cycle ?? ''); ?>"><?php echo htmlspecialchars($p->name); ?> — $<?php echo number_format($p->price, 2); ?>/<?php echo $p->billing_cycle ?? 'once'; ?></option>
<?php endforeach; ?>
</select></div>
<div class="form-group"><label>Hosting Package (optional)</label>
<select name="package_id">
<option value="">-- No Package --</option>
<?php foreach ($packages as $pkg): ?>
<option value="<?php echo $pkg->id; ?>"><?php echo htmlspecialchars($pkg->name); ?> (<?php echo htmlspecialchars($pkg->type); ?>)</option>
<?php endforeach; ?>
</select></div>
<div class="form-group" style="display:flex;gap:8px">
<div style="flex:1"><label>Total</label><input name="total" id="ord_total" type="number" step="0.01" required></div>
</div>
<div class="form-group"><label>Description</label><textarea name="description" id="ord_desc" rows="2" placeholder="Order description (auto-filled from product)"></textarea></div>
<button type="submit" class="btn primary">Create Order</button>
</form></div>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:10px">
<?php if (!empty($orders)): foreach ($orders as $o): ?>
<div class="card" style="margin-bottom:0;padding:14px">
<div style="display:flex;justify-content:space-between;align-items:start">
<div><span style="font-weight:600;font-size:14px">#<?php echo $o->id; ?></span>
<span class="status-badge status-<?php echo $o->status === 'active' ? 'active' : ($o->status === 'cancelled' ? 'terminated' : ''); ?>" style="margin-left:6px;font-size:10px"><?php echo $o->status; ?></span></div>
<span style="font-size:11px;color:#64748b"><?php echo $o->created_at; ?></span>
</div>
<div style="font-size:12px;color:#94a3b8;margin-top:4px"><?php echo htmlspecialchars($userMap[$o->user_id]->username ?? "User #{$o->user_id}"); ?><?php if (!empty($userMap[$o->user_id]->domain)): echo ' · ' . htmlspecialchars($userMap[$o->user_id]->domain); endif; ?></div>
<div style="font-size:12px;color:#94a3b8">Product: <?php echo $o->product_id ? '#' . $o->product_id : '-'; ?><?php if ($o->package_id): ?> · Package: #<?php echo $o->package_id; ?><?php endif; ?></div>
<div style="font-size:14px;font-weight:600;margin-top:6px">$<?php echo number_format($o->total, 2); ?></div>
<div style="font-size:11px;color:#64748b;margin-top:2px"><?php echo htmlspecialchars($o->description ?? ''); ?></div>
<div style="margin-top:8px"><form method="POST" action="/admin/billing/orders/update/<?php echo $o->id; ?>" style="display:flex;gap:4px">
<select name="status" style="flex:1"><option value="pending" <?php echo $o->status==='pending'?'selected':''; ?>>Pending</option><option value="active" <?php echo $o->status==='active'?'selected':''; ?>>Active</option><option value="suspended" <?php echo $o->status==='suspended'?'selected':''; ?>>Suspended</option><option value="cancelled" <?php echo $o->status==='cancelled'?'selected':''; ?>>Cancelled</option><option value="invoiced" <?php echo $o->status==='invoiced'?'selected':''; ?>>Invoiced</option></select>
<button type="submit" class="btn btn-sm primary">Update</button></form></div>
</div>
<?php endforeach; else: ?>
<div class="card" style="text-align:center;padding:20px;grid-column:1/-1;color:#64748b">No orders yet.</div>
<?php endif; ?>
</div>
<script>
function updateOrderProduct() {
    var sel = document.getElementById('ord_product');
    var opt = sel.options[sel.selectedIndex];
    if (opt && opt.value) {
        document.getElementById('ord_total').value = opt.getAttribute('data-price') || '0.00';
        document.getElementById('ord_desc').value = opt.text;
    }
}
</script>