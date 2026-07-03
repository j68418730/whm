<div style="max-width:800px;margin:0 auto">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
<h3 style="margin:0"><i class="bi bi-cart" style="color:var(--accent)"></i> Shopping Cart</h3>
<a href="/store" class="btn btn-sm secondary"><i class="bi bi-arrow-left"></i> Continue Shopping</a>
</div>
<?php if (empty($items)): ?>
<div class="card" style="padding:40px;text-align:center">
<div style="font-size:48px;color:rgba(255,255,255,.1);margin-bottom:12px"><i class="bi bi-cart-x"></i></div>
<p style="color:#64748b">Your cart is empty.</p>
<a href="/store" class="btn primary" style="margin-top:12px">Browse Products</a>
</div>
<?php else: ?>
<div class="card" style="padding:16px">
<?php foreach ($items as $i): ?>
<div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid rgba(255,255,255,.06)">
<div><strong><?php echo htmlspecialchars($i->name); ?></strong><br><span style="font-size:11px;color:#94a3b8">$<?php echo number_format($i->price,2); ?> each</span></div>
<div style="display:flex;align-items:center;gap:8px">
<form method="POST" action="/store/cart/update" style="display:flex;gap:4px;align-items:center">
<input type="hidden" name="id" value="<?php echo $i->id; ?>">
<input type="number" name="qty" value="<?php echo $i->qty; ?>" min="0" style="width:50px;padding:4px 6px;border-radius:4px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:12px;text-align:center">
<button type="submit" class="btn btn-sm secondary" style="font-size:9px;padding:3px 6px">Update</button>
</form>
<form method="POST" action="/store/cart/remove">
<input type="hidden" name="id" value="<?php echo $i->id; ?>">
<button type="submit" class="btn btn-sm" style="background:#ef4444;color:#fff;font-size:9px;padding:3px 6px"><i class="bi bi-trash"></i></button>
</form>
</div>
</div>
<?php endforeach; ?>
</div>
<div style="display:flex;justify-content:space-between;align-items:center;margin-top:16px;padding:16px;background:rgba(0,0,0,.15);border-radius:10px">
<div><span style="color:#94a3b8">Total:</span> <span style="font-size:24px;font-weight:700;color:var(--accent)">$<?php echo number_format($total,2); ?></span></div>
<a href="/store/checkout" class="btn primary" style="padding:10px 28px;font-size:14px"><i class="bi bi-credit-card"></i> Proceed to Checkout</a>
</div>
<?php endif; ?>
</div>
