<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;margin-bottom:16px">
<h3 style="color:var(--accent)">Slot Pricing Tiers</h3>
<a class="btn primary" onclick="document.getElementById('priceForm').classList.toggle('hidden')">Add Pricing Tier</a>
</div>
<div id="priceForm" class="card hidden" style="max-width:500px;margin-bottom:20px">
<form method="POST" action="/admin/games/pricing/store">
<?php echo $csrfField ?? ''; ?>
<input type="hidden" name="id" id="editId" value="0">
<div class="form-group"><label>Game Type</label><select name="game_type_id" id="editGame" required>
<option value="">Select game...</option>
<?php foreach ($types as $t): ?>
<option value="<?php echo $t->id; ?>"><?php echo htmlspecialchars($t->name); ?></option>
<?php endforeach; ?>
</select></div>
<div class="form-group" style="display:flex;gap:8px">
<div style="flex:1"><label>Min Slots</label><input name="min_slots" id="editMin" type="number" value="1"></div>
<div style="flex:1"><label>Max Slots</label><input name="max_slots" id="editMax" type="number" value="100"></div>
</div>
<div class="form-group"><label>Price Per Slot ($)</label><input name="price_per_slot" id="editPrice" type="number" step="0.01" value="0.00"></div>
<button type="submit" class="btn primary">Save</button>
<a class="btn btn-sm" style="background:#333;color:#ccc;cursor:pointer" onclick="cancelEdit()">Cancel</a>
</form></div>
<table><tr><th>Game</th><th>Min Slots</th><th>Max Slots</th><th>Price/Slot</th><th></th></tr>
<?php if (!empty($pricing)): foreach ($pricing as $p): ?>
<tr>
<td><?php echo htmlspecialchars($typeMap[$p->game_type_id] ?? 'Unknown'); ?></td>
<td><?php echo $p->min_slots; ?></td>
<td><?php echo $p->max_slots; ?></td>
<td>$<?php echo number_format($p->price_per_slot, 2); ?></td>
<td>
<a class="btn btn-sm" style="background:#333;color:#ccc;cursor:pointer" onclick="editPrice(<?php echo htmlspecialchars(json_encode($p), ENT_QUOTES, 'UTF-8'); ?>)">Edit</a>
<a href="/admin/games/pricing/delete/<?php echo $p->id; ?>" class="btn btn-sm danger" onclick="return confirm('Delete this pricing tier?')">Delete</a>
</td></tr>
<?php endforeach; else: ?><tr><td colspan="5" style="text-align:center;padding:20px;color:#64748b">No pricing tiers yet.</td></tr>
<?php endif; ?></table>
<script>
function editPrice(p) {
    document.getElementById('editId').value = p.id;
    document.getElementById('editGame').value = p.game_type_id;
    document.getElementById('editMin').value = p.min_slots;
    document.getElementById('editMax').value = p.max_slots;
    document.getElementById('editPrice').value = p.price_per_slot;
    document.getElementById('priceForm').classList.remove('hidden');
}
function cancelEdit() {
    document.getElementById('editId').value = 0;
    document.getElementById('priceForm').classList.add('hidden');
}
</script>
