<style>
select{background:rgba(8,16,28,.9);border:1px solid rgba(255,255,255,.12);color:#e0e0e0;padding:8px 12px;border-radius:6px;font-size:14px;width:100%;outline:none}
select:focus{border-color:var(--accent)}
select option{background:#0a0f1a;color:#e0e0e0}
</style>
<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;margin-bottom:12px">
<h3 style="color:var(--accent);margin:0">Knowledgebase</h3>
<div style="display:flex;gap:6px">
<a class="btn primary" onclick="document.getElementById('catForm').classList.toggle('hidden')">+ Category</a>
<a class="btn primary" onclick="document.getElementById('artForm').classList.toggle('hidden')">+ Article</a>
</div>
</div>
<div id="catForm" class="card hidden" style="max-width:400px;margin-bottom:12px">
<form method="POST" action="/admin/support/kb/category/store">
<div class="form-group"><label>Category Name</label><input name="name" required></div>
<div class="form-group"><label>Description</label><textarea name="description" rows="2"></textarea></div>
<button type="submit" class="btn primary">Create</button>
</form></div>
<div id="artForm" class="card hidden" style="max-width:500px;margin-bottom:12px">
<form method="POST" action="/admin/support/kb/article/store">
<div class="form-group"><label>Title</label><input name="title" required></div>
<div class="form-group"><label>Category</label><select name="category_id"><option value="0">Uncategorized</option>
<?php foreach ($cats as $c): ?><option value="<?php echo $c->id; ?>"><?php echo htmlspecialchars($c->name); ?></option><?php endforeach; ?></select></div>
<div class="form-group"><label>Content</label><textarea name="content" rows="6" required></textarea></div>
<div class="form-group"><label><input name="is_published" type="checkbox" value="1" checked> Published</label></div>
<button type="submit" class="btn primary">Create</button>
</form></div>
<h4 style="color:var(--accent);margin:16px 0 8px">Articles</h4>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:10px">
<?php if (!empty($articles)): foreach ($articles as $a): $cn = $catNames[$a->category_id] ?? '-'; ?>
<div class="card" style="margin-bottom:0;padding:14px">
<div style="font-weight:600;font-size:14px"><?php echo htmlspecialchars($a->title); ?></div>
<div style="font-size:12px;color:#94a3b8;margin-top:4px"><?php echo htmlspecialchars($cn); ?> · <?php echo $a->views; ?> views</div>
<div style="font-size:11px;color:var(--text-secondary);margin-top:4px"><?php echo htmlspecialchars(substr(strip_tags($a->content ?? ''), 0, 100)); ?>...</div>
<div style="margin-top:8px;display:flex;gap:4px">
<span class="status-badge status-<?php echo $a->is_published ? 'active' : 'terminated'; ?>" style="font-size:10px"><?php echo $a->is_published ? 'Published' : 'Draft'; ?></span>
<a href="/admin/support/kb/article/delete/<?php echo $a->id; ?>" class="btn btn-sm danger" onclick="return confirm('Delete?')">🗑</a>
</div>
</div>
<?php endforeach; else: ?>
<div class="card" style="text-align:center;padding:20px;grid-column:1/-1;color:#64748b">No articles yet.</div>
<?php endif; ?>
</div>
<h4 style="color:var(--accent);margin:20px 0 8px">Categories</h4>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:10px">
<?php if (!empty($cats)): foreach ($cats as $c): ?>
<div class="card" style="margin-bottom:0;padding:14px">
<div style="display:flex;justify-content:space-between;align-items:start">
<strong style="font-size:14px"><?php echo htmlspecialchars($c->name); ?></strong>
<div style="display:flex;gap:4px">
<a class="btn btn-sm secondary" onclick="editCat(<?php echo $c->id; ?>, '<?php echo htmlspecialchars(addslashes($c->name)); ?>', '<?php echo htmlspecialchars(addslashes($c->description ?? '')); ?>')">✏</a>
<a href="/admin/support/kb/category/delete/<?php echo $c->id; ?>" class="btn btn-sm danger" onclick="return confirm('Delete?')">🗑</a>
</div>
</div>
<div style="font-size:12px;color:#94a3b8;margin-top:4px"><?php echo htmlspecialchars($c->description ?? '-'); ?></div>
</div>
<?php endforeach; else: ?>
<div class="card" style="text-align:center;padding:20px;grid-column:1/-1;color:#64748b">No categories.</div>
<?php endif; ?>
</div>
<div id="editCatModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.7);align-items:center;justify-content:center" onclick="if(event.target===this)this.style.display='none'">
<div class="card" style="max-width:400px;margin:auto;position:relative;top:10%">
<h4 style="color:var(--accent);margin-bottom:8px">Edit Category</h4>
<form method="POST" action="" id="editCatForm">
<div class="form-group"><label>Name</label><input name="name" id="ec_name" required></div>
<div class="form-group"><label>Description</label><textarea name="description" id="ec_desc" rows="2"></textarea></div>
<button type="submit" class="btn primary">Save</button>
<button type="button" class="btn secondary" onclick="document.getElementById('editCatModal').style.display='none'">Cancel</button>
</form></div></div>
<script>
function editCat(id, name, desc) {
    document.getElementById('editCatForm').action = '/admin/support/kb/category/update/' + id;
    document.getElementById('ec_name').value = name;
    document.getElementById('ec_desc').value = desc;
    document.getElementById('editCatModal').style.display = 'flex';
}
</script>