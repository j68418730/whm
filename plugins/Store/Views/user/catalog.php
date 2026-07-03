<div style="max-width:1200px;margin:0 auto">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:8px">
<div><h3 style="margin:0"><i class="bi bi-shop" style="color:var(--accent)"></i> Store</h3></div>
<a href="/store/cart" class="btn btn-sm secondary"><i class="bi bi-cart"></i> Cart <?php if ($cartCount > 0): ?><span class="badge"><?php echo $cartCount; ?></span><?php endif; ?></a>
</div>
<?php if (!empty($featured)): ?>
<h4 style="font-size:13px;margin-bottom:10px">Featured Products</h4>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px;margin-bottom:24px">
<?php foreach ($featured as $p): ?>
<div class="card" style="padding:14px;text-align:center">
<div style="font-size:32px;margin-bottom:8px;color:var(--accent)"><i class="bi bi-box-seam"></i></div>
<h5 style="font-size:13px"><?php echo htmlspecialchars($p->name); ?></h5>
<p style="font-size:11px;color:#94a3b8;margin:4px 0"><?php echo htmlspecialchars($p->short_description ?? ''); ?></p>
<div style="font-size:18px;font-weight:700;color:var(--accent);margin:8px 0">$<?php echo number_format($p->price, 2); ?></div>
<a href="/store/product/<?php echo $p->slug; ?>" class="btn btn-sm primary">View Details</a>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
<?php if (!empty($categories)): ?>
<h4 style="font-size:13px;margin-bottom:10px">Categories</h4>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:10px;margin-bottom:24px">
<?php foreach ($categories as $c): ?>
<a href="/store/category/<?php echo $c->slug; ?>" class="card" style="padding:16px;text-align:center;text-decoration:none;color:#e0e0e0">
<div style="font-size:28px;color:var(--accent);margin-bottom:6px"><i class="bi <?php echo $c->icon ?? 'bi-folder'; ?>"></i></div>
<strong style="font-size:13px"><?php echo htmlspecialchars($c->name); ?></strong>
</a>
<?php endforeach; ?>
</div>
<?php endif; ?>
<?php if (!empty($new)): ?>
<h4 style="font-size:13px;margin-bottom:10px">New Arrivals</h4>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px">
<?php foreach ($new as $p): ?>
<div class="card" style="padding:14px;text-align:center">
<div style="font-size:32px;margin-bottom:8px;color:var(--accent)"><i class="bi bi-box-seam"></i></div>
<h5 style="font-size:13px"><?php echo htmlspecialchars($p->name); ?></h5>
<p style="font-size:11px;color:#94a3b8;margin:4px 0"><?php echo htmlspecialchars($p->short_description ?? ''); ?></p>
<div style="font-size:18px;font-weight:700;color:var(--accent);margin:8px 0">$<?php echo number_format($p->price, 2); ?></div>
<a href="/store/product/<?php echo $p->slug; ?>" class="btn btn-sm primary">View Details</a>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
</div>
