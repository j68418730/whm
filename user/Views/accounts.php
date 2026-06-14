<div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px"><a href="/user/accounts/create" class="btn primary">+ Create Station</a></div>
<table><tr><th>Name</th><th>Type</th><th>Port</th><th>Status</th><th>Actions</th></tr>
<?php if (!empty($streams)): foreach ($streams as $s): ?>
<tr><td><?php echo htmlspecialchars($s->server_type ?? 'Stream'); ?></td><td><?php echo $s->server_type; ?></td><td><?php echo $s->port; ?></td><td><span class="status-badge status-<?php echo $s->status === 'running' ? 'active' : 'terminated'; ?>"><?php echo $s->status; ?></span></td>
<td><a href="/user/start/<?php echo $s->id; ?>" class="btn btn-sm primary">Start</a> <a href="/user/stop/<?php echo $s->id; ?>" class="btn btn-sm danger">Stop</a></td></tr>
<?php endforeach; else: ?><tr><td colspan="5" style="text-align:center;padding:20px;color:#64748b">No stations yet.</td></tr>
<?php endif; ?></table>
