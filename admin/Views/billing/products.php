<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;margin-bottom:16px">
<h3 style="color:var(--accent)">Products</h3>
<a class="btn primary" onclick="document.getElementById('prodForm').classList.toggle('hidden')">Add Product</a>
</div>
<div id="prodForm" class="card hidden" style="max-width:500px;margin-bottom:20px">
<form method="POST" action="/admin/billing/products/store">
<div class="form-group"><label>Name</label><input name="name" required></div>
<div class="form-group"><label>Description</label><textarea name="description" rows="2"></textarea></div>
<div class="form-group"><label>Type</label><select name="type"><option value="hosting">Hosting</option><option value="radio">Radio</option><option value="vps">VPS</option><option value="domain">Domain</option><option value="addon">Addon</option></select></div>
<div class="form-group" style="display:flex;gap:8px"><div style="flex:1"><label>Price</label><input name="price" type="number" step="0.01" value="0.00"></div><div style="flex:1"><label>Setup Fee</label><input name="setup_fee" type="number" step="0.01" value="0.00"></div></div>
<div class="form-group"><label>Billing Cycle</label><select name="billing_cycle"><option value="monthly">Monthly</option><option value="quarterly">Quarterly</option><option value="semiannual">Semi-Annual</option><option value="annual">Annual</option><option value="biennial">Biennial</option></select></div>
<button type="submit" class="btn primary">Create</button>
</form></div>
<table><tr><th>Name</th><th>Type</th><th>Price</th><th>Cycle</th><th>Status</th><th></th></tr>
<?php if (!empty($products)): foreach ($products as $p): ?>
<tr><td><?php echo htmlspecialchars($p->name); ?></td><td><?php echo $p->type; ?></td><td>$<?php echo number_format($p->price, 2); ?></td><td><?php echo $p->billing_cycle; ?></td>
<td><span class="status-badge status-<?php echo $p->is_active ? 'active' : 'terminated'; ?>"><?php echo $p->is_active ? 'Active' : 'Inactive'; ?></span></td>
<td><a href="/admin/billing/products/delete/<?php echo $p->id; ?>" class="btn btn-sm danger" onclick="return confirm('Delete?')">Delete</a></td></tr>
<?php endforeach; else: ?><tr><td colspan="6" style="text-align:center;padding:20px;color:#64748b">No products yet.</td></tr>
<?php endif; ?></table>
