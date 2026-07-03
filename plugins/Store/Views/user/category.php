<div style="max-width:1000px;margin:0 auto">
<div style="margin-bottom:12px"><a href="/store" class="btn btn-sm secondary"><i class="bi bi-arrow-left"></i> Back to Store</a></div>
<h3 style="margin-bottom:16px"><?php echo htmlspecialchars($category->name); ?></h3>
<?php if (empty($products)): ?>
<p style="color:#64748b">No products in this category.</p>
<?php else: ?>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:14px">
<?php foreach ($products as $p): ?>
<div class="card" style="padding:16px;text-align:center">
<div style="font-size:36px;margin-bottom:8px;color:var(--accent)"><i class="bi bi-box-seam"></i></div>
<h5 style="font-size:14px"><?php echo htmlspecialchars($p->name); ?></h5>
<p style="font-size:11px;color:#94a3b8;margin:4px 0"><?php echo htmlspecialchars($p->short_description ?? ''); ?></p>
<div style="font-size:20px;font-weight:700;color:var(--accent);margin:8px 0">$<?php echo number_format($p->price, 2); ?></div>
<a href="/store/product/<?php echo $p->slug; ?>" class="btn btn-sm primary">View Details</a>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
</div>
