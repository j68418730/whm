<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;margin-bottom:16px">
<h3 style="color:var(--accent)">Coupons</h3>
<a class="btn primary" onclick="document.getElementById('cpnForm').classList.toggle('hidden')">Add Coupon</a>
</div>
<div id="cpnForm" class="card hidden" style="max-width:500px;margin-bottom:20px">
<form method="POST" action="/admin/billing/coupons/store">
<div class="form-group"><label>Code</label><input name="code" required placeholder="SAVE20"></div>
<div class="form-group"><label>Type</label><select name="type"><option value="percentage">Percentage</option><option value="fixed">Fixed Amount</option></select></div>
<div class="form-group"><label>Value</label><input name="value" type="number" step="0.01" required></div>
<div class="form-group"><label>Max Uses (0 = unlimited)</label><input name="max_uses" type="number" value="0"></div>
<div class="form-group"><label>Min Total</label><input name="min_total" type="number" step="0.01" value="0.00"></div>
<div class="form-group"><label>Expires At</label><input name="expires_at" type="date"></div>
<button type="submit" class="btn primary">Create</button>
</form></div>
<table><tr><th>Code</th><th>Type</th><th>Value</th><th>Used</th><th>Expires</th><th>Status</th><th></th></tr>
<?php if (!empty($coupons)): foreach ($coupons as $c): ?>
<tr><td style="font-family:monospace"><?php echo htmlspecialchars($c->code); ?></td><td><?php echo $c->type; ?></td>
<td><?php echo $c->type === 'percentage' ? $c->value . '%' : '$' . number_format($c->value, 2); ?></td>
<td><?php echo $c->used_count; ?> / <?php echo $c->max_uses ?: '∞'; ?></td><td><?php echo $c->expires_at ?? '-'; ?></td>
<td><span class="status-badge status-<?php echo $c->is_active ? 'active' : 'terminated'; ?>"><?php echo $c->is_active ? 'Active' : 'Inactive'; ?></span></td>
<td><a href="/admin/billing/coupons/delete/<?php echo $c->id; ?>" class="btn btn-sm danger">Delete</a></td></tr>
<?php endforeach; else: ?><tr><td colspan="7" style="text-align:center;padding:20px;color:#64748b">No coupons created.</td></tr>
<?php endif; ?></table>
