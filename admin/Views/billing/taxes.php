<div style="display:flex;gap:4px;flex-wrap:wrap;margin-bottom:16px;border-bottom:1px solid rgba(255,255,255,.06);padding-bottom:8px">
<a href="/admin/billing" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">📊 Dashboard</a>
<a href="/admin/billing/cart" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">🛒 Cart</a>
<a href="/admin/billing/products" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">📦 Products</a>
<a href="/admin/billing/orders" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">📋 Orders</a>
<a href="/admin/billing/services" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">🖥 Services</a>
<a href="/admin/billing/invoices" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">💰 Invoices</a>
<a href="/admin/billing/payments" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">💳 Payments</a>
<a href="/admin/billing/taxes" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;background:rgba(0,191,255,.1);color:#00bfff;border-bottom:2px solid #008cff">🏛️ Taxes</a>
<a href="/admin/billing/coupons" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">🎟️ Coupons</a>
<a href="/admin/billing/credits" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">🏦 Credits</a>
<a href="/admin/billing/refunds" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">↩️ Refunds</a>
<a href="/admin/billing/reports" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">📈 Reports</a>
</div>
<div style="display:flex;gap:8px;margin-bottom:12px">
<a class="btn primary" onclick="document.getElementById('taxForm').classList.toggle('hidden')">+ Add Tax Rate</a>
</div>
<div id="taxForm" class="card hidden" style="max-width:500px;margin-bottom:16px">
<form method="POST" action="/admin/billing/taxes/store">
<h3 style="color:var(--accent);margin-bottom:8px">New Tax Rate</h3>
<div class="form-group"><label>Name</label><input name="name" required placeholder="VAT 20%"></div>
<div class="form-group"><label>Rate (%)</label><input name="rate" type="number" step="0.01" value="0.00"></div>
<div class="form-group"><label>Country Code</label><input name="country" placeholder="US"></div>
<button type="submit" class="btn primary">Add</button>
</form></div>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:10px">
<?php if (!empty($taxes)): foreach ($taxes as $t): ?>
<div class="card" style="margin-bottom:0;padding:14px">
<div style="font-weight:600;font-size:14px"><?php echo htmlspecialchars($t->name); ?></div>
<div style="font-size:22px;font-weight:700;color:var(--accent);margin:6px 0"><?php echo ($t->rate * 100); ?>%</div>
<div style="font-size:11px;color:#64748b">Country: <?php echo $t->country ?: 'All'; ?></div>
<div style="display:flex;justify-content:space-between;margin-top:6px;gap:4px">
<span class="status-badge status-<?php echo $t->is_active ? 'active' : 'terminated'; ?>"><?php echo $t->is_active ? 'Active' : 'Inactive'; ?></span>
<div style="display:flex;gap:4px">
<a class="btn btn-sm secondary" onclick="editTax(<?php echo $t->id; ?>, '<?php echo htmlspecialchars(addslashes($t->name)); ?>', <?php echo $t->rate; ?>, '<?php echo htmlspecialchars(addslashes($t->country ?? '')); ?>', <?php echo $t->is_active ? 1 : 0; ?>)">✏ Edit</a>
<a href="/admin/billing/taxes/delete/<?php echo $t->id; ?>" class="btn btn-sm danger">Delete</a>
</div>
</div>
</div>
<?php endforeach; else: ?>
<div class="card" style="text-align:center;padding:20px;grid-column:1/-1;color:#64748b">No tax rates configured.</div>
<?php endif; ?>
</div>

<div id="editTaxModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.7);align-items:center;justify-content:center" onclick="if(event.target===this)this.style.display='none'">
<div class="card" style="max-width:500px;margin:auto;position:relative;top:10%">
<h3 style="color:var(--accent);margin-bottom:8px">Edit Tax Rate</h3>
<form method="POST" action="" id="editTaxForm">
<div class="form-group"><label>Name</label><input name="name" id="et_name" required></div>
<div class="form-group"><label>Rate (%)</label><input name="rate" id="et_rate" type="number" step="0.01"></div>
<div class="form-group"><label>Country Code</label><input name="country" id="et_country"></div>
<div class="form-group"><label>Active</label><select name="is_active" id="et_active"><option value="1">Active</option><option value="0">Inactive</option></select></div>
<button type="submit" class="btn primary">Save</button>
<button type="button" class="btn secondary" onclick="document.getElementById('editTaxModal').style.display='none'">Cancel</button>
</form></div></div>

<script>
function editTax(id, name, rate, country, active) {
    document.getElementById('editTaxForm').action = '/admin/billing/taxes/update/' + id;
    document.getElementById('et_name').value = name;
    document.getElementById('et_rate').value = rate;
    document.getElementById('et_country').value = country;
    document.getElementById('et_active').value = active;
    document.getElementById('editTaxModal').style.display = 'flex';
}
</script>