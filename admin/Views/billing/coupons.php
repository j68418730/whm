<div style="display:flex;gap:8px;margin-bottom:12px">
<a class="btn primary" onclick="document.getElementById('cpnForm').classList.toggle('hidden')">+ Add Coupon</a>
</div>
<div id="cpnForm" class="card hidden" style="max-width:500px;margin-bottom:16px">
<form method="POST" action="/admin/billing/coupons/store">
<h3 style="color:var(--accent);margin-bottom:8px">New Coupon</h3>
<div class="form-group"><label>Code</label><input name="code" required placeholder="SAVE20"></div>
<div class="form-group"><label>Type</label><select name="type"><option value="percentage">Percentage</option><option value="fixed">Fixed Amount</option></select></div>
<div class="form-group"><label>Value</label><input name="value" type="number" step="0.01" required></div>
<div class="form-group"><label>Max Uses (0 = unlimited)</label><input name="max_uses" type="number" value="0"></div>
<div class="form-group"><label>Min Total</label><input name="min_total" type="number" step="0.01" value="0.00"></div>
<div class="form-group"><label>Expires At</label><input name="expires_at" type="date"></div>
<button type="submit" class="btn primary">Create</button>
</form></div>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:10px">
<?php if (!empty($coupons)): foreach ($coupons as $c): ?>
<div class="card" style="margin-bottom:0;padding:14px">
<div style="display:flex;justify-content:space-between">
<div style="font-weight:700;font-size:16px;font-family:monospace;color:var(--accent)"><?php echo htmlspecialchars($c->code); ?></div>
<span class="status-badge status-<?php echo $c->is_active ? 'active' : 'terminated'; ?>"><?php echo $c->is_active ? 'Active' : 'Inactive'; ?></span>
</div>
<div style="font-size:20px;font-weight:700;margin:4px 0"><?php echo $c->type === 'percentage' ? $c->value . '%' : '$' . number_format($c->value, 2); ?></div>
<div style="font-size:11px;color:#64748b">Used: <?php echo $c->used_count; ?> / <?php echo $c->max_uses ?: '∞'; ?> · Min: $<?php echo number_format($c->min_total ?? 0, 2); ?></div>
<div style="font-size:11px;color:#64748b">Expires: <?php echo $c->expires_at ?? 'Never'; ?></div>
<div style="margin-top:8px;display:flex;gap:4px">
<a class="btn btn-sm secondary" onclick="editCoupon(<?php echo $c->id; ?>, '<?php echo htmlspecialchars(addslashes($c->code)); ?>', '<?php echo $c->type; ?>', <?php echo $c->value; ?>, <?php echo $c->max_uses ?? 0; ?>, <?php echo $c->min_total ?? 0; ?>, '<?php echo $c->expires_at ?? ''; ?>', <?php echo $c->is_active; ?>)">✏ Edit</a>
<a href="/admin/billing/coupons/delete/<?php echo $c->id; ?>" class="btn btn-sm danger" onclick="return confirm('Delete?')">Delete</a>
</div>
</div>
<?php endforeach; else: ?>
<div class="card" style="text-align:center;padding:20px;grid-column:1/-1;color:#64748b">No coupons created.</div>
<?php endif; ?>
</div>

<div id="editCouponModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.7);align-items:center;justify-content:center" onclick="if(event.target===this)this.style.display='none'">
<div class="card" style="max-width:500px;margin:auto;position:relative;top:10%">
<h3 style="color:var(--accent);margin-bottom:8px">Edit Coupon</h3>
<form method="POST" action="" id="editCouponForm">
<div class="form-group"><label>Code</label><input name="code" id="ec_code" required></div>
<div class="form-group"><label>Type</label><select name="type" id="ec_type"><option value="percentage">Percentage</option><option value="fixed">Fixed Amount</option></select></div>
<div class="form-group"><label>Value</label><input name="value" id="ec_value" type="number" step="0.01" required></div>
<div class="form-group"><label>Max Uses (0=unlimited)</label><input name="max_uses" id="ec_max" type="number" value="0"></div>
<div class="form-group"><label>Min Total</label><input name="min_total" id="ec_min" type="number" step="0.01" value="0.00"></div>
<div class="form-group"><label>Expires At</label><input name="expires_at" id="ec_expires" type="date"></div>
<div class="form-group"><label>Active</label><select name="is_active" id="ec_active"><option value="1">Active</option><option value="0">Inactive</option></select></div>
<button type="submit" class="btn primary">Save</button>
<button type="button" class="btn secondary" onclick="document.getElementById('editCouponModal').style.display='none'">Cancel</button>
</form></div></div>
<script>
function editCoupon(id, code, type, value, max, min, expires, active) {
    document.getElementById('editCouponForm').action = '/admin/billing/coupons/update/' + id;
    document.getElementById('ec_code').value = code;
    document.getElementById('ec_type').value = type;
    document.getElementById('ec_value').value = value;
    document.getElementById('ec_max').value = max;
    document.getElementById('ec_min').value = min;
    document.getElementById('ec_expires').value = expires;
    document.getElementById('ec_active').value = active;
    document.getElementById('editCouponModal').style.display = 'flex';
}
</script>