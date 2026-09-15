<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<div class="page-header">
<div class="d-flex justify-content-between align-items-center">
<h2 style="margin:0; color:var(--accent, #008cff)">📚 Knowledgebase</h2>
<a href="/admin/dashboard" class="btn btn-secondary"><i class="bi bi-arrow-left me-2"></i> Dashboard</a>
</div>
</div>

<div class="stats-grid">
<div class="stat-card"><h3>Categories</h3><div class="value"><?php echo count($cats ?? []); ?></div></div>
<div class="stat-card"><h3>Articles</h3><div class="value"><?php echo count($articles ?? []); ?></div></div>
</div>

<div class="card" style="max-width:500px;margin-top:20px">
<div class="card-header" style="background:rgba(0,191,255,.1);border-bottom:1px solid rgba(0,191,255,.2)">
<h3 style="margin:0; color:var(--primary, #008cff)">Create Category</h3>
</div>
<div class="card-body">
<form method="POST" action="/admin/support/kb/category/store">
<div class="form-group"><label>Category Name</label><input name="name" type="text" required></div>
<div class="form-group"><label>Description</label><textarea name="description" rows="2"></textarea></div>
<button type="submit" class="btn w-100" style="background:linear-gradient(135deg,var(--accent, #008cff),var(--accent-hover, #3bb8ff));border:none;border-radius:6px;padding:10px;font-weight:600">Create Category</button>
</form>
</div>
</div>

<div class="grid-2" style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:20px">
<div class="card">
<h3 style="color:var(--accent, #008cff);margin-bottom:12px">Categories</h3>
<?php if (!empty($cats)): foreach ($cats as $c): ?>
<p style="margin:6px 0;"><strong><?php echo htmlspecialchars($c->name); ?></strong> <span style="font-size:11px;color:var(--secondary, #64748b)"><?php echo htmlspecialchars($c->description ?? ''); ?></span></p>
<?php endforeach; else: ?>
<p style="color:var(--secondary, #64748b);font-size:12px">No categories yet.</p>
<?php endif; ?>
</div>
<div class="card">
<h3 style="color:var(--accent, #008cff);margin-bottom:12px">Articles</h3>
<?php if (!empty($articles)): foreach ($articles as $a): $cn = $catNames[$a->category_id] ?? '-'; ?>
<div class="p-3 mb-2" style="background:rgba(0,191,255,.05);border-radius:6px">
<div style="font-weight:600;font-size:13px;color:var(--primary, #008cff)"><?php echo htmlspecialchars($a->title); ?></div>
<div style="font-size:11px;color:var(--secondary, #64748b);margin-top:4px"><?php echo htmlspecialchars($cn); ?> · <?php echo $a->views; ?> views</div>
<div style="font-size:10px;color:var(--text-secondary);margin-top:2px"><?php echo htmlspecialchars(substr(strip_tags($a->content ?? ''), 0, 80)); ?>...</div>
<div style="margin-top:6px;display:flex;gap:4px">
<span class="status-badge status-<?php echo $a->is_published ? 'active' : 'terminated'; ?>" style="font-size:10px"><?php echo $a->is_published ? 'Published' : 'Draft'; ?></span>
<a href="/admin/support/kb/article/delete/<?php echo $a->id; ?>" class="btn btn-sm danger" onclick="return confirm('Delete?')">🗑</a>
</div>
</div>
<?php endforeach; else: ?>
<p style="color:var(--secondary, #64748b);font-size:12px">No articles yet.</p>
<?php endif; ?>
</div>
</div>
</div>
</div>