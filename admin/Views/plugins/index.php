<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px">
<div>
<h2 style="margin:0">🧩 Plugin Manager</h2>
<p style="color:#64748b;margin:4px 0 0">Manage, install, and configure plugins.</p>
</div>
<button class="btn primary" onclick="document.getElementById('uploadPanel').style.display='block';document.getElementById('uploadPanel').scrollIntoView({behavior:'smooth'})"><i class="bi bi-cloud-upload"></i> Upload Plugin</button>
</div>

<div class="stats-grid" style="margin-bottom:24px">
<div class="stat-card"><h3>Installed</h3><div class="value"><?php echo count($plugins); ?></div></div>
<div class="stat-card"><h3>Active</h3><div class="value" style="color:#4ade80"><?php echo count(array_filter($plugins, fn($p) => $p->is_active)); ?></div></div>
<div class="stat-card"><h3>Inactive</h3><div class="value" style="color:#f87171"><?php echo count(array_filter($plugins, fn($p) => !$p->is_active)); ?></div></div>
<div class="stat-card"><h3>Available</h3><div class="value" style="color:#facc15"><?php echo count($uninstalled); ?></div></div>
</div>

<!-- Upload Panel -->
<div id="uploadPanel" class="card" style="display:none;max-width:500px;margin-bottom:24px">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
<h4 style="margin:0;color:var(--accent)"><i class="bi bi-cloud-upload"></i> Upload Plugin</h4>
<button class="btn btn-sm secondary" onclick="document.getElementById('uploadPanel').style.display='none'"><i class="bi bi-x"></i></button>
</div>
<form method="POST" action="/admin/plugins/upload" enctype="multipart/form-data">
<div class="form-group"><label>Plugin ZIP File</label>
<div style="border:2px dashed rgba(0,191,255,.2);border-radius:12px;padding:30px;text-align:center;cursor:pointer" onclick="document.getElementById('pluginZip').click()" onmouseover="this.style.borderColor='#0A84FF'" onmouseout="this.style.borderColor='rgba(0,191,255,.2)'">
<i class="bi bi-file-zip" style="font-size:32px;color:#0A84FF;display:block;margin-bottom:8px"></i>
<span style="color:#94a3b8;font-size:13px">Click to select a plugin ZIP file</span>
<input type="file" id="pluginZip" name="plugin_zip" accept=".zip" required style="display:none" onchange="this.parentElement.querySelector('span').textContent=this.files[0].name">
</div></div>
<button type="submit" class="btn primary" style="margin-top:12px"><i class="bi bi-upload"></i> Upload & Install</button>
</form>
</div>

<!-- Installed Plugins -->
<h3 style="color:var(--accent);margin-bottom:16px">📦 Installed Plugins (<?php echo count($plugins); ?>)</h3>
<?php if (!empty($plugins)): ?>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:16px;margin-bottom:30px">
<?php foreach ($plugins as $p):
$colors = ['#0A84FF','#4ade80','#a78bfa','#fbbf24','#f87171','#34d399','#38bdf8','#fb923c'];
$color = $colors[$p->id % 8];
$icon = $p->is_active ? 'bi-toggle-on' : 'bi-toggle-off';
$iconColor = $p->is_active ? '#4ade80' : '#64748b';
?>
<div style="background:var(--bg-card);border:1px solid <?php echo $p->is_active ? $color : 'var(--border)'; ?>;border-radius:14px;padding:20px;transition:.2s;position:relative;overflow:hidden">
<div style="position:absolute;top:0;left:0;width:4px;height:100%;background:<?php echo $p->is_active ? $color : 'var(--text-muted)'; ?>"></div>
<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px">
<div style="display:flex;align-items:center;gap:12px">
<div style="width:42px;height:42px;border-radius:10px;background:<?php echo $color; ?>20;display:flex;align-items:center;justify-content:center;font-size:20px;color:<?php echo $color; ?>">
<i class="bi bi-puzzle-fill"></i>
</div>
<div>
<h4 style="margin:0;font-size:14px"><?php echo htmlspecialchars($p->name, ENT_QUOTES, 'UTF-8'); ?></h4>
<span style="font-size:11px;color:#64748b">v<?php echo htmlspecialchars($p->version ?? '1.0', ENT_QUOTES, 'UTF-8'); ?></span>
</div>
</div>
<i class="bi <?php echo $icon; ?>" style="font-size:22px;color:<?php echo $iconColor; ?>"></i>
</div>
<div style="font-size:12px;color:#64748b;margin-bottom:14px;line-height:1.5">
<?php echo htmlspecialchars($p->description ?? htmlspecialchars($p->name) . ' plugin for Planet Hosts.', ENT_QUOTES, 'UTF-8'); ?>
</div>
<div style="display:flex;gap:6px;flex-wrap:wrap">
<a href="/admin/plugins/toggle/<?php echo $p->id; ?>" class="btn btn-sm <?php echo $p->is_active ? 'danger' : 'primary'; ?>">
<i class="bi <?php echo $p->is_active ? 'bi-toggle-off' : 'bi-toggle-on'; ?>"></i> <?php echo $p->is_active ? 'Disable' : 'Enable'; ?>
</a>
<a href="/admin/plugins/uninstall/<?php echo $p->id; ?>" class="btn btn-sm danger" onclick="return confirm('Uninstall <?php echo htmlspecialchars(addslashes($p->name), ENT_QUOTES, 'UTF-8'); ?>?')">
<i class="bi bi-trash"></i> Uninstall
</a>
</div>
</div>
<?php endforeach; ?>
</div>
<?php else: ?>
<div style="text-align:center;padding:60px 20px;background:var(--bg-card);border:1px solid var(--border);border-radius:14px;margin-bottom:30px">
<div style="font-size:48px;margin-bottom:16px;opacity:.3"><i class="bi bi-puzzle"></i></div>
<h4 style="color:var(--text-muted);margin-bottom:8px">No Plugins Installed</h4>
<p style="color:#64748b;font-size:13px">Upload a plugin ZIP file to get started.</p>
</div>
<?php endif; ?>

<!-- Available Plugins -->
<?php if (!empty($uninstalled)): ?>
<h3 style="color:var(--accent);margin:20px 0 16px">📋 Available Plugins (<?php echo count($uninstalled); ?>)</h3>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:16px">
<?php foreach ($uninstalled as $u): ?>
<div style="background:var(--bg-card);border:1px dashed var(--border);border-radius:14px;padding:20px">
<div style="display:flex;align-items:center;gap:12px;margin-bottom:12px">
<div style="width:42px;height:42px;border-radius:10px;background:rgba(250,204,21,.12);display:flex;align-items:center;justify-content:center;font-size:20px;color:#facc15"><i class="bi bi-box-seam"></i></div>
<div>
<h4 style="margin:0;font-size:14px"><?php echo htmlspecialchars($u['name'], ENT_QUOTES, 'UTF-8'); ?></h4>
</div>
</div>
<div style="font-size:12px;color:#64748b;margin-bottom:14px;line-height:1.5"><?php echo htmlspecialchars($u['description'], ENT_QUOTES, 'UTF-8'); ?></div>
<form method="POST" action="/admin/plugins/install" style="display:inline">
<input type="hidden" name="name" value="<?php echo htmlspecialchars($u['name']); ?>">
<input type="hidden" name="description" value="<?php echo htmlspecialchars($u['description']); ?>">
<button type="submit" class="btn btn-sm primary"><i class="bi bi-download"></i> Install</button>
</form>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
