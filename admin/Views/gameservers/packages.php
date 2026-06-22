<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;margin-bottom:16px">
<h3 style="color:var(--accent)">Fixed Packages</h3>
<a class="btn primary" onclick="document.getElementById('pkgForm').classList.toggle('hidden')">Add Package</a>
</div>
<div id="pkgForm" class="card hidden" style="max-width:500px;margin-bottom:20px">
<form method="POST" action="/admin/games/packages/store">
<?php echo $csrfField ?? ''; ?>
<input type="hidden" name="id" id="editId" value="0">
<div class="form-group"><label>Game Type</label><select name="game_type_id" id="editGame" required>
<option value="">Select game...</option>
<?php foreach ($types as $t): ?>
<option value="<?php echo $t->id; ?>"><?php echo htmlspecialchars($t->name); ?></option>
<?php endforeach; ?>
</select></div>
<div class="form-group"><label>Package Name</label><input name="name" id="editName" required></div>
<div class="form-group"><label>Description</label><textarea name="description" id="editDesc" rows="2"></textarea></div>
<div class="form-group" style="display:flex;gap:8px">
<div style="flex:1"><label>Slots</label><input name="slots" id="editSlots" type="number" value="10"></div>
<div style="flex:1"><label>Price ($)</label><input name="price" id="editPrice" type="number" step="0.01" value="0.00"></div>
</div>
<div class="form-group" style="display:flex;gap:8px">
<div style="flex:1"><label>Setup Fee ($)</label><input name="setup_fee" id="editSetup" type="number" step="0.01" value="0.00"></div>
<div style="flex:1"><label>Billing Cycle</label><select name="billing_cycle" id="editCycle"><option value="monthly">Monthly</option><option value="quarterly">Quarterly</option><option value="semiannual">Semi-Annual</option><option value="annual">Annual</option></select></div>
</div>
<div class="form-group"><label><input type="checkbox" name="is_active" value="1" checked> Active</label></div>
<button type="submit" class="btn primary">Save</button>
<a class="btn btn-sm" style="background:#333;color:#ccc;cursor:pointer" onclick="cancelEdit()">Cancel</a>
</form></div>
<table><tr><th>Game</th><th>Name</th><th>Slots</th><th>Price</th><th>Setup</th><th>Cycle</th><th>Status</th><th></th></tr>
<?php if (!empty($packages)): foreach ($packages as $pkg): ?>
<tr>
<td><?php echo htmlspecialchars($typeMap[$pkg->game_type_id] ?? 'Unknown'); ?></td>
<td><strong><?php echo htmlspecialchars($pkg->name); ?></strong></td>
<td><?php echo $pkg->slots; ?></td>
<td>$<?php echo number_format($pkg->price, 2); ?></td>
<td>$<?php echo number_format($pkg->setup_fee, 2); ?></td>
<td><?php echo $pkg->billing_cycle; ?></td>
<td><span class="status-badge status-<?php echo $pkg->is_active ? 'active' : 'terminated'; ?>"><?php echo $pkg->is_active ? 'Active' : 'Inactive'; ?></span></td>
<td>
<a class="btn btn-sm" style="background:#333;color:#ccc;cursor:pointer" onclick="editPkg(<?php echo htmlspecialchars(json_encode($pkg), ENT_QUOTES, 'UTF-8'); ?>)">Edit</a>
<a href="/admin/games/packages/delete/<?php echo $pkg->id; ?>" class="btn btn-sm danger" onclick="return confirm('Delete this package?')">Delete</a>
</td></tr>
<?php endforeach; else: ?><tr><td colspan="8" style="text-align:center;padding:20px;color:#64748b">No packages yet.</td></tr>
<?php endif; ?></table>
<script>
function editPkg(p) {
    document.getElementById('editId').value = p.id;
    document.getElementById('editGame').value = p.game_type_id;
    document.getElementById('editName').value = p.name;
    document.getElementById('editDesc').value = p.description || '';
    document.getElementById('editSlots').value = p.slots;
    document.getElementById('editPrice').value = p.price;
    document.getElementById('editSetup').value = p.setup_fee || 0;
    document.getElementById('editCycle').value = p.billing_cycle || 'monthly';
    var cb = document.querySelector('#pkgForm input[name="is_active"]');
    if (cb) cb.checked = p.is_active == 1;
    document.getElementById('pkgForm').classList.remove('hidden');
}
function cancelEdit() {
    document.getElementById('editId').value = 0;
    document.getElementById('pkgForm').classList.add('hidden');
}
</script>
