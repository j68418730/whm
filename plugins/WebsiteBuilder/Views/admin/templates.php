<?php if (isset($_SESSION['success_message'])): ?><div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div><?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?><div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></div><?php endif; ?>

<div class="card" style="margin-bottom:16px">
<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
<div><h3 style="margin:0">Templates</h3><p style="color:var(--text_muted);font-size:12px;margin:2px 0 0"><?php echo count($templates); ?> templates available</p></div>
<div class="d-flex gap-2">
<button class="btn btn-sm btn-primary" onclick="document.getElementById('createForm').style.display='block'"><i class="bi bi-plus-circle"></i> New Template</button>
<button class="btn btn-sm btn-secondary" onclick="document.getElementById('importForm').style.display='block'"><i class="bi bi-upload"></i> Import JSON</button>
<a href="/admin/websitebuilder" class="btn btn-sm btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>
</div>
</div>

<div id="createForm" style="display:none" class="card" style="margin-bottom:16px">
<h4 style="margin-bottom:12px">Create Template</h4>
<form method="POST" action="/admin/websitebuilder/templates/store">
<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px">
<div><label style="display:block;font-size:12px;color:var(--text_muted);margin-bottom:4px">Name</label><input name="name" required class="form-control"></div>
<div><label style="display:block;font-size:12px;color:var(--text_muted);margin-bottom:4px">Category</label><input name="category" class="form-control"></div>
</div>
<div style="margin-bottom:12px"><label style="display:block;font-size:12px;color:var(--text_muted);margin-bottom:4px">Description</label><textarea name="description" rows="2" class="form-control"></textarea></div>
<div style="margin-bottom:12px"><label style="display:block;font-size:12px;color:var(--text_muted);margin-bottom:4px">Thumbnail URL</label><input name="thumbnail" class="form-control"></div>
<div style="margin-bottom:12px"><label style="display:block;font-size:12px;color:var(--text_muted);margin-bottom:4px">Config (JSON)</label><textarea name="config" rows="6" class="form-control" placeholder='{"pages":[...],"menus":[...]}'></textarea></div>
<button type="submit" class="btn btn-sm btn-primary">Save Template</button>
<button type="button" class="btn btn-sm btn-secondary" onclick="document.getElementById('createForm').style.display='none'">Cancel</button>
</form>
</div>

<div id="importForm" style="display:none" class="card" style="margin-bottom:16px">
<h4 style="margin-bottom:12px">Import Template from JSON</h4>
<form method="POST" action="/admin/websitebuilder/templates/import">
<textarea name="json_data" rows="8" class="form-control" style="margin-bottom:8px" placeholder='Paste JSON template data...'></textarea>
<button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-upload"></i> Import</button>
<button type="button" class="btn btn-sm btn-secondary" onclick="document.getElementById('importForm').style.display='none'">Cancel</button>
</form>
</div>

<?php
$currentCat = '';
foreach ($templates as $t):
$cfg = json_decode($t->config, true);
$cat = $cfg['category'] ?? 'uncategorized';
if ($cat !== $currentCat):
$currentCat = $cat;
?>
<div class="card" style="margin-bottom:12px;padding:12px 16px">
<h4 style="margin:0;font-size:13px;color:var(--accent);text-transform:capitalize"><?php echo htmlspecialchars($currentCat ?: 'Uncategorized'); ?></h4>
</div>
<?php endif; ?>
<div class="card" style="margin-bottom:8px;padding:16px">
<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px">
<div style="flex:1">
<h4 style="margin:0 0 4px"><?php echo htmlspecialchars($t->name); ?></h4>
<p style="color:var(--text_muted);font-size:12px;margin:0"><?php echo htmlspecialchars($t->description ?: 'No description'); ?></p>
</div>
<div class="d-flex gap-2">
<a href="/admin/websitebuilder/templates/export/<?php echo $t->id; ?>" class="btn btn-sm btn-secondary"><i class="bi bi-download"></i> Export</a>
<a href="/admin/websitebuilder/templates/delete/<?php echo $t->id; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete template?')"><i class="bi bi-trash"></i></a>
</div>
</div>
</div>
<?php endforeach; ?>
