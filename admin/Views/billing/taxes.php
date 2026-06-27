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
<div style="display:flex;justify-content:space-between;margin-top:6px">
<span class="status-badge status-<?php echo $t->is_active ? 'active' : 'terminated'; ?>"><?php echo $t->is_active ? 'Active' : 'Inactive'; ?></span>
<a href="/admin/billing/taxes/delete/<?php echo $t->id; ?>" class="btn btn-sm danger">Delete</a>
</div>
</div>
<?php endforeach; else: ?>
<div class="card" style="text-align:center;padding:20px;grid-column:1/-1;color:#64748b">No tax rates configured.</div>
<?php endif; ?>
</div>