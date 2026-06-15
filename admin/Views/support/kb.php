<div class="page-grid" style="margin-bottom:20px">
<a href="/admin/support/kb" class="action-card"><div class="icon">📚</div><div class="name">Knowledgebase</div></a>
<a href="/admin/support/announcements" class="action-card"><div class="icon">📢</div><div class="name">Announcements</div></a>
<a href="/admin/support/status" class="action-card"><div class="icon">🖥</div><div class="name">Server Status</div></a>
</div>
<h3 style="color:var(--accent);margin:16px 0 12px">Knowledgebase</h3>
<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px">
<a class="btn primary" onclick="document.getElementById('catForm').classList.toggle('hidden')">Add Category</a>
<a class="btn primary" onclick="document.getElementById('artForm').classList.toggle('hidden')">Add Article</a>
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
<table><tr><th>Title</th><th>Category</th><th>Views</th><th>Status</th><th></th></tr>
<?php if (!empty($articles)): foreach ($articles as $a): ?><tr><td><?php echo htmlspecialchars($a->title); ?></td><td><?php echo htmlspecialchars($a->category_id ? ($this->db->table('kb_categories')->where('id', $a->category_id)->first()->name ?? '') : '-'); ?></td><td><?php echo $a->views; ?></td>
<td><span class="status-badge status-<?php echo $a->is_published ? 'active' : 'terminated'; ?>"><?php echo $a->is_published ? 'Published' : 'Draft'; ?></span></td>
<td><a href="/admin/support/kb/article/delete/<?php echo $a->id; ?>" class="btn btn-sm danger" onclick="return confirm('Delete?')">Delete</a></td></tr>
<?php endforeach; else: ?><tr><td colspan="5" style="text-align:center;padding:20px;color:#64748b">No articles yet.</td></tr>
<?php endif; ?></table>
<h3 style="color:var(--accent);margin:20px 0 12px">Categories</h3>
<table><tr><th>Name</th><th>Description</th><th></th></tr>
<?php if (!empty($cats)): foreach ($cats as $c): ?><tr><td><?php echo htmlspecialchars($c->name); ?></td><td><?php echo htmlspecialchars($c->description ?? '-'); ?></td>
<td><a href="/admin/support/kb/category/delete/<?php echo $c->id; ?>" class="btn btn-sm danger" onclick="return confirm('Delete?')">Delete</a></td></tr>
<?php endforeach; else: ?><tr><td colspan="3" style="text-align:center;padding:20px;color:#64748b">No categories.</td></tr>
<?php endif; ?></table>
