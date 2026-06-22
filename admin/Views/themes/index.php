<style>
.theme-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:14px}
.theme-card{background:var(--card_bg,rgba(8,16,28,.6));border:1px solid var(--border,rgba(0,191,255,.1));border-radius:12px;padding:20px;text-align:center;transition:.3s;position:relative}
.theme-card.active{border-color:var(--primary,#008cff);box-shadow:0 0 20px rgba(0,140,255,.1)}
.theme-card .preview{width:100%;height:120px;border-radius:8px;margin-bottom:10px;border:1px solid var(--border,rgba(0,191,255,.1));display:flex;align-items:center;justify-content:center;font-size:32px;background:var(--bg,#02050e)}
.theme-card .tname{font-size:15px;font-weight:700;margin-bottom:2px}
.theme-card .tauthor{font-size:11px;color:var(--text_muted,#64748b);margin-bottom:8px}
.theme-card .tbadge{display:inline-block;padding:2px 10px;border-radius:4px;font-size:10px;font-weight:600;margin-bottom:8px}
.badge-active{background:rgba(74,222,128,.12);color:var(--success,#4ade80)}
.badge-inactive{background:rgba(100,116,139,.12);color:var(--text_muted,#64748b)}
</style>

<div style="display:flex;gap:12px;align-items:start;flex-wrap:wrap;margin-bottom:20px">
<h2 style="margin:0">🎨 Theme Manager</h2>
</div>

<div class="card" style="margin-bottom:20px">
<h3 style="color:var(--primary,#008cff);margin-bottom:12px">🖥 Admin Themes</h3>
<p style="color:var(--text_muted,#64748b);font-size:13px;margin-bottom:14px">Controls the look and feel of the admin panel.</p>
<div class="theme-grid">
<?php foreach ($adminThemes as $key => $t): ?>
<div class="theme-card <?php echo $key === $activeAdmin ? 'active' : ''; ?>">
<div class="preview" style="background:<?php echo $t['colors']['sidebar_bg'] ?? '#0b1728'; ?>;color:<?php echo $t['colors']['primary'] ?? '#008cff'; ?>">🎨</div>
<div class="tname"><?php echo htmlspecialchars($t['name'] ?? $key); ?></div>
<div class="tauthor">by <?php echo htmlspecialchars($t['author'] ?? 'Planet-Hosts'); ?> · v<?php echo htmlspecialchars($t['version'] ?? '1.0'); ?></div>
<div style="display:flex;gap:6px;justify-content:center;flex-wrap:wrap">
<?php if ($key === $activeAdmin): ?>
<span class="tbadge badge-active">✓ Active</span>
<?php else: ?>
<a href="/admin/themes/activate/admin/<?php echo $key; ?>" class="btn btn-sm btn-primary">Activate</a>
<?php endif; ?>
<?php if ($key !== 'default'): ?>
<a href="/admin/themes/export/admin/<?php echo $key; ?>" class="btn btn-sm btn-secondary">Export</a>
<a href="/admin/themes/delete/admin/<?php echo $key; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this theme?')">Delete</a>
<?php endif; ?>
</div>
</div>
<?php endforeach; ?>
</div>
</div>

<div class="card">
<h3 style="color:var(--primary,#008cff);margin-bottom:12px">🌐 Public Themes</h3>
<p style="color:var(--text_muted,#64748b);font-size:13px;margin-bottom:14px">Controls the look and feel of the public website.</p>
<div class="theme-grid">
<?php foreach ($publicThemes as $key => $t): ?>
<div class="theme-card <?php echo $key === $activePublic ? 'active' : ''; ?>">
<div class="preview">🌐</div>
<div class="tname"><?php echo htmlspecialchars($t['name'] ?? $key); ?></div>
<div class="tauthor">by <?php echo htmlspecialchars($t['author'] ?? 'Planet-Hosts'); ?> · v<?php echo htmlspecialchars($t['version'] ?? '1.0'); ?></div>
<div style="display:flex;gap:6px;justify-content:center;flex-wrap:wrap">
<?php if ($key === $activePublic): ?>
<span class="tbadge badge-active">✓ Active</span>
<?php else: ?>
<a href="/admin/themes/activate/public/<?php echo $key; ?>" class="btn btn-sm btn-primary">Activate</a>
<?php endif; ?>
<?php if ($key !== 'default'): ?>
<a href="/admin/themes/export/public/<?php echo $key; ?>" class="btn btn-sm btn-secondary">Export</a>
<a href="/admin/themes/delete/public/<?php echo $key; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this theme?')">Delete</a>
<?php endif; ?>
</div>
</div>
<?php endforeach; ?>
</div>
</div>

<div class="card">
<h3 style="color:var(--primary,#008cff);margin-bottom:12px">📦 Upload Theme</h3>
<form method="post" action="/admin/themes/upload" enctype="multipart/form-data" style="display:flex;gap:12px;align-items:end;flex-wrap:wrap">
<div class="form-group" style="flex:1;min-width:200px">
<label>Theme Type</label>
<select name="theme_type">
<option value="admin">Admin</option>
<option value="public">Public</option>
</select>
</div>
<div class="form-group" style="flex:2;min-width:200px">
<label>Archive (.zip / .tar.gz)</label>
<input type="file" name="theme_file" accept=".zip,.tar,.gz,.tar.gz" required>
</div>
<button type="submit" class="btn btn-primary">Upload & Install</button>
</form>
</div>
