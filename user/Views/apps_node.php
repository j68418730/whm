<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:16px">
<div><span style="color:var(--text-secondary)">Node.js <?php echo htmlspecialchars($nodeVer); ?> &middot; npm <?php echo htmlspecialchars($npmVer); ?></span></div>
<a class="btn primary" onclick="document.getElementById('nodeForm').classList.toggle('hidden')">Create App</a>
</div>
<div id="nodeForm" class="card hidden" style="max-width:500px;margin-bottom:20px">
<form method="POST" action="/user/apps/node/create">
<h4 style="color:var(--accent);margin-bottom:8px">New Node.js App</h4>
<div class="form-group"><label>App Name</label><input name="name" required placeholder="myapp"></div>
<div class="form-group"><label>Domain</label><input name="domain" placeholder="myapp.example.com"></div>
<div class="form-group"><label>Port</label><input name="port" type="number" value="3000"></div>
<div class="form-group"><label>Entry Point</label><input name="entry_point" value="app.js"></div>
<button type="submit" class="btn primary">Create</button>
</form></div>

<div class="card"><h3 style="color:var(--accent);margin-bottom:12px">Your Node.js Applications</h3>
<table><tr><th>Name</th><th>Port</th><th>Status</th><th>Actions</th></tr>
<?php if (!empty($apps)): foreach ($apps as $a): ?>
<tr>
<td><?php echo htmlspecialchars($a->name); ?></td>
<td><?php echo $a->port; ?></td>
<td><span class="status-badge status-<?php echo $a->status === 'running' ? 'active' : 'terminated'; ?>"><?php echo $a->status; ?></span></td>
<td style="display:flex;gap:4px">
<?php if ($a->status !== 'running'): ?><a href="/user/apps/node/start/<?php echo $a->id; ?>" class="btn btn-sm primary">Start</a><?php endif; ?>
<?php if ($a->status === 'running'): ?><a href="/user/apps/node/stop/<?php echo $a->id; ?>" class="btn btn-sm danger">Stop</a><?php endif; ?>
<a href="/user/apps/node/delete/<?php echo $a->id; ?>" class="btn btn-sm danger" onclick="return confirm('Delete?')">Delete</a>
</td></tr>
<?php endforeach; else: ?>
<tr><td colspan="4" style="text-align:center;padding:20px;color:#64748b">No Node.js apps yet.</td></tr>
<?php endif; ?></table></div>
