<?php if (isset($_SESSION['success_message'])): ?><div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div><?php endif; ?>

<div class="card" style="margin-bottom:16px">
<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
<div><h3 style="margin:0">Themes</h3><p style="color:var(--text_muted);font-size:12px;margin:2px 0 0">Website appearance themes</p></div>
<button class="btn btn-sm btn-primary" onclick="document.getElementById('themeForm').style.display='block'"><i class="bi bi-plus-circle"></i> Add Theme</button>
</div>
</div>

<div id="themeForm" style="display:none" class="card" style="margin-bottom:16px">
<h4 style="margin-bottom:12px">New Theme</h4>
<form method="POST" action="/admin/websitebuilder/themes/store">
<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px">
<div><label style="display:block;font-size:12px;color:var(--text_muted);margin-bottom:4px">Name</label><input name="name" required class="form-control"></div>
<div><label style="display:block;font-size:12px;color:var(--text_muted);margin-bottom:4px">Version</label><input name="version" value="1.0" class="form-control"></div>
</div>
<div style="margin-bottom:12px"><label style="display:block;font-size:12px;color:var(--text_muted);margin-bottom:4px">Description</label><textarea name="description" rows="2" class="form-control"></textarea></div>
<div style="margin-bottom:12px"><label style="display:block;font-size:12px;color:var(--text_muted);margin-bottom:4px">Author</label><input name="author" class="form-control"></div>
<div style="margin-bottom:12px"><label style="display:block;font-size:12px;color:var(--text_muted);margin-bottom:4px">Config (JSON colors)</label>
<textarea name="config" rows="4" class="form-control" placeholder='{"primary":"#008cff","secondary":"#00e5ff","bg":"#02050e","card_bg":"rgba(8,16,28,.85)","text":"#ffffff","accent":"#38bdf8"}'></textarea></div>
<button type="submit" class="btn btn-sm btn-primary">Save Theme</button>
<button type="button" class="btn btn-sm btn-secondary" onclick="document.getElementById('themeForm').style.display='none'">Cancel</button>
</form>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px">
<?php foreach ($themes as $t):
$cfg = json_decode($t->config, true);
$primary = $cfg['primary'] ?? '#008cff';
?>
<div class="card" style="overflow:hidden">
<div style="height:80px;background:linear-gradient(135deg,<?php echo $primary; ?>,<?php echo $cfg['secondary'] ?? $primary; ?>);display:flex;align-items:center;justify-content:center;font-size:32px;opacity:.8">🎨</div>
<div style="padding:16px">
<h4 style="margin:0 0 4px"><?php echo htmlspecialchars($t->name); ?></h4>
<p style="color:var(--text_muted);font-size:11px;margin:0 0 8px"><?php echo htmlspecialchars($t->description ?: ''); ?> v<?php echo htmlspecialchars($t->version ?? '1.0'); ?></p>
<div style="display:flex;gap:8px;margin-bottom:10px">
<?php foreach (['primary','secondary','accent','bg'] as $k):
$c = $cfg[$k] ?? '#333';
$isHex = str_starts_with($c, '#');
?>
<div style="width:24px;height:24px;border-radius:50%;background:<?php echo $c; ?>;border:2px solid rgba(255,255,255,.1)" title="<?php echo $k; ?>: <?php echo $c; ?>"></div>
<?php endforeach; ?>
</div>
<a href="/admin/websitebuilder/themes/delete/<?php echo $t->id; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete theme?')"><i class="bi bi-trash"></i> Delete</a>
</div>
</div>
<?php endforeach; ?>
</div>
