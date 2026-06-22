<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;margin-bottom:16px">
<h3 style="color:var(--accent)">Game Types</h3>
<a class="btn primary" onclick="document.getElementById('typeForm').classList.toggle('hidden')">Add Game Type</a>
</div>
<div id="typeForm" class="card hidden" style="max-width:600px;margin-bottom:20px">
<form method="POST" action="/admin/games/store">
<?php echo $csrfField ?? ''; ?>
<input type="hidden" name="id" id="editId" value="0">
<div class="form-group"><label>Name</label><input name="name" id="editName" required></div>
<div class="form-group"><label>Description</label><textarea name="description" id="editDesc" rows="2"></textarea></div>
<div class="form-group" style="display:flex;gap:8px">
<div style="flex:1"><label>Icon (emoji)</label><input name="icon" id="editIcon" value="🎮"></div>
<div style="flex:1"><label>Pricing Model</label><select name="pricing_model" id="editModel"><option value="per_slot">Per Slot</option><option value="tiered">Tiered</option><option value="package">Package Only</option></select></div>
</div>
<div class="form-group" style="display:flex;gap:8px">
<div style="flex:1"><label>Min Slots</label><input name="min_slots" id="editMin" type="number" value="1"></div>
<div style="flex:1"><label>Max Slots</label><input name="max_slots" id="editMax" type="number" value="100"></div>
</div>
<div class="form-group" style="display:flex;gap:8px">
<div style="flex:1"><label>Price Per Slot ($)</label><input name="price_per_slot" id="editPrice" type="number" step="0.01" value="0.00"></div>
<div style="flex:1"><label>Setup Fee ($)</label><input name="setup_fee" id="editSetup" type="number" step="0.01" value="0.00"></div>
</div>
<div class="form-group" style="display:flex;gap:8px">
<div style="flex:1"><label>Billing Cycle</label><select name="billing_cycle" id="editCycle"><option value="monthly">Monthly</option><option value="quarterly">Quarterly</option><option value="semiannual">Semi-Annual</option><option value="annual">Annual</option></select></div>
<div style="flex:1"><label>Sort Order</label><input name="sort_order" id="editSort" type="number" value="0"></div>
</div>
<div class="form-group"><label><input type="checkbox" name="is_active" value="1" checked> Active</label></div>
<button type="submit" class="btn primary">Save</button>
<a class="btn btn-sm" style="background:#333;color:#ccc;cursor:pointer" onclick="cancelEdit()">Cancel</a>
</form></div>
<table><tr><th>Icon</th><th>Name</th><th>Model</th><th>Slots</th><th>Price/Slot</th><th>Setup</th><th>Status</th><th></th></tr>
<?php if (!empty($types)): foreach ($types as $t): ?>
<tr>
<td style="font-size:20px"><?php echo htmlspecialchars($t->icon ?? '🎮'); ?></td>
<td><strong><?php echo htmlspecialchars($t->name); ?></strong></td>
<td><?php echo $t->pricing_model; ?></td>
<td><?php echo $t->min_slots; ?> - <?php echo $t->max_slots; ?></td>
<td>$<?php echo number_format($t->price_per_slot, 2); ?></td>
<td>$<?php echo number_format($t->setup_fee, 2); ?></td>
<td><span class="status-badge status-<?php echo $t->is_active ? 'active' : 'terminated'; ?>"><?php echo $t->is_active ? 'Active' : 'Inactive'; ?></span></td>
<td>
<a class="btn btn-sm" style="background:#333;color:#ccc;cursor:pointer" onclick="editType(<?php echo htmlspecialchars(json_encode($t), ENT_QUOTES, 'UTF-8'); ?>)">Edit</a>
<a href="/admin/games/delete/<?php echo $t->id; ?>" class="btn btn-sm danger" onclick="return confirm('Delete this game type and all its pricing/packages?')">Delete</a>
</td></tr>
<?php endforeach; else: ?><tr><td colspan="8" style="text-align:center;padding:20px;color:#64748b">No game types yet.</td></tr>
<?php endif; ?></table>
<script>
function editType(t) {
    document.getElementById('editId').value = t.id;
    document.getElementById('editName').value = t.name;
    document.getElementById('editDesc').value = t.description || '';
    document.getElementById('editIcon').value = t.icon || '🎮';
    document.getElementById('editModel').value = t.pricing_model || 'per_slot';
    document.getElementById('editMin').value = t.min_slots || 1;
    document.getElementById('editMax').value = t.max_slots || 100;
    document.getElementById('editPrice').value = t.price_per_slot || 0;
    document.getElementById('editSetup').value = t.setup_fee || 0;
    document.getElementById('editCycle').value = t.billing_cycle || 'monthly';
    document.getElementById('editSort').value = t.sort_order || 0;
    var cb = document.querySelector('input[name="is_active"]');
    cb.checked = t.is_active == 1;
    document.getElementById('typeForm').classList.remove('hidden');
}
function cancelEdit() {
    document.getElementById('editId').value = 0;
    document.getElementById('typeForm').classList.add('hidden');
}
</script>
