<div style="max-width:900px;margin:0 auto">
<div style="margin-bottom:12px"><a href="/store" class="btn btn-sm secondary"><i class="bi bi-arrow-left"></i> Back to Store</a></div>
<div class="card" style="padding:24px">
<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">
<div style="background:rgba(0,0,0,.2);border-radius:12px;display:flex;align-items:center;justify-content:center;min-height:300px">
<?php if (!empty($product->images_arr)): ?>
<img src="<?php echo htmlspecialchars($product->images_arr[0]); ?>" alt="<?php echo htmlspecialchars($product->name); ?>" style="max-width:100%;max-height:300px;border-radius:8px">
<?php else: ?>
<div style="font-size:64px;color:rgba(255,255,255,.1)"><i class="bi bi-box-seam"></i></div>
<?php endif; ?>
</div>
<div>
<h3 style="margin-bottom:8px"><?php echo htmlspecialchars($product->name); ?></h3>
<?php if ($product->compare_price): ?>
<div style="font-size:14px;color:#94a3b8"><span style="text-decoration:line-through">$<?php echo number_format($product->compare_price,2); ?></span></div>
<?php endif; ?>
<div style="font-size:28px;font-weight:700;color:var(--accent);margin:8px 0">$<?php echo number_format($product->price,2); ?></div>
<p style="color:#94a3b8;font-size:13px;margin-bottom:12px"><?php echo nl2br(htmlspecialchars($product->description ?? '')); ?></p>
<form method="POST" action="/store/cart/add" style="display:flex;gap:8px;align-items:center;margin-bottom:12px">
<input type="hidden" name="product_id" value="<?php echo $product->id; ?>">
<input type="number" name="qty" value="1" min="1" style="width:60px;padding:6px 8px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:13px;text-align:center">
<button type="submit" class="btn primary" style="padding:8px 24px;font-size:13px"><i class="bi bi-cart-plus"></i> Add to Cart</button>
</form>
<div style="font-size:11px;color:#64748b">
<span>Type: <?php echo $product->type; ?></span> &middot;
<span>SKU: <?php echo htmlspecialchars($product->sku ?: 'N/A'); ?></span>
</div>
</div>
</div>
</div>
<?php if (!empty($related)): ?>
<h4 style="font-size:13px;margin:20px 0 10px">Related Products</h4>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px">
<?php foreach ($related as $p): ?>
<div class="card" style="padding:12px;text-align:center">
<div style="font-size:28px;margin-bottom:6px;color:var(--accent)"><i class="bi bi-box-seam"></i></div>
<h5 style="font-size:12px"><?php echo htmlspecialchars($p->name); ?></h5>
<div style="font-size:16px;font-weight:700;color:var(--accent)">$<?php echo number_format($p->price,2); ?></div>
<a href="/store/product/<?php echo $p->slug; ?>" class="btn btn-sm secondary" style="margin-top:6px">View</a>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
</div>
