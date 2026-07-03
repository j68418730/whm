<div class="card" style="padding:20px">
<h3 style="margin-bottom:12px">Categories</h3>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
<div>
<h4 style="font-size:13px;margin-bottom:8px">New Category</h4>
<form method="POST" action="/admin/store/categories/store">
<div style="margin-bottom:8px"><input name="name" placeholder="Category name" required style="width:100%;padding:8px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:12px"></div>
<div style="margin-bottom:8px"><input name="description" placeholder="Description" style="width:100%;padding:8px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:12px"></div>
<div style="margin-bottom:8px"><input name="icon" placeholder="Icon class (e.g. bi-phone)" style="width:100%;padding:8px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:12px"></div>
<div style="margin-bottom:8px"><input name="sort_order" type="number" placeholder="Sort order" value="0" style="width:100%;padding:8px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:12px"></div>
<button type="submit" class="btn primary btn-sm"><i class="bi bi-plus-lg"></i> Create</button>
</form>
</div>
<div>
<h4 style="font-size:13px;margin-bottom:8px">Existing Categories</h4>
<?php if (empty($categories)): ?>
<p style="font-size:12px;color:#64748b">No categories.</p>
<?php else: ?>
<?php foreach ($categories as $c): ?>
<div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid rgba(255,255,255,.04);font-size:12px">
<span><i class="bi <?php echo $c->icon ?? 'bi-folder'; ?>"></i> <?php echo htmlspecialchars($c->name); ?></span>
<a href="/admin/store/categories/delete/<?php echo $c->id; ?>" class="btn btn-sm" style="background:#ef4444;color:#fff;font-size:9px;padding:3px 8px" onclick="return confirm('Delete this category? Products will be uncategorized.')">Delete</a>
</div>
<?php endforeach; ?>
<?php endif; ?>
</div>
</div>
</div>
