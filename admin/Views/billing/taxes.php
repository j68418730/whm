<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;margin-bottom:16px">
<h3 style="color:var(--accent)">Tax Rates</h3>
<a class="btn primary" onclick="document.getElementById('taxForm').classList.toggle('hidden')">Add Tax Rate</a>
</div>
<div id="taxForm" class="card hidden" style="max-width:500px;margin-bottom:20px">
<form method="POST" action="/admin/billing/taxes/store">
<div class="form-group"><label>Name</label><input name="name" required placeholder="VAT 20%"></div>
<div class="form-group"><label>Rate (%)</label><input name="rate" type="number" step="0.01" value="0.00"></div>
<div class="form-group"><label>Country Code (optional)</label><input name="country" placeholder="US"></div>
<button type="submit" class="btn primary">Add</button>
</form></div>
<table><tr><th>Name</th><th>Rate</th><th>Country</th><th>Status</th><th></th></tr>
<?php if (!empty($taxes)): foreach ($taxes as $t): ?>
<tr><td><?php echo htmlspecialchars($t->name); ?></td><td><?php echo ($t->rate * 100); ?>%</td><td><?php echo $t->country ?: '-'; ?></td>
<td><span class="status-badge status-<?php echo $t->is_active ? 'active' : 'terminated'; ?>"><?php echo $t->is_active ? 'Active' : 'Inactive'; ?></span></td>
<td><a href="/admin/billing/taxes/delete/<?php echo $t->id; ?>" class="btn btn-sm danger">Delete</a></td></tr>
<?php endforeach; else: ?><tr><td colspan="5" style="text-align:center;padding:20px;color:#64748b">No tax rates configured.</td></tr>
<?php endif; ?></table>
