<div style="display:flex;gap:16px;flex-wrap:wrap;margin-bottom:20px">
<a href="#docker-pull" class="btn primary" onclick="document.getElementById('pullForm').classList.toggle('hidden')">Pull Image</a>
</div>
<div id="pullForm" class="card hidden" style="max-width:400px;margin-bottom:20px">
<form method="POST" action="/admin/container/pull"><div class="form-group"><label>Image name</label><input name="image" required placeholder="nginx:latest"></div>
<button type="submit" class="btn primary">Pull</button></form>
</div>

<h3 style="margin-bottom:10px">Containers</h3>
<table><tr><th>ID</th><th>Name</th><th>Image</th><th>Status</th><th>Ports</th><th></th></tr>
<?php if (!empty($containers)): foreach ($containers as $c): ?>
<tr>
<td style="font-family:monospace;font-size:12px"><?php echo htmlspecialchars($c['id']); ?></td>
<td><?php echo htmlspecialchars($c['name']); ?></td>
<td><?php echo htmlspecialchars($c['image']); ?></td>
<td><span class="status-badge status-<?php echo $c['status'] === 'running' ? 'active' : 'terminated'; ?>"><?php echo htmlspecialchars($c['status_text']); ?></span></td>
<td style="font-size:12px"><?php echo htmlspecialchars($c['ports']); ?></td>
<td style="display:flex;gap:4px">
<a href="/admin/container/start/<?php echo $c['id']; ?>" class="btn btn-sm primary" title="Start">▶</a>
<a href="/admin/container/stop/<?php echo $c['id']; ?>" class="btn btn-sm danger" title="Stop">⏹</a>
<a href="/admin/container/restart/<?php echo $c['id']; ?>" class="btn btn-sm secondary" title="Restart">⟳</a>
<a href="/admin/container/remove/<?php echo $c['id']; ?>" class="btn btn-sm danger" onclick="return confirm('Remove container?')" title="Remove">✕</a>
</td></tr>
<?php endforeach; else: ?>
<tr><td colspan="6" style="text-align:center;padding:20px;color:#64748b">No containers found. Docker may not be installed.</td></tr>
<?php endif; ?></table>

<h3 style="margin:20px 0 10px">Images</h3>
<table><tr><th>Repository</th><th>Tag</th><th>ID</th><th>Size</th></tr>
<?php if (!empty($images)): foreach ($images as $img): ?>
<tr><td><?php echo htmlspecialchars($img['repo']); ?></td>
<td><?php echo htmlspecialchars($img['tag']); ?></td>
<td style="font-family:monospace;font-size:12px"><?php echo htmlspecialchars($img['id']); ?></td>
<td><?php echo htmlspecialchars($img['size']); ?></td></tr>
<?php endforeach; else: ?>
<tr><td colspan="4" style="text-align:center;padding:20px;color:#64748b">No images found.</td></tr>
<?php endif; ?></table>
