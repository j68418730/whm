<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<div class="stats-grid" style="margin-bottom:20px">
<div class="stat-card"><h3>Installed</h3><div class="value"><?php echo count($plugins); ?></div></div>
<div class="stat-card"><h3>Active</h3><div class="value" style="color:#4ade80"><?php echo count(array_filter($plugins, fn($p) => $p->is_active)); ?></div></div>
<div class="stat-card"><h3>Available</h3><div class="value"><?php echo count($uninstalled); ?></div></div>
</div>
<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px">
<a class="btn primary" onclick="document.getElementById('uploadForm').classList.toggle('hidden')">Upload Plugin</a>
</div>
<div id="uploadForm" class="card hidden" style="max-width:500px;margin-bottom:20px">
<form method="POST" action="/admin/plugins/upload" enctype="multipart/form-data">
<h4 style="color:var(--accent);margin-bottom:8px">Upload Plugin (ZIP)</h4>
<div class="form-group"><label>Plugin ZIP file</label><input name="plugin_zip" type="file" accept=".zip" required></div>
<button type="submit" class="btn primary">Upload & Extract</button>
</form>
</div>
<h3 style="color:var(--accent);margin-bottom:12px">Installed Plugins</h3>
<table><tr><th>Name</th><th>Version</th><th>Status</th><th></th></tr>
<?php if (!empty($plugins)): foreach ($plugins as $p): ?>
<tr><td><?php echo htmlspecialchars($p->name); ?></td><td><?php echo htmlspecialchars($p->version ?? '1.0.0'); ?></td>
<td><span class="status-badge status-<?php echo $p->is_active ? 'active' : 'terminated'; ?>"><?php echo $p->is_active ? 'Enabled' : 'Disabled'; ?></span></td>
<td style="display:flex;gap:4px">
<a href="/admin/plugins/toggle/<?php echo $p->id; ?>" class="btn btn-sm <?php echo $p->is_active ? 'danger' : 'primary'; ?>"><?php echo $p->is_active ? 'Disable' : 'Enable'; ?></a>
<a href="/admin/plugins/uninstall/<?php echo $p->id; ?>" class="btn btn-sm danger" onclick="return confirm('Uninstall?')">Uninstall</a>
</td></tr>
<?php endforeach; else: ?><tr><td colspan="4" style="text-align:center;padding:20px;color:#64748b">No plugins installed.</td></tr>
<?php endif; ?></table>

<?php if (!empty($uninstalled)): ?>
<h3 style="color:var(--accent);margin:20px 0 12px">Available Plugins (Not Registered)</h3>
<table><tr><th>Name</th><th>Description</th><th></th></tr>
<?php foreach ($uninstalled as $u): ?>
<tr><td><?php echo htmlspecialchars($u['name']); ?></td><td><?php echo htmlspecialchars($u['description']); ?></td>
<td><form method="POST" action="/admin/plugins/install" style="display:inline"><input type="hidden" name="name" value="<?php echo htmlspecialchars($u['name']); ?>"><input type="hidden" name="description" value="<?php echo htmlspecialchars($u['description']); ?>"><button type="submit" class="btn btn-sm primary">Install</button></form></td></tr>
<?php endforeach; ?></table>
<?php endif; ?>
