<div style="display:flex;gap:4px;flex-wrap:wrap;margin-bottom:16px;border-bottom:1px solid rgba(255,255,255,.06);padding-bottom:8px">
<a href="/admin/billing" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">📊 Dashboard</a>
<a href="/admin/billing/products" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;background:rgba(0,191,255,.1);color:#00bfff;border-bottom:2px solid #008cff">📦 Products</a>
<a href="/admin/billing/orders" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">📋 Orders</a>
<a href="/admin/billing/services" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">🖥 Services</a>
<a href="/admin/billing/invoices" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">💰 Invoices</a>
<a href="/admin/billing/payments" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">💳 Payments</a>
<a href="/admin/billing/taxes" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">🏛️ Taxes</a>
<a href="/admin/billing/coupons" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">🎟️ Coupons</a>
<a href="/admin/billing/credits" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">🏦 Credits</a>
<a href="/admin/billing/refunds" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">↩️ Refunds</a>
<a href="/admin/billing/reports" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">📈 Reports</a>
</div>
<div style="display:flex;gap:8px;margin-bottom:12px">
<a class="btn primary" onclick="document.getElementById('prodForm').classList.toggle('hidden')">+ Add Product</a>
<a class="btn secondary" onclick="toggleSortMode()" id="sortToggleBtn">↕ Sort</a>
</div>
<div id="prodForm" class="card hidden" style="max-width:500px;margin-bottom:16px">
<form method="POST" action="/admin/billing/products/store">
<h3 style="color:var(--accent);margin-bottom:8px">New Product</h3>
<div class="form-group"><label>Name</label><input name="name" required></div>
<div class="form-group"><label>Description</label><textarea name="description" rows="2"></textarea></div>
<div class="form-group"><label>Type</label><select name="type"><option value="hosting">Hosting</option><option value="radio">Radio</option><option value="vps">VPS</option><option value="domain">Domain</option><option value="addon">Addon</option></select></div>
<div class="form-group" style="display:flex;gap:8px"><div style="flex:1"><label>Price</label><input name="price" type="number" step="0.01" value="0.00"></div><div style="flex:1"><label>Setup Fee</label><input name="setup_fee" type="number" step="0.01" value="0.00"></div></div>
<div class="form-group"><label>Billing Cycle</label><select name="billing_cycle"><option value="monthly">Monthly</option><option value="quarterly">Quarterly</option><option value="semiannual">Semi-Annual</option><option value="annual">Annual</option><option value="biennial">Biennial</option></select></div>
<button type="submit" class="btn primary">Create</button>
</form></div>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:10px" id="productList">
<?php if (!empty($products)): foreach ($products as $p): ?>
<div class="card" style="margin-bottom:0;padding:14px;cursor:grab" data-id="<?php echo $p->id; ?>">
<div style="display:flex;justify-content:space-between">
<div style="font-weight:600;font-size:14px"><?php echo htmlspecialchars($p->name); ?></div>
<div class="drag-handle" style="cursor:grab;color:#64748b;display:none">⠿</div>
</div>
<div style="font-size:11px;color:#64748b;margin-top:4px"><?php echo htmlspecialchars($p->description ?: ''); ?></div>
<div style="display:flex;gap:8px;margin-top:6px;flex-wrap:wrap">
<span class="status-badge status-<?php echo $p->is_active ? 'active' : 'terminated'; ?>" style="font-size:10px"><?php echo $p->is_active ? 'Active' : 'Inactive'; ?></span>
<span style="font-size:11px;color:#94a3b8"><?php echo $p->type; ?> · <?php echo $p->billing_cycle; ?></span>
</div>
<div style="font-size:13px;font-weight:600;margin-top:6px">$<?php echo number_format($p->price, 2); ?> /mo</div>
<div style="margin-top:6px;display:flex;gap:4px">
<a class="btn btn-sm secondary" onclick="editProduct(<?php echo $p->id; ?>, '<?php echo htmlspecialchars(addslashes($p->name)); ?>', '<?php echo htmlspecialchars(addslashes($p->description ?? '')); ?>', '<?php echo $p->type; ?>', <?php echo $p->price; ?>, <?php echo $p->setup_fee ?? 0; ?>, '<?php echo $p->billing_cycle; ?>', <?php echo $p->is_active; ?>)">✏ Edit</a>
<a href="/admin/billing/products/delete/<?php echo $p->id; ?>" class="btn btn-sm danger" onclick="return confirm('Delete?')">Delete</a>
</div>
</div>
<?php endforeach; else: ?>
<div class="card" style="text-align:center;padding:20px;grid-column:1/-1;color:#64748b">No products yet.</div>
<?php endif; ?>
</div>

<div id="editProductModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.7);align-items:center;justify-content:center" onclick="if(event.target===this)this.style.display='none'">
<div class="card" style="max-width:500px;margin:auto;position:relative;top:10%">
<h3 style="color:var(--accent);margin-bottom:8px">Edit Product</h3>
<form method="POST" action="" id="editProductForm">
<div class="form-group"><label>Name</label><input name="name" id="edit_name" required></div>
<div class="form-group"><label>Description</label><textarea name="description" id="edit_desc" rows="2"></textarea></div>
<div class="form-group" style="display:flex;gap:8px"><div style="flex:1"><label>Type</label><select name="type" id="edit_type"><option value="hosting">Hosting</option><option value="radio">Radio</option><option value="vps">VPS</option><option value="domain">Domain</option><option value="addon">Addon</option></select></div>
<div style="flex:1"><label>Cycle</label><select name="billing_cycle" id="edit_cycle"><option value="monthly">Monthly</option><option value="quarterly">Quarterly</option><option value="semiannual">Semi-Annual</option><option value="annual">Annual</option><option value="biennial">Biennial</option></select></div></div>
<div class="form-group" style="display:flex;gap:8px"><div style="flex:1"><label>Price</label><input name="price" id="edit_price" type="number" step="0.01"></div><div style="flex:1"><label>Setup Fee</label><input name="setup_fee" id="edit_setup" type="number" step="0.01"></div></div>
<div class="form-group"><label>Active</label><select name="is_active" id="edit_active"><option value="1">Active</option><option value="0">Inactive</option></select></div>
<button type="submit" class="btn primary">Save</button>
<button type="button" class="btn secondary" onclick="document.getElementById('editProductModal').style.display='none'">Cancel</button>
</form></div></div>

<script>
function editProduct(id, name, desc, type, price, setup, cycle, active) {
    document.getElementById('editProductForm').action = '/admin/billing/products/update/' + id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_desc').value = desc;
    document.getElementById('edit_type').value = type;
    document.getElementById('edit_price').value = price;
    document.getElementById('edit_setup').value = setup;
    document.getElementById('edit_cycle').value = cycle;
    document.getElementById('edit_active').value = active;
    document.getElementById('editProductModal').style.display = 'flex';
}

var sortMode = false;
function toggleSortMode() {
    sortMode = !sortMode;
    document.getElementById('sortToggleBtn').style.background = sortMode ? 'rgba(0,191,255,.2)' : '';
    document.querySelectorAll('.drag-handle').forEach(function(h) { h.style.display = sortMode ? 'inline' : 'none'; });
    document.querySelectorAll('#productList > .card').forEach(function(c) { c.style.cursor = sortMode ? 'grab' : 'default'; });
    if (!sortMode) saveSortOrder();
}
function saveSortOrder() {
    var ids = [];
    document.querySelectorAll('#productList > .card[data-id]').forEach(function(c) { ids.push(c.getAttribute('data-id')); });
    var x = new XMLHttpRequest();
    x.open('POST', '/admin/billing/products/sort', true);
    x.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    x.onload = function() { if (x.status === 200) showToast('Sort order saved'); };
    x.send('ids=' + ids.join(','));
}
// HTML5 drag-and-drop sort
var dragEl;
document.addEventListener('dragstart', function(e) {
    if (!sortMode) return;
    dragEl = e.target.closest('.card[data-id]');
    if (dragEl) e.dataTransfer.effectAllowed = 'move';
});
document.addEventListener('dragover', function(e) {
    if (!sortMode) return;
    var target = e.target.closest('.card[data-id]');
    if (target && target !== dragEl) {
        var rect = target.getBoundingClientRect();
        var after = e.clientY > rect.top + rect.height / 2;
        var parent = target.parentNode;
        if (after) parent.insertBefore(dragEl, target.nextSibling);
        else parent.insertBefore(dragEl, target);
    }
});
document.addEventListener('dragend', function() { dragEl = null; });
// Enable drag on product cards
document.querySelectorAll('#productList > .card').forEach(function(c) { c.setAttribute('draggable', 'true'); });
</script>