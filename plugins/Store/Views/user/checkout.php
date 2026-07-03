<div style="max-width:800px;margin:0 auto">
<h3 style="margin-bottom:16px"><i class="bi bi-credit-card" style="color:var(--accent)"></i> Checkout</h3>
<div style="display:grid;grid-template-columns:1.5fr 1fr;gap:16px">
<div class="card" style="padding:20px">
<h4 style="font-size:14px;margin-bottom:12px">Shipping Information</h4>
<form method="POST" action="/store/checkout/place">
<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px">
<div><label style="font-size:11px;color:#94a3b8">First Name</label><input name="first_name" value="<?php echo $user ? htmlspecialchars($user->first_name ?? '') : ''; ?>" required style="width:100%;padding:8px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:12px"></div>
<div><label style="font-size:11px;color:#94a3b8">Last Name</label><input name="last_name" value="<?php echo $user ? htmlspecialchars($user->last_name ?? '') : ''; ?>" required style="width:100%;padding:8px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:12px"></div>
</div>
<div style="margin-bottom:10px"><label style="font-size:11px;color:#94a3b8">Email</label><input name="email" type="email" value="<?php echo $user ? htmlspecialchars($user->email ?? '') : ''; ?>" required style="width:100%;padding:8px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:12px"></div>
<div style="margin-bottom:10px"><label style="font-size:11px;color:#94a3b8">Phone</label><input name="phone" style="width:100%;padding:8px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:12px"></div>
<div style="margin-bottom:10px"><label style="font-size:11px;color:#94a3b8">Address Line 1</label><input name="address_line1" required style="width:100%;padding:8px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:12px"></div>
<div style="margin-bottom:10px"><label style="font-size:11px;color:#94a3b8">Address Line 2</label><input name="address_line2" style="width:100%;padding:8px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:12px"></div>
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:10px">
<div><label style="font-size:11px;color:#94a3b8">City</label><input name="city" required style="width:100%;padding:8px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:12px"></div>
<div><label style="font-size:11px;color:#94a3b8">State</label><input name="state" style="width:100%;padding:8px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:12px"></div>
<div><label style="font-size:11px;color:#94a3b8">ZIP</label><input name="zip" style="width:100%;padding:8px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:12px"></div>
</div>
<div style="margin-bottom:10px"><label style="font-size:11px;color:#94a3b8">Country</label><input name="country" value="US" required style="width:100%;padding:8px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:12px"></div>
<div style="margin-bottom:10px"><label style="font-size:11px;color:#94a3b8">Order Notes (optional)</label><textarea name="notes" rows="2" style="width:100%;padding:8px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:12px;resize:vertical"></textarea></div>
<button type="submit" class="btn primary" style="width:100%;padding:12px;font-size:14px;font-weight:600"><i class="bi bi-check-lg"></i> Place Order — $<?php echo number_format($total,2); ?></button>
</form>
</div>
<div>
<div class="card" style="padding:16px;margin-bottom:12px">
<h4 style="font-size:13px;margin-bottom:10px">Order Summary</h4>
<?php foreach ($items as $i): ?>
<div style="display:flex;justify-content:space-between;font-size:12px;padding:6px 0;border-bottom:1px solid rgba(255,255,255,.04)">
<span><?php echo htmlspecialchars($i->name); ?> x<?php echo $i->qty; ?></span>
<span>$<?php echo number_format($i->price * $i->qty,2); ?></span>
</div>
<?php endforeach; ?>
<div style="display:flex;justify-content:space-between;font-size:16px;font-weight:700;color:var(--accent);margin-top:10px">
<span>Total</span><span>$<?php echo number_format($total,2); ?></span>
</div>
</div>
<div class="card" style="padding:14px;font-size:11px;color:#94a3b8">
<i class="bi bi-shield-lock"></i> Your information is secure. We use encryption to protect your data.
</div>
</div>
</div>
</div>
