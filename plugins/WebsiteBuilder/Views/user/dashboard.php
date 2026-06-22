<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>

<div class="card" style="margin-bottom:16px">
<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px">
<div>
<h3 style="margin:0"><?php echo htmlspecialchars($site->name); ?></h3>
<p style="color:var(--text_muted);font-size:12px;margin:2px 0 0">
Status: <span class="badge bg-<?php echo $site->status === 'published' ? 'success' : ($site->status === 'draft' ? 'warning' : 'secondary'); ?>"><?php echo $site->status ?: 'draft'; ?></span>
Domain: <?php echo htmlspecialchars($site->domain ?: 'Not set'); ?>
</p>
</div>
<div class="d-flex gap-2">
<?php if ($site->status !== 'published'): ?>
<a href="/user/websites/<?php echo $site->id; ?>/publish" class="btn btn-sm btn-success"><i class="bi bi-skip-forward"></i> Publish</a>
<?php else: ?>
<a href="/user/websites/<?php echo $site->id; ?>/unpublish" class="btn btn-sm btn-secondary"><i class="bi bi-pause"></i> Unpublish</a>
<?php endif; ?>
<a href="/user/websites" class="btn btn-sm btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>
</div>
</div>

<div class="stats-grid" style="margin-bottom:16px">
<div class="stat-card"><h3>Pages</h3><div class="value"><?php echo count($pages); ?></div></div>
<div class="stat-card"><h3>Media Files</h3><div class="value"><?php echo $totalMedia; ?></div></div>
<div class="stat-card"><h3>Forms</h3><div class="value"><?php echo $totalForms; ?></div></div>
<div class="stat-card"><h3>Blog Posts</h3><div class="value"><?php echo $totalBlog; ?></div></div>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px;margin-bottom:16px">
<a href="/user/websites/<?php echo $site->id; ?>/editor/<?php echo isset($pages[0]) ? $pages[0]->id : 0; ?>" class="action-card">
<div class="icon">✏️</div><div class="name">Page Editor</div>
</a>
<a href="/user/websites/<?php echo $site->id; ?>/settings" class="action-card">
<div class="icon">⚙️</div><div class="name">Site Settings</div>
</a>
<a href="/user/websites/<?php echo $site->id; ?>/media" class="action-card">
<div class="icon">📁</div><div class="name">Media Library</div>
</a>
<a href="/user/websites/<?php echo $site->id; ?>/menus" class="action-card">
<div class="icon">📋</div><div class="name">Menus</div>
</a>
<a href="/user/websites/<?php echo $site->id; ?>/forms" class="action-card">
<div class="icon">📝</div><div class="name">Forms</div>
</a>
<a href="/user/websites/<?php echo $site->id; ?>/blog" class="action-card">
<div class="icon">📰</div><div class="name">Blog</div>
</a>
<a href="/user/websites/<?php echo $site->id; ?>/theme" class="action-card">
<div class="icon">🎨</div><div class="name">Theme</div>
</a>
</div>

<div class="card">
<h3 style="margin-bottom:12px">Pages</h3>
<?php if (count($pages) > 0): ?>
<table>
<thead><tr><th>Title</th><th>Slug</th><th>Status</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach ($pages as $p): ?>
<tr>
<td><strong><?php echo htmlspecialchars($p->title); ?></strong></td>
<td>/<?php echo htmlspecialchars($p->slug); ?></td>
<td><span class="badge bg-<?php echo ($p->status ?? 'draft') === 'published' ? 'success' : 'warning'; ?>"><?php echo $p->status ?? 'draft'; ?></span></td>
<td>
<a href="/user/websites/<?php echo $site->id; ?>/editor/<?php echo $p->id; ?>" class="btn btn-sm btn-primary"><i class="bi bi-pencil"></i> Edit</a>
<a href="/user/websites/<?php echo $site->id; ?>/preview/<?php echo $p->id; ?>" class="btn btn-sm btn-secondary" target="_blank"><i class="bi bi-eye"></i> Preview</a>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php else: ?>
<p style="color:var(--text_muted);text-align:center;padding:20px">No pages yet.</p>
<?php endif; ?>
</div>
