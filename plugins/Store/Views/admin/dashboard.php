<div class="card" style="padding:20px">
<h3 style="margin-bottom:12px"><i class="bi bi-shop" style="color:var(--accent)"></i> Store Dashboard</h3>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:12px;margin-bottom:16px">
<div class="card" style="padding:14px;text-align:center;background:rgba(10,132,255,.06);border-color:rgba(10,132,255,.1)">
<strong style="font-size:28px"><?php echo $stats['products']; ?></strong><br><span style="font-size:11px;color:#94a3b8">Products</span>
</div>
<div class="card" style="padding:14px;text-align:center;background:rgba(139,92,246,.06);border-color:rgba(139,92,246,.1)">
<strong style="font-size:28px"><?php echo $stats['categories']; ?></strong><br><span style="font-size:11px;color:#94a3b8">Categories</span>
</div>
<div class="card" style="padding:14px;text-align:center;background:rgba(48,209,88,.06);border-color:rgba(48,209,88,.1)">
<strong style="font-size:28px"><?php echo $stats['orders']; ?></strong><br><span style="font-size:11px;color:#94a3b8">Orders</span>
</div>
<div class="card" style="padding:14px;text-align:center;background:rgba(255,159,10,.06);border-color:rgba(255,159,10,.1)">
<strong style="font-size:28px">$<?php echo number_format($stats['revenue'],0); ?></strong><br><span style="font-size:11px;color:#94a3b8">Revenue</span>
</div>
</div>
<div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap">
<a href="/admin/store/products" class="btn btn-sm primary">Manage Products</a>
<a href="/admin/store/products/create" class="btn btn-sm secondary">Add Product</a>
<a href="/admin/store/categories" class="btn btn-sm secondary">Categories</a>
<a href="/admin/store/orders" class="btn btn-sm secondary">Orders <?php if ($stats['pending'] > 0): ?><span class="badge" style="background:#fbbf24;color:#000"><?php echo $stats['pending']; ?></span><?php endif; ?></a>
</div>
<?php if (!empty($recent)): ?>
<h4 style="font-size:12px;margin-bottom:8px">Recent Orders</h4>
<div style="font-size:11px">
<?php foreach ($recent as $o): ?>
<div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid rgba(255,255,255,.04)">
<span><a href="/admin/store/orders/<?php echo $o->id; ?>" style="color:var(--accent)">#<?php echo $o->id; ?></a> — $<?php echo number_format($o->total,2); ?></span>
<span style="color:#64748b"><?php echo $o->status; ?> &middot; <?php echo date("M j", strtotime($o->created_at)); ?></span>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
</div>
